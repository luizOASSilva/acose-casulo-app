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
            Activity::with('publication.media')->paginate()
        );
    }

    public function recent()
    {
        return ActivityResource::collection(
            Activity::with('publication.media')->latest()->take(9)->get()
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

        $post = Activity::create([
            'likes' => $request->likes ?? 0,
            'publication_id' => $publication->id,
        ]);

        $post->load('publication.media');

        return ActivityResource::make($post)->response()->setStatusCode(201);
    }

    public function show(string $slug)
    {
        $post = Activity::whereHas('publication', fn($q) => $q->where('slug', $slug))
            ->with('publication.media')
            ->firstOrFail();

        return ActivityResource::make($post);
    }

    public function update(UpdateActivityRequest $request, Activity $post)
    {
        $post->load('publication.media');

        $post->publication->media->update([
            'url' => $request->image_url,
            'alt_text' => $request->image_description,
        ]);

        $post->publication->update([
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        $post->update([
            'likes' => $request->likes,
        ]);

        return ActivityResource::make(
            $post->load('publication.media')
        );
    }

    public function destroy(Activity $post)
    {
        $post->load('publication.media');

        $post->publication->delete();

        return response()->json(null, 204);
    }
}
