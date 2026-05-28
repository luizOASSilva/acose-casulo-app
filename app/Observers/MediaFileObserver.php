<?php

namespace App\Observers;

use App\Models\MediaFile;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class MediaFileObserver implements ShouldHandleEventsAfterCommit
{
    public function created(MediaFile $mediaFile): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getMediaName($mediaFile);

        AdminActionLogger::log(
            action: 'media.created',
            title: 'Mídia enviada',
            description: $this->adminName() . ' enviou a mídia "' . $name . '".',
            subject: $mediaFile,
            subjectName: $name,
            properties: [
                'media_file_id' => $mediaFile->id,
                'collection' => $mediaFile->collection ?? null,
                'disk' => $mediaFile->disk ?? null,
                'filename' => $mediaFile->filename ?? null,
                'mime_type' => $mediaFile->mime_type ?? null,
                'size' => $mediaFile->size ?? null,
            ]
        );
    }

    public function updated(MediaFile $mediaFile): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($mediaFile)) {
            return;
        }

        $name = $this->getMediaName($mediaFile);

        AdminActionLogger::log(
            action: 'media.updated',
            title: 'Mídia atualizada',
            description: $this->adminName() . ' atualizou a mídia "' . $name . '".',
            subject: $mediaFile,
            subjectName: $name,
            properties: [
                'media_file_id' => $mediaFile->id,
                'changed_fields' => $this->changedFields($mediaFile),
            ]
        );
    }

    public function deleted(MediaFile $mediaFile): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getMediaName($mediaFile);

        AdminActionLogger::log(
            action: 'media.deleted',
            title: 'Mídia removida',
            description: $this->adminName() . ' removeu a mídia "' . $name . '".',
            subject: $mediaFile,
            subjectName: $name,
            properties: [
                'media_file_id' => $mediaFile->id,
                'collection' => $mediaFile->collection ?? null,
                'filename' => $mediaFile->filename ?? null,
                'path' => $mediaFile->path ?? null,
            ]
        );
    }

    private function getMediaName(MediaFile $mediaFile): string
    {
        return $mediaFile->original_name
            ?: $mediaFile->filename
            ?: 'Mídia #' . $mediaFile->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(MediaFile $mediaFile): bool
    {
        return count($this->changedFields($mediaFile)) > 0;
    }

    private function changedFields(MediaFile $mediaFile): array
    {
        return collect(array_keys($mediaFile->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}
