<?php

namespace App\Http\Controllers;

use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Keyword;
use App\Models\KeywordTranslation;
use App\Models\Media;
use App\Models\MediaTranslation;
use App\Models\Publication;
use App\Models\PublicationTranslation;
use App\Services\ArticleAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()
            ->with([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
            ]);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(function ($articleQuery) use ($search) {
                $articleQuery
                    ->where('summary', 'like', "%{$search}%")
                    ->orWhereHas('publication', function ($publicationQuery) use ($search) {
                        $publicationQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    })
                    ->orWhereHas('publication.translations', function ($translationQuery) use ($search) {
                        $translationQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%")
                            ->orWhere('summary', 'like', "%{$search}%");
                    })
                    ->orWhereHas('keywords', function ($keywordQuery) use ($search) {
                        $keywordQuery->where('word', 'like', "%{$search}%");
                    })
                    ->orWhereHas('keywords.translations', function ($keywordTranslationQuery) use ($search) {
                        $keywordTranslationQuery->where('word', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('keyword')) {
            $keywordTerms = $this->parseKeywordTerms(
                (string) $request->input('keyword')
            );

            foreach ($keywordTerms as $keyword) {
                $query->where(function ($articleQuery) use ($keyword) {
                    $articleQuery
                        ->whereHas('keywords', function ($keywordQuery) use ($keyword) {
                            $keywordQuery->where('word', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('keywords.translations', function ($translationQuery) use ($keyword) {
                            $translationQuery->where('word', 'like', "%{$keyword}%");
                        });
                });
            }
        }

        match ($request->input('sort', 'recent')) {
            'oldest' => $query->oldest('articles.created_at'),

            'az' => $query
                ->join('publications', 'articles.publication_id', '=', 'publications.id')
                ->orderBy('publications.title')
                ->select('articles.*'),

            default => $query->latest('articles.created_at'),
        };

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 24));

        return ArticleResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function recent()
    {
        return ArticleResource::collection(
            Article::with([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
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

            $this->syncMediaPortugueseTranslation($media);

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

            $this->syncPortugueseTranslation(
                publication: $publication->fresh(),
                summary: $validated['summary']
            );

            if (array_key_exists('keywords', $validated)) {
                $keywordIds = collect($validated['keywords'])
                    ->filter()
                    ->map(function ($word) {
                        $keyword = Keyword::firstOrCreate([
                            'word' => trim((string) $word),
                        ]);

                        $this->syncKeywordPortugueseTranslation($keyword);

                        return $keyword->id;
                    })
                    ->values()
                    ->all();

                $article->keywords()->attach($keywordIds);
            }

            return $article->fresh([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
            ]);
        });

        ArticleAuditLogger::created($article, $request);

        return ArticleResource::make(
            $article->load([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
            ])
        )->response()->setStatusCode(201);
    }

    public function show(string $article)
    {
        $articleModel = $this->findArticleByIdOrSlug($article);

        return ArticleResource::make($articleModel);
    }

    public function update(UpdateArticleRequest $request, string $article)
    {
        $validated = $request->validated();

        $articleModel = $this->findArticleByIdOrSlug($article);

        $before = ArticleAuditLogger::snapshot($articleModel);

        $articleModel = DB::transaction(function () use ($validated, $articleModel) {
            $articleModel->load([
                'publication.media.translations',
                'publication.translations',
                'keywords.translations',
            ]);

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

                $articleModel->publication->media->refresh();

                $this->syncMediaPortugueseTranslation(
                    $articleModel->publication->media
                );
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

            $articleModel->refresh();
            $articleModel->load('publication');

            $this->syncPortugueseTranslation(
                publication: $articleModel->publication,
                summary: $articleModel->summary
            );

            if (array_key_exists('keywords', $validated)) {
                $keywordIds = collect($validated['keywords'])
                    ->filter()
                    ->map(function ($word) {
                        $keyword = Keyword::firstOrCreate([
                            'word' => trim((string) $word),
                        ]);

                        $this->syncKeywordPortugueseTranslation($keyword);

                        return $keyword->id;
                    })
                    ->values()
                    ->all();

                $articleModel->keywords()->sync($keywordIds);
            }

            return $articleModel->fresh([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
            ]);
        });

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
            'publication.media.translations',
            'publication.admin',
            'publication.translations',
            'keywords.translations',
        ]);

        $snapshot = ArticleAuditLogger::snapshot($article);

        DB::transaction(function () use ($article, $snapshot) {
            ArticleAuditLogger::deleted($article, $snapshot, request());

            $publication = $article->publication;
            $media = $publication?->media;

            $article->keywords()->detach();

            $article->delete();
            $publication?->delete();
            $media?->delete();
        });

        return response()->json(null, 204);
    }

    private function findArticleByIdOrSlug(string $article): Article
    {
        return Article::query()
            ->where('id', $article)
            ->orWhereHas('publication', function ($query) use ($article) {
                $query->where('slug', $article);
            })
            ->orWhereHas('publication.translations', function ($query) use ($article) {
                $query->where('slug', $article);
            })
            ->with([
                'publication.media.translations',
                'publication.admin',
                'publication.translations',
                'keywords.translations',
            ])
            ->firstOrFail();
    }

    private function syncPortugueseTranslation(
        Publication $publication,
        ?string $summary = null
    ): void {
        PublicationTranslation::updateOrCreate(
            [
                'publication_id' => $publication->id,
                'locale' => PublicationTranslation::LOCALE_PT_BR,
            ],
            [
                'title' => $publication->title,
                'slug' => $publication->slug,
                'content' => $publication->content,
                'summary' => $summary,
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

    private function syncKeywordPortugueseTranslation(Keyword $keyword): void
    {
        KeywordTranslation::updateOrCreate(
            [
                'keyword_id' => $keyword->id,
                'locale' => KeywordTranslation::LOCALE_PT_BR,
            ],
            [
                'word' => $keyword->word,
                'translation_status' => KeywordTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
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
