<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ActivityObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Activity $activity): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $activity->loadMissing('publication');

        $name = $this->getActivityName($activity);

        AdminActionLogger::log(
            action: 'activity.created',
            title: 'Atividade criada',
            description: $this->adminName() . ' criou a atividade "' . $name . '".',
            subject: $activity,
            subjectName: $name,
            properties: [
                'activity_id' => $activity->id,
                'slug' => $activity->slug ?? null,
            ]
        );
    }

    public function updated(Activity $activity): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($activity)) {
            return;
        }

        $activity->loadMissing('publication');

        $name = $this->getActivityName($activity);

        AdminActionLogger::log(
            action: 'activity.updated',
            title: 'Atividade atualizada',
            description: $this->adminName() . ' atualizou a atividade "' . $name . '".',
            subject: $activity,
            subjectName: $name,
            properties: [
                'activity_id' => $activity->id,
                'slug' => $activity->slug ?? null,
                'changed_fields' => $this->changedFields($activity),
            ]
        );
    }

    public function deleted(Activity $activity): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $activity->loadMissing('publication');

        $name = $this->getActivityName($activity);

        AdminActionLogger::log(
            action: 'activity.deleted',
            title: 'Atividade removida',
            description: $this->adminName() . ' removeu a atividade "' . $name . '".',
            subject: $activity,
            subjectName: $name,
            properties: [
                'activity_id' => $activity->id,
                'slug' => $activity->slug ?? null,
            ]
        );
    }

    private function getActivityName(Activity $activity): string
    {
        return $activity->publication?->title
            ?: $activity->title
            ?: $activity->slug
            ?: 'Atividade #' . $activity->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Activity $activity): bool
    {
        return count($this->changedFields($activity)) > 0;
    }

    private function changedFields(Activity $activity): array
    {
        return collect(array_keys($activity->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}

