<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Media;
use App\Models\Publication;

class ActivityController extends Controller
{
    public function index()
    {
        return ActivityResource::collection(
            Activity::with([
                'publication.media',
                'publication.admin'
            ])
            ->latest()
            ->paginate(12)
        );
    }

    public function recent()
    {
        return ActivityResource::collection(
            Activity::with([
                'publication.media',
                'publication.admin'
            ])
            ->latest()
            ->limit(9)
            ->get()
        );
    }

    public function store(StoreActivityRequest $request)
    {
        $media = Media::create([
            'url' => $request->image_url,
            'alt_text' => $request->image_description,
            'caption' => null,
        ]);

        $publication = Publication::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'admin_id' => $request->user()->id,
            'media_id' => $media->id,
        ]);

        $activity = Activity::create([
            'likes' => $request->likes ?? 0,
            'publication_id' => $publication->id,
        ]);

        return ActivityResource::make(
            $activity->load('publication.media')
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
            'publication.admin'
        ])
        ->firstOrFail();

        return ActivityResource::make($activity);
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $activity->load('publication.media');

        $activity->publication->media->update([
            'url' => $request->image_url,
            'alt_text' => $request->image_description,
        ]);

        $activity->publication->update([
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        $activity->update([
            'likes' => $request->likes,
        ]);

        return ActivityResource::make(
            $activity->load('publication.media')
        );
    }

    public function destroy(Activity $activity)
    {
        $activity->load('publication.media');

        $activity->publication->delete();

        return response()->json(null, 204);
    }
}
