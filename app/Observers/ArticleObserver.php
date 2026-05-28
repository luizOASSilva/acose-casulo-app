<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ArticleObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Article $article): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $article->loadMissing('publication');

        $name = $this->getArticleName($article);

        AdminActionLogger::log(
            action: 'article.created',
            title: 'Artigo criado',
            description: $this->adminName() . ' criou o artigo "' . $name . '".',
            subject: $article,
            subjectName: $name,
            properties: [
                'article_id' => $article->id,
                'slug' => $article->slug ?? null,
            ]
        );
    }

    public function updated(Article $article): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($article)) {
            return;
        }

        $article->loadMissing('publication');

        $name = $this->getArticleName($article);

        AdminActionLogger::log(
            action: 'article.updated',
            title: 'Artigo atualizado',
            description: $this->adminName() . ' atualizou o artigo "' . $name . '".',
            subject: $article,
            subjectName: $name,
            properties: [
                'article_id' => $article->id,
                'slug' => $article->slug ?? null,
                'changed_fields' => $this->changedFields($article),
            ]
        );
    }

    public function deleted(Article $article): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $article->loadMissing('publication');

        $name = $this->getArticleName($article);

        AdminActionLogger::log(
            action: 'article.deleted',
            title: 'Artigo removido',
            description: $this->adminName() . ' removeu o artigo "' . $name . '".',
            subject: $article,
            subjectName: $name,
            properties: [
                'article_id' => $article->id,
                'slug' => $article->slug ?? null,
            ]
        );
    }

    private function getArticleName(Article $article): string
    {
        return $article->publication?->title
            ?: $article->title
            ?: $article->slug
            ?: 'Artigo #' . $article->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Article $article): bool
    {
        return count($this->changedFields($article)) > 0;
    }

    private function changedFields(Article $article): array
    {
        return collect(array_keys($article->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}
