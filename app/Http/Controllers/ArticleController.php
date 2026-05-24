<?php

namespace App\Http\Controllers;

use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index()
    {
        return ArticleResource::collection(
            Article::with([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
                ->latest()
                ->paginate(12)
        );
    }

    public function recent()
    {
        return ArticleResource::collection(
            Article::with([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
                ->latest()
                ->limit(4)
                ->get()
        );
    }

    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();

        $article = DB::transaction(function () use ($validated, $request) {
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

            $article = Article::create([
                'summary' => $validated['summary'],
                'publication_id' => $publication->id,
            ]);

            if (array_key_exists('keywords', $validated)) {
                $keywordIds = collect($validated['keywords'])
                    ->filter()
                    ->map(fn ($word) => Keyword::firstOrCreate([
                        'word' => $word,
                    ])->id);

                $article->keywords()->attach($keywordIds);
            }

            return $article;
        });

        return ArticleResource::make(
            $article->load([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
        )->response()->setStatusCode(201);
    }

    public function show(string $article)
    {
        $articleModel = Article::query()
            ->where('id', $article)
            ->orWhereHas('publication', function ($query) use ($article) {
                $query->where('slug', $article);
            })
            ->with([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
            ->firstOrFail();

        return ArticleResource::make($articleModel);
    }

    public function update(UpdateArticleRequest $request, string $article)
    {
        $validated = $request->validated();

        $articleModel = Article::query()
            ->where('id', $article)
            ->orWhereHas('publication', function ($query) use ($article) {
                $query->where('slug', $article);
            })
            ->with([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $articleModel) {
            $articleModel->load('publication.media');

            if (
                array_key_exists('image_url', $validated) ||
                array_key_exists('image_description', $validated) ||
                array_key_exists('image_caption', $validated)
            ) {
                $articleModel->publication->media->update([
                    'url' => $validated['image_url'] ?? $articleModel->publication->media->url,
                    'alt_text' => $validated['image_description'] ?? $articleModel->publication->media->alt_text,
                    'caption' => array_key_exists('image_caption', $validated)
                        ? $validated['image_caption']
                        : $articleModel->publication->media->caption,
                ]);
            }

            if (
                array_key_exists('title', $validated) ||
                array_key_exists('content', $validated)
            ) {
                $articleModel->publication->update([
                    'title' => $validated['title'] ?? $articleModel->publication->title,
                    'content' => $validated['content'] ?? $articleModel->publication->content,
                ]);
            }

            if (array_key_exists('summary', $validated)) {
                $articleModel->update([
                    'summary' => $validated['summary'],
                ]);
            }

            if (array_key_exists('keywords', $validated)) {
                $keywordIds = collect($validated['keywords'])
                    ->filter()
                    ->map(fn ($word) => Keyword::firstOrCreate([
                        'word' => $word,
                    ])->id);

                $articleModel->keywords()->sync($keywordIds);
            }
        });

        return ArticleResource::make(
            $articleModel->fresh()
                ->load([
                    'publication.media',
                    'publication.admin',
                    'keywords',
                ])
        );
    }

    public function destroy(Article $article)
    {
        $article->load('publication.media');

        DB::transaction(function () use ($article) {
            $publication = $article->publication;
            $media = $publication?->media;

            $article->keywords()->detach();

            $article->delete();
            $publication?->delete();
            $media?->delete();
        });

        return response()->json(null, 204);
    }
}
