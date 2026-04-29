<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Media;
use App\Models\Post;
use App\Models\Publication;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(
            Post::with('publication.media')->paginate()
        );
    }

    public function store(StorePostRequest $request)
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

        // 3. post
        $post = Post::create([
            'likes' => $request->likes ?? 0,
            'publication_id' => $publication->id,
        ]);

        $post->load('publication.media');

        return PostResource::make($post)->response()->setStatusCode(201);
    }

    public function show(Post $post)
    {
        return PostResource::make(
            $post->load('publication.media')
        );
    }

    public function update(UpdatePostRequest $request, Post $post)
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

        return PostResource::make(
            $post->load('publication.media')
        );
    }

    public function destroy(Post $post)
    {
        $post->load('publication.media');

        $post->publication->delete();

        return response()->json(null, 204);
    }
}
