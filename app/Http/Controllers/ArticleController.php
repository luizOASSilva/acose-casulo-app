<?php

namespace App\Http\Controllers;

use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Publication;
use App\Services\ArticleAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()
            ->with([
                'publication.media',
                'publication.admin',
                'keywords',
            ]);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(function ($articleQuery) use ($search) {
                $articleQuery
                    ->where('summary', 'like', "%{$search}%")
                    ->orWhereHas(
                        'publication',
                        function ($publicationQuery) use ($search) {
                            $publicationQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('content', 'like', "%{$search}%");
                        }
                    )
                    ->orWhereHas(
                        'keywords',
                        function ($keywordQuery) use ($search) {
                            $keywordQuery->where(
                                'word',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        if ($request->filled('keyword')) {
            $keywordTerms = $this->parseKeywordTerms(
                (string) $request->input('keyword')
            );

            foreach ($keywordTerms as $keyword) {
                $query->whereHas(
                    'keywords',
                    function ($keywordQuery) use ($keyword) {
                        $keywordQuery->where(
                            'word',
                            'like',
                            "%{$keyword}%"
                        );
                    }
                );
            }
        }

        match ($request->input('sort', 'recent')) {
            'oldest' => $query->oldest('articles.created_at'),

            'az' => $query
                ->join(
                    'publications',
                    'articles.publication_id',
                    '=',
                    'publications.id'
                )
                ->orderBy('publications.title')
                ->select('articles.*'),

            default => $query->latest('articles.created_at'),
        };

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 24));

        return ArticleResource::collection(
            $query
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function recent()
    {
        return ArticleResource::collection(
            Article::query()
                ->with([
                    'publication.media',
                    'publication.admin',
                    'keywords',
                ])
                ->latest('articles.created_at')
                ->limit(4)
                ->get()
        );
    }

    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();

        $article = DB::transaction(
            function () use ($validated, $request) {
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

                $article = Article::create([
                    'summary' => $validated['summary'],
                    'publication_id' => $publication->id,
                ]);

                if (array_key_exists('keywords', $validated)) {
                    $keywordIds = collect($validated['keywords'])
                        ->filter()
                        ->map(function ($word) {
                            $normalizedWord = trim((string) $word);

                            if ($normalizedWord === '') {
                                return null;
                            }

                            $keyword = Keyword::firstOrCreate([
                                'word' => $normalizedWord,
                            ]);

                            return $keyword->id;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $article->keywords()->attach($keywordIds);
                }

                return $article->fresh([
                    'publication.media',
                    'publication.admin',
                    'keywords',
                ]);
            }
        );

        ArticleAuditLogger::created($article, $request);

        return ArticleResource::make($article)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $article)
    {
        $articleModel = $this->findArticleByIdOrSlug($article);

        return ArticleResource::make($articleModel);
    }

    public function update(
        UpdateArticleRequest $request,
        string $article
    ) {
        $validated = $request->validated();

        $articleModel = $this->findArticleByIdOrSlug($article);

        $before = ArticleAuditLogger::snapshot($articleModel);

        $articleModel = DB::transaction(
            function () use ($validated, $articleModel) {
                $articleModel->load([
                    'publication.media',
                    'keywords',
                ]);

                if (
                    array_key_exists('image_url', $validated) ||
                    array_key_exists('image_description', $validated) ||
                    array_key_exists('image_caption', $validated)
                ) {
                    $media = $articleModel->publication?->media;

                    if ($media) {
                        $media->update([
                            'url' => $validated['image_url']
                                ?? $media->url,

                            'alt_text' => $validated['image_description']
                                ?? $media->alt_text,

                            'caption' => array_key_exists(
                                'image_caption',
                                $validated
                            )
                                ? $validated['image_caption']
                                : $media->caption,
                        ]);
                    }
                }

                if (
                    array_key_exists('title', $validated) ||
                    array_key_exists('content', $validated)
                ) {
                    $publication = $articleModel->publication;

                    if ($publication) {
                        $publication->update([
                            'title' => $validated['title']
                                ?? $publication->title,

                            'content' => $validated['content']
                                ?? $publication->content,
                        ]);
                    }
                }

                if (array_key_exists('summary', $validated)) {
                    $articleModel->update([
                        'summary' => $validated['summary'],
                    ]);
                }

                if (array_key_exists('keywords', $validated)) {
                    $keywordIds = collect($validated['keywords'])
                        ->filter()
                        ->map(function ($word) {
                            $normalizedWord = trim((string) $word);

                            if ($normalizedWord === '') {
                                return null;
                            }

                            $keyword = Keyword::firstOrCreate([
                                'word' => $normalizedWord,
                            ]);

                            return $keyword->id;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $articleModel->keywords()->sync($keywordIds);
                }

                return $articleModel->fresh([
                    'publication.media',
                    'publication.admin',
                    'keywords',
                ]);
            }
        );

        $after = ArticleAuditLogger::snapshot($articleModel);

        ArticleAuditLogger::updated(
            article: $articleModel,
            before: $before,
            after: $after,
            request: $request
        );

        return ArticleResource::make($articleModel);
    }

    public function destroy(Article $article)
    {
        $article->load([
            'publication.media',
            'publication.admin',
            'keywords',
        ]);

        $snapshot = ArticleAuditLogger::snapshot($article);

        DB::transaction(
            function () use ($article, $snapshot) {
                ArticleAuditLogger::deleted(
                    $article,
                    $snapshot,
                    request()
                );

                $publication = $article->publication;
                $media = $publication?->media;

                $article->keywords()->detach();

                $article->delete();
                $publication?->delete();
                $media?->delete();
            }
        );

        return response()->json(null, 204);
    }

    private function findArticleByIdOrSlug(string $article): Article
    {
        return Article::query()
            ->where('id', $article)
            ->orWhereHas(
                'publication',
                function ($query) use ($article) {
                    $query->where('slug', $article);
                }
            )
            ->with([
                'publication.media',
                'publication.admin',
                'keywords',
            ])
            ->firstOrFail();
    }

    private function parseKeywordTerms(string $value): array
    {
        $normalized = str($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[,\s]+/', ' ')
            ->trim()
            ->value();

        if ($normalized === '') {
            return [];
        }

        return collect(explode(' ', $normalized))
            ->map(fn ($term) => trim($term))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
