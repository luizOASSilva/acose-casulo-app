<?php

namespace App\Observers;

use App\Mail\AdminDocumentDeletedMail;
use App\Models\Admin;
use App\Models\Document;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DocumentObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Document $document): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $document->loadMissing('category');

        $name = $this->getDocumentName($document);

        AdminActionLogger::log(
            action: 'document.created',
            title: 'Documento enviado',
            description: $this->adminName() . ' enviou o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'category_id' => $document->category_id ?? null,
                'category_name' => $this->getDocumentCategoryName($document),
                'year' => $document->year ?? null,
            ]
        );
    }

    public function updated(Document $document): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($document)) {
            return;
        }

        $document->loadMissing('category');

        $name = $this->getDocumentName($document);

        AdminActionLogger::log(
            action: 'document.updated',
            title: 'Documento atualizado',
            description: $this->adminName() . ' atualizou o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'category_id' => $document->category_id ?? null,
                'category_name' => $this->getDocumentCategoryName($document),
                'changed_fields' => $this->changedFields($document),
            ]
        );
    }

    public function deleted(Document $document): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $document->loadMissing('category');

        $name = $this->getDocumentName($document);
        $categoryName = $this->getDocumentCategoryName($document);
        $admin = auth('admin')->user();

        AdminActionLogger::log(
            action: 'document.deleted',
            title: 'Documento removido',
            description: $this->adminName() . ' removeu o documento "' . $name . '".',
            subject: $document,
            subjectName: $name,
            properties: [
                'document_id' => $document->id,
                'category_id' => $document->category_id ?? null,
                'category_name' => $categoryName,
                'year' => $document->year ?? null,
                'deleted_by_admin_id' => $admin?->id,
                'deleted_by_name' => $admin?->name,
                'deleted_by_email' => $admin?->email,
            ]
        );

        $this->notifyDocumentDeleted(
            document: $document,
            name: $name,
            categoryName: $categoryName
        );
    }

    private function notifyDocumentDeleted(
        Document $document,
        string $name,
        ?string $categoryName
    ): void {
        try {
            $recipients = Admin::query()
                ->where('role', Admin::ROLE_MASTER)
                ->where('is_active', true)
                ->pluck('email')
                ->filter()
                ->unique()
                ->values();

            if ($recipients->isEmpty()) {
                return;
            }

            $admin = auth('admin')->user();

            Mail::to($recipients->all())->send(
                new AdminDocumentDeletedMail(
                    documentName: $name,
                    documentId: $document->id,
                    deletedByName: $admin?->name ?: 'Administrador',
                    deletedByEmail: $admin?->email,
                    categoryId: $document->category_id ?? null,
                    categoryName: $categoryName,
                    year: $document->year ?? null,
                    deletedAt: now()
                )
            );
        } catch (Throwable $exception) {
            Log::error('Erro ao enviar e-mail de documento removido.', [
                'document_id' => $document->id,
                'document_name' => $name,
                'deleted_by_admin_id' => auth('admin')->id(),
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function getDocumentName(Document $document): string
    {
        return $document->title
            ?: basename((string) $document->file_url)
            ?: 'Documento #' . $document->id;
    }

    private function getDocumentCategoryName(Document $document): ?string
    {
        if (! $document->relationLoaded('category')) {
            return null;
        }

        return $document->category?->name
            ?: $document->category?->title;
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
