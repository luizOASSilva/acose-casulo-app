<?php

namespace App\Services;

use App\Models\Article;
use App\Support\AuditDiff;
use Illuminate\Http\Request;

class ArticleAuditLogger
{
    public static function snapshot(Article $article): array
    {
        $article->loadMissing([
            'publication.media',
            'publication.admin',
            'keywords',
        ]);

        return [
            'title' => $article->publication?->title,
            'content' => $article->publication?->content,
            'summary' => $article->summary,
            'image_url' => $article->publication?->media?->url,
            'image_description' => $article->publication?->media?->alt_text,
            'image_caption' => $article->publication?->media?->caption,
            'keywords' => $article->keywords
                ->pluck('word')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    public static function created(Article $article, Request $request): void
    {
        if (! self::shouldLog()) {
            return;
        }

        $article->loadMissing([
            'publication.media',
            'publication.admin',
            'keywords',
        ]);

        $snapshot = self::snapshot($article);
        $newValues = AuditDiff::filterFilled($snapshot);
        $changedValues = AuditDiff::createdValues($snapshot);

        if (empty($changedValues)) {
            return;
        }

        $title = self::articleTitle($article, $snapshot);

        AdminActionLogger::log(
            action: 'article.created',
            title: 'Artigo criado',
            description: self::adminName() . ' criou o artigo "' . $title . '".',
            subject: $article,
            subjectName: $title,
            properties: [
                'article_id' => $article->id,
                'publication_id' => $article->publication_id,
                'title' => $title,
                'slug' => $article->publication?->slug,
                'changed_fields' => array_keys($changedValues),
                'new_values' => $newValues,
                'changed_values' => $changedValues,
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    public static function updated(
        Article $article,
        array $before,
        array $after,
        Request $request
    ): void {
        if (! self::shouldLog()) {
            return;
        }

        $changedValues = AuditDiff::changedValues($before, $after);

        if (empty($changedValues)) {
            return;
        }

        $article->loadMissing([
            'publication.media',
            'publication.admin',
            'keywords',
        ]);

        $title = self::articleTitle($article, $after);

        AdminActionLogger::log(
            action: 'article.updated',
            title: 'Artigo atualizado',
            description: self::adminName() . ' atualizou o artigo "' . $title . '".',
            subject: $article,
            subjectName: $title,
            properties: [
                'article_id' => $article->id,
                'publication_id' => $article->publication_id,
                'title' => $title,
                'slug' => $article->publication?->slug,
                'changed_fields' => array_keys($changedValues),
                'old_values' => AuditDiff::oldValuesFromChanges($changedValues),
                'new_values' => AuditDiff::newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    public static function deleted(
        Article $article,
        array $snapshot,
        Request $request
    ): void {
        if (! self::shouldLog()) {
            return;
        }

        $article->loadMissing([
            'publication.media',
            'publication.admin',
            'keywords',
        ]);

        $title = self::articleTitle($article, $snapshot);

        AdminActionLogger::log(
            action: 'article.deleted',
            title: 'Artigo removido',
            description: self::adminName() . ' removeu o artigo "' . $title . '".',
            subject: $article,
            subjectName: $title,
            properties: [
                'article_id' => $article->id,
                'publication_id' => $article->publication_id,
                'title' => $title,
                'slug' => $article->publication?->slug,
                'old_values' => AuditDiff::filterFilled($snapshot),
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    private static function articleTitle(Article $article, array $values = []): string
    {
        return $values['title']
            ?? $article->publication?->title
            ?? 'Artigo #' . $article->id;
    }

    private static function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private static function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }
}
