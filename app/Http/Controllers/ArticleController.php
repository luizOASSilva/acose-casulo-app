<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Publication;

class ArticleController extends Controller
{
    public function index()
    {
        return ArticleResource::collection(
            Article::with('publication.media', 'publication.admin', 'keywords')->paginate()
        );
    }

    public function store(StoreArticleRequest $request)
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

        $article = Article::create([
            'summary' => $request->summary,
            'publication_id' => $publication->id,
        ]);

        if ($request->has('keywords')) {
            $keywordIds = collect($request->keywords)->map(fn($word) =>
                Keyword::firstOrCreate(['word' => $word])->id
            );

            $article->keywords()->attach($keywordIds);
        }

        $article->load('publication.media', 'keywords');

        return ArticleResource::make($article)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Article $article)
    {
        return ArticleResource::make(
            $article->load('publication.media', 'publication.admin', 'keywords')
        );
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->load('publication.media', 'publication.admin', 'keywords');

        $article->publication->media()->update([
            'url' => $request->image_url,
            'alt_text' => $request->image_description,
        ]);

        $article->publication->update([
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        $article->update([
            'summary' => $request->summary,
        ]);

        if ($request->has('keywords')) {
            $keywordIds = collect($request->keywords)->map(fn   ($word) =>
                Keyword::firstOrCreate(['word' => $word])->id
            );

            $article->keywords()->sync($keywordIds);
        }

        return ArticleResource::make(
            $article->load('publication.media', 'keywords')
        );
    }

    public function destroy(Article $article)
    {
        $article->load('publication.media');

        $article->keywords()->detach();

        $article->publication->delete();

        return response()->json(null, 204);
    }
}
