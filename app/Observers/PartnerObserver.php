<?php

namespace App\Observers;

use App\Models\Partner;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PartnerObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Partner $partner): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getPartnerName($partner);

        AdminActionLogger::log(
            action: 'partner.created',
            title: 'Parceiro criado',
            description: $this->adminName() . ' criou o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'website_url' => $partner->website_url ?? null,
                'is_active' => $partner->is_active ?? null,
            ]
        );
    }

    public function updated(Partner $partner): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($partner)) {
            return;
        }

        $name = $this->getPartnerName($partner);

        AdminActionLogger::log(
            action: 'partner.updated',
            title: 'Parceiro atualizado',
            description: $this->adminName() . ' atualizou o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'changed_fields' => $this->changedFields($partner),
            ]
        );
    }

    public function deleted(Partner $partner): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getPartnerName($partner);

        AdminActionLogger::log(
            action: 'partner.deleted',
            title: 'Parceiro removido',
            description: $this->adminName() . ' removeu o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'website_url' => $partner->website_url ?? null,
            ]
        );
    }

    private function getPartnerName(Partner $partner): string
    {
        return $partner->name ?: 'Parceiro #' . $partner->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Partner $partner): bool
    {
        return count($this->changedFields($partner)) > 0;
    }

    private function changedFields(Partner $partner): array
    {
        return collect(array_keys($partner->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}
