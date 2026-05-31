<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\ActivityLike;
use App\Models\ActivitySchedule;
use App\Models\Media;
use App\Models\Publication;
use App\Services\ActivityAuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with([
                'publication.media',
                'publication.admin',
                'schedules',
            ])
            ->withCount('likes');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->whereHas('publication', function ($publicationQuery) use ($search) {
                $publicationQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('weekday')) {
            $query->whereHas('schedules', function ($scheduleQuery) use ($request) {
                $scheduleQuery->where('weekday', $request->input('weekday'));
            });
        }

        if ($request->filled('start_time') || $request->filled('end_time')) {
            $query->whereHas('schedules', function ($scheduleQuery) use ($request) {
                if ($request->filled('weekday')) {
                    $scheduleQuery->where('weekday', $request->input('weekday'));
                }

                $startTime = $request->input('start_time');
                $endTime = $request->input('end_time');

                if ($startTime && $endTime) {
                    $scheduleQuery
                        ->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);

                    return;
                }

                if ($startTime) {
                    $scheduleQuery->where('start_time', '>=', $startTime);
                    return;
                }

                if ($endTime) {
                    $scheduleQuery->where('end_time', '<=', $endTime);
                }
            });
        }

        match ($request->input('sort', 'recent')) {
            'oldest' => $query->oldest('activities.created_at'),

            'az' => $query
                ->join('publications', 'activities.publication_id', '=', 'publications.id')
                ->orderBy('publications.title')
                ->select('activities.*'),

            'likes' => $query->orderByDesc('likes_count'),

            default => $query->latest('activities.created_at'),
        };

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 24));

        return ActivityResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function recent()
    {
        return ActivityResource::collection(
            Activity::query()
                ->with([
                    'publication.media',
                    'publication.admin',
                    'schedules',
                ])
                ->withCount('likes')
                ->latest()
                ->limit(9)
                ->get()
        );
    }

    public function schedules()
    {
        return ActivitySchedule::query()
            ->with('activity.publication')
            ->orderByRaw("FIELD(weekday, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get()
            ->map(fn ($schedule) => [
                'id' => $schedule->id,
                'activity_id' => $schedule->activity_id,
                'activity_title' => $schedule->activity?->publication?->title,
                'weekday' => $schedule->weekday,
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
            ]);
    }

    public function store(StoreActivityRequest $request)
    {
        $validated = $request->validated();

        $activity = DB::transaction(function () use ($validated, $request) {
            $media = Media::create([
                'url' => $validated['image_url'],
                'alt_text' => $validated['image_description'],
                'caption' => $validated['image_caption'] ?? null,
            ]);

            $admin = $request->user('admin') ?? $request->user();

            $publication = Publication::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'admin_id' => $admin?->id,
                'media_id' => $media->id,
            ]);

            $activity = Activity::create([
                'publication_id' => $publication->id,
            ]);

            $activity->schedules()->createMany($validated['schedules']);

            return $activity->fresh([
                'publication.media',
                'publication.admin',
                'schedules',
            ])->loadCount('likes');
        });

        ActivityAuditLogger::created($activity, $request);

        return ActivityResource::make(
            $activity->load([
                'publication.media',
                'publication.admin',
                'schedules',
            ])->loadCount('likes')
        )->response()->setStatusCode(201);
    }

    public function show(string $activity)
    {
        $activityModel = Activity::query()
            ->where('id', $activity)
            ->orWhereHas('publication', function ($query) use ($activity) {
                $query->where('slug', $activity);
            })
            ->with([
                'publication.media',
                'publication.admin',
                'schedules',
            ])
            ->withCount('likes')
            ->firstOrFail();

        return ActivityResource::make($activityModel);
    }

    public function update(UpdateActivityRequest $request, string $activity)
    {
        $validated = $request->validated();

        $activityModel = Activity::query()
            ->where('id', $activity)
            ->orWhereHas('publication', function ($query) use ($activity) {
                $query->where('slug', $activity);
            })
            ->with([
                'publication.media',
                'publication.admin',
                'schedules',
            ])
            ->withCount('likes')
            ->firstOrFail();

        $before = ActivityAuditLogger::snapshot($activityModel);

        $activityModel = DB::transaction(function () use ($validated, $activityModel) {
            $activityModel->load([
                'publication.media',
                'schedules',
            ]);

            if (
                array_key_exists('image_url', $validated) ||
                array_key_exists('image_description', $validated) ||
                array_key_exists('image_caption', $validated)
            ) {
                $activityModel->publication->media->update([
                    'url' => $validated['image_url'] ?? $activityModel->publication->media->url,
                    'alt_text' => $validated['image_description'] ?? $activityModel->publication->media->alt_text,
                    'caption' => array_key_exists('image_caption', $validated)
                        ? $validated['image_caption']
                        : $activityModel->publication->media->caption,
                ]);
            }

            if (
                array_key_exists('title', $validated) ||
                array_key_exists('content', $validated)
            ) {
                $activityModel->publication->update([
                    'title' => $validated['title'] ?? $activityModel->publication->title,
                    'content' => $validated['content'] ?? $activityModel->publication->content,
                ]);
            }

            if (array_key_exists('schedules', $validated)) {
                $activityModel->schedules()->delete();
                $activityModel->schedules()->createMany($validated['schedules']);
            }

            return $activityModel->fresh([
                'publication.media',
                'publication.admin',
                'schedules',
            ])->loadCount('likes');
        });

        $after = ActivityAuditLogger::snapshot($activityModel);

        ActivityAuditLogger::updated(
            activity: $activityModel,
            before: $before,
            after: $after,
            request: $request
        );

        return ActivityResource::make($activityModel);
    }

    public function destroy(Activity $activity)
    {
        $activity->load([
            'publication.media',
            'publication.admin',
            'schedules',
        ]);

        $snapshot = ActivityAuditLogger::snapshot($activity);

        DB::transaction(function () use ($activity, $snapshot) {
            ActivityAuditLogger::deleted($activity, $snapshot, request());

            $publication = $activity->publication;
            $media = $publication?->media;

            $activity->delete();
            $publication?->delete();
            $media?->delete();
        });

        return response()->json(null, 204);
    }

    public function toggleLike(Request $request, string $activity): JsonResponse
    {
        $activityModel = Activity::query()
            ->where('id', $activity)
            ->orWhereHas('publication', function ($query) use ($activity) {
                $query->where('slug', $activity);
            })
            ->firstOrFail();

        $visitorId =
            $request->cookie('visitor_id')
            ?? $request->header('X-Visitor-ID')
            ?? $request->input('visitor_id');

        if (! $visitorId) {
            $visitorId = (string) Str::uuid();
        }

        $visitorId = Str::limit((string) $visitorId, 64, '');

        $liked = false;

        DB::transaction(function () use ($activityModel, $request, $visitorId, &$liked) {
            $like = ActivityLike::query()
                ->where('activity_id', $activityModel->id)
                ->where('visitor_id', $visitorId)
                ->lockForUpdate()
                ->first();

            if ($like) {
                $like->delete();
                $liked = false;

                return;
            }

            ActivityLike::create([
                'activity_id' => $activityModel->id,
                'visitor_id' => $visitorId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $liked = true;
        });

        $likesCount = $activityModel->likes()->count();

        return response()->json([
            'liked' => $liked,
            'is_liked' => $liked,
            'likes' => $likesCount,
            'likes_count' => $likesCount,
            'visitor_id' => $visitorId,
        ]);
    }
}
