<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class DocumentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Document $document): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getDocumentName($document);

        AdminActionLogger::log(
            action: 'document.created',
            title: 'Documento enviado',
            description: $this->adminName() . ' enviou o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'category_id' => $document->document_category_id ?? null,
                'year' => $document->year ?? null,
            ]
        );
    }

    public function updated(Document $document): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($document)) {
            return;
        }

        $name = $this->getDocumentName($document);

        AdminActionLogger::log(
            action: 'document.updated',
            title: 'Documento atualizado',
            description: $this->adminName() . ' atualizou o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'changed_fields' => $this->changedFields($document),
            ]
        );
    }

    public function deleted(Document $document): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getDocumentName($document);

        AdminActionLogger::log(
            action: 'document.deleted',
            title: 'Documento removido',
            description: $this->adminName() . ' removeu o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'category_id' => $document->document_category_id ?? null,
                'year' => $document->year ?? null,
            ]
        );
    }

    private function getDocumentName(Document $document): string
    {
        return $document->title
            ?: $document->name
            ?: $document->filename
            ?: 'Documento #' . $document->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Document $document): bool
    {
        return count($this->changedFields($document)) > 0;
    }

    private function changedFields(Document $document): array
    {
        return collect(array_keys($document->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}
