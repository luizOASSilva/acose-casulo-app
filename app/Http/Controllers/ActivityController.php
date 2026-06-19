<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\ActivityLike;
use App\Models\ActivitySchedule;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Publication;
use App\Models\PublicationTranslation;
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
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'schedules',
            ])
            ->withCount('likes');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(function ($activityQuery) use ($search) {
                $activityQuery
                    ->whereHas('publication', function ($publicationQuery) use ($search) {
                        $publicationQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    })
                    ->orWhereHas('publication.translations', function ($translationQuery) use ($search) {
                        $translationQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
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
                    'publication.media.translations',
                    'publication.admin',
                    'publication.translations',
                    'schedules',
                ])
                ->withCount('likes')
                ->latest()
                ->limit(9)
                ->get()
        );
    }

    public function schedules(Request $request)
    {
        $locale = $this->resolveLocale($request);

        return ActivitySchedule::query()
            ->with('activity.publication.translations')
            ->orderByRaw("FIELD(weekday, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get()
            ->map(fn ($schedule) => [
                'id' => $schedule->id,
                'activity_id' => $schedule->activity_id,
                'activity_title' => $schedule->activity?->publication?->translatedTitle($locale),
                'weekday' => $schedule->weekday,
                'weekday_label' => $this->weekdayLabel($schedule->weekday, $locale),
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

            $this->syncMediaPortugueseTranslation($media);

            $admin = $request->user('admin') ?? $request->user();

            $publication = Publication::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'admin_id' => $admin?->id,
                'media_id' => $media->id,
            ]);

            $this->syncPortugueseTranslation($publication->fresh());

            $activity = Activity::create([
                'publication_id' => $publication->id,
            ]);

            $activity->schedules()->createMany($validated['schedules']);

            return $activity->fresh([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'schedules',
            ])->loadCount('likes');
        });

        ActivityAuditLogger::created($activity, $request);

        return ActivityResource::make(
            $activity->load([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'schedules',
            ])->loadCount('likes')
        )->response()->setStatusCode(201);
    }

    public function show(string $activity)
    {
        $activityModel = $this->findActivityByIdOrSlug($activity);

        return ActivityResource::make($activityModel);
    }

    public function update(UpdateActivityRequest $request, string $activity)
    {
        $validated = $request->validated();

        $activityModel = $this->findActivityByIdOrSlug($activity);

        $before = ActivityAuditLogger::snapshot($activityModel);

        $activityModel = DB::transaction(function () use ($validated, $activityModel) {
            $activityModel->load([
                'publication.media.translations',
                'publication.translations',
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

                $activityModel->publication->media->refresh();

                $this->syncMediaPortugueseTranslation(
                    $activityModel->publication->media
                );
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

            $activityModel->publication->refresh();

            $this->syncPortugueseTranslation($activityModel->publication);

            if (array_key_exists('schedules', $validated)) {
                $activityModel->schedules()->delete();
                $activityModel->schedules()->createMany($validated['schedules']);
            }

            return $activityModel->fresh([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
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

    public function destroy(string $activity)
    {
        $activityModel = $this->findActivityByIdOrSlug($activity);

        $activityModel->load([
            'publication.media.translations',
            'publication.admin',
            'publication.translations',
            'schedules',
        ]);

        $snapshot = ActivityAuditLogger::snapshot($activityModel);

        DB::transaction(function () use ($activityModel, $snapshot) {
            ActivityAuditLogger::deleted($activityModel, $snapshot, request());

            $publication = $activityModel->publication;
            $media = $publication?->media;

            $activityModel->delete();
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
            ->orWhereHas('publication.translations', function ($query) use ($activity) {
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

    private function findActivityByIdOrSlug(string $activity): Activity
    {
        return Activity::query()
            ->where('id', $activity)
            ->orWhereHas('publication', function ($query) use ($activity) {
                $query->where('slug', $activity);
            })
            ->orWhereHas('publication.translations', function ($query) use ($activity) {
                $query->where('slug', $activity);
            })
            ->with([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'schedules',
            ])
            ->withCount('likes')
            ->firstOrFail();
    }

    private function syncPortugueseTranslation(Publication $publication): void
    {
        PublicationTranslation::updateOrCreate(
            [
                'publication_id' => $publication->id,
                'locale' => PublicationTranslation::LOCALE_PT_BR,
            ],
            [
                'title' => $publication->title,
                'slug' => $publication->slug,
                'content' => $publication->content,
                'summary' => null,
                'translation_status' => PublicationTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }

    private function syncMediaPortugueseTranslation(Media $media): void
    {
        MediaTranslation::updateOrCreate(
            [
                'media_id' => $media->id,
                'locale' => MediaTranslation::LOCALE_PT_BR,
            ],
            [
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'translation_status' => MediaTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? PublicationTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => PublicationTranslation::LOCALE_EN,
            default => PublicationTranslation::LOCALE_PT_BR,
        };
    }

    private function weekdayLabel(?string $weekday, string $locale): string
    {
        $labels = [
            PublicationTranslation::LOCALE_PT_BR => [
                'monday' => 'Segunda-feira',
                'tuesday' => 'Terça-feira',
                'wednesday' => 'Quarta-feira',
                'thursday' => 'Quinta-feira',
                'friday' => 'Sexta-feira',
                'saturday' => 'Sábado',
                'sunday' => 'Domingo',
            ],

            PublicationTranslation::LOCALE_EN => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
        ];

        return $labels[$locale][$weekday] ?? $weekday ?? '';
    }
}
