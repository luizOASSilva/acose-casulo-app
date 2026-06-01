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
        $changedValues = $this->createdValues($document);

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
                'changed_fields' => array_keys($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function updated(Document $document): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($document)) {
            return;
        }

        $changedValues = $this->changedValues($document);

        if (empty($changedValues)) {
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
                'changed_fields' => array_keys($changedValues),
                'old_values' => $this->oldValuesFromChanges($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
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

        $actionLog = AdminActionLogger::log(
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
                'old_values' => $this->filledSnapshot($document),
                'request' => $this->requestContext(),
            ]
        );

        $this->notifyDocumentDeleted(
            document: $document,
            name: $name,
            categoryName: $categoryName,
            auditUrl: $this->auditUrl($actionLog?->id)
        );
    }

    private function notifyDocumentDeleted(
        Document $document,
        string $name,
        ?string $categoryName,
        ?string $auditUrl = null
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
                    deletedAt: now(),
                    auditUrl: $auditUrl
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

    private function auditUrl(null|int|string $actionLogId): ?string
    {
        if (! $actionLogId) {
            return null;
        }

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        if (! $frontendUrl) {
            $frontendUrl = rtrim((string) config('app.url'), '/');
        }

        return $frontendUrl . '/admin/auditoria/' . $actionLogId;
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
            ->reject(fn ($field) => in_array($field, $this->ignoredFields(), true))
            ->values()
            ->all();
    }

    private function changedValues(Document $document): array
    {
        return collect($this->changedFields($document))
            ->mapWithKeys(fn ($field) => [
                $field => [
                    'old' => $document->getOriginal($field),
                    'new' => $document->{$field},
                ],
            ])
            ->reject(fn ($value) => $this->normalizeForCompare($value['old']) === $this->normalizeForCompare($value['new']))
            ->all();
    }

    private function createdValues(Document $document): array
    {
        return collect($this->snapshot($document))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->mapWithKeys(fn ($value, $field) => [
                $field => [
                    'old' => null,
                    'new' => $value,
                ],
            ])
            ->all();
    }

    private function oldValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn ($value, $field) => [
                $field => $value['old'] ?? null,
            ])
            ->all();
    }

    private function newValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn ($value, $field) => [
                $field => $value['new'] ?? null,
            ])
            ->all();
    }

    private function filledSnapshot(Document $document): array
    {
        return collect($this->snapshot($document))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function oldSnapshot(Document $document): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $document->getOriginal($field),
            ])
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function snapshot(Document $document): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $document->{$field},
            ])
            ->all();
    }

    private function auditableFields(): array
    {
        return [
            'id',
            'title',
            'description',
            'file_url',
            'file_path',
            'filename',
            'original_name',
            'mime_type',
            'size',
            'category_id',
            'year',
            'is_active',
            'published_at',
        ];
    }

    private function ignoredFields(): array
    {
        return [
            'updated_at',
            'created_at',
        ];
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && count($value) === 0);
    }

    private function normalizeForCompare(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->normalizeForCompare($item))
                ->values()
                ->all();
        }

        return $value;
    }

    private function requestContext(): array
    {
        $request = request();

        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
