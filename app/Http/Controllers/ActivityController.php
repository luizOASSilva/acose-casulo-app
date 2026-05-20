<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\ActivityLike;
use App\Models\Media;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
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
                ->paginate(12)
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

    public function store(StoreActivityRequest $request)
    {
        $validated = $request->validated();

        $activity = DB::transaction(function () use ($validated, $request) {
            $media = Media::create([
                'url' => $validated['image_url'],
                'alt_text' => $validated['image_description'],
                'caption' => $validated['image_caption'] ?? null,
            ]);

            $publication = Publication::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'admin_id' => $request->user()->id,
                'media_id' => $media->id,
            ]);

            $activity = Activity::create([
                'publication_id' => $publication->id,
            ]);

            $activity->schedules()->createMany($validated['schedules']);

            return $activity;
        });

        return ActivityResource::make(
            $activity->load([
                'publication.media',
                'publication.admin',
                'schedules',
            ])->loadCount('likes')
        )->response()->setStatusCode(201);
    }

    public function show(string $slug)
    {
        $activity = Activity::whereHas(
            'publication',
            fn ($q) => $q->where('slug', $slug)
        )
            ->with([
                'publication.media',
                'publication.admin',
                'schedules',
            ])
            ->withCount('likes')
            ->firstOrFail();

        return ActivityResource::make($activity);
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $activity) {
            $activity->load('publication.media');

            if (isset($validated['image_url']) || isset($validated['image_description']) || array_key_exists('image_caption', $validated)) {
                $activity->publication->media->update([
                    'url' => $validated['image_url'] ?? $activity->publication->media->url,
                    'alt_text' => $validated['image_description'] ?? $activity->publication->media->alt_text,
                    'caption' => array_key_exists('image_caption', $validated)
                        ? $validated['image_caption']
                        : $activity->publication->media->caption,
                ]);
            }

            if (isset($validated['title']) || isset($validated['content'])) {
                $activity->publication->update([
                    'title' => $validated['title'] ?? $activity->publication->title,
                    'content' => $validated['content'] ?? $activity->publication->content,
                ]);
            }

            if (array_key_exists('schedules', $validated)) {
                $activity->schedules()->delete();
                $activity->schedules()->createMany($validated['schedules']);
            }
        });

        return ActivityResource::make(
            $activity->fresh()
                ->load([
                    'publication.media',
                    'publication.admin',
                    'schedules',
                ])
                ->loadCount('likes')
        );
    }

    public function destroy(Activity $activity)
    {
        $activity->load('publication.media');

        DB::transaction(function () use ($activity) {
            $publication = $activity->publication;
            $media = $publication?->media;

            $activity->delete();
            $publication?->delete();
            $media?->delete();
        });

        return response()->json(null, 204);
    }

    public function toggleLike(Request $request, Activity $activity): JsonResponse
    {
        $validated = $request->validate([
            'visitor_id' => [
                'required',
                'string',
                'max:64',
            ],
        ]);

        $liked = false;

        DB::transaction(function () use ($activity, $request, $validated, &$liked) {
            $like = ActivityLike::query()
                ->where('activity_id', $activity->id)
                ->where('visitor_id', $validated['visitor_id'])
                ->lockForUpdate()
                ->first();

            if ($like) {
                $like->delete();
                $liked = false;

                return;
            }

            ActivityLike::create([
                'activity_id' => $activity->id,
                'visitor_id' => $validated['visitor_id'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $liked = true;
        });

        return response()->json([
            'liked' => $liked,
            'likes' => $activity->likes()->count(),
        ]);
    }
}
