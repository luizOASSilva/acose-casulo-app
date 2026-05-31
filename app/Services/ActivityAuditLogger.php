<?php

namespace App\Services;

use App\Models\Activity;
use App\Support\AuditDiff;
use Illuminate\Http\Request;

class ActivityAuditLogger
{
    public static function snapshot(Activity $activity): array
    {
        $activity->loadMissing([
            'publication.media',
            'publication.admin',
            'schedules',
        ]);

        return [
            'title' => $activity->publication?->title,
            'content' => $activity->publication?->content,
            'image_url' => $activity->publication?->media?->url,
            'image_description' => $activity->publication?->media?->alt_text,
            'image_caption' => $activity->publication?->media?->caption,
            'schedules' => self::scheduleSnapshot($activity),
        ];
    }

    public static function created(Activity $activity, Request $request): void
    {
        if (! self::shouldLog()) {
            return;
        }

        $activity->loadMissing([
            'publication.media',
            'publication.admin',
            'schedules',
        ]);

        $snapshot = self::snapshot($activity);
        $newValues = AuditDiff::filterFilled($snapshot);
        $changedValues = AuditDiff::createdValues($snapshot);

        if (empty($changedValues)) {
            return;
        }

        $title = self::activityTitle($activity, $snapshot);

        AdminActionLogger::log(
            action: 'activity.created',
            title: 'Atividade criada',
            description: self::adminName() . ' criou a atividade "' . $title . '".',
            subject: $activity,
            subjectName: $title,
            properties: [
                'activity_id' => $activity->id,
                'publication_id' => $activity->publication_id,
                'title' => $title,
                'slug' => $activity->publication?->slug,
                'changed_fields' => array_keys($changedValues),
                'new_values' => $newValues,
                'changed_values' => $changedValues,
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    public static function updated(
        Activity $activity,
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

        $activity->loadMissing([
            'publication.media',
            'publication.admin',
            'schedules',
        ]);

        $title = self::activityTitle($activity, $after);

        AdminActionLogger::log(
            action: 'activity.updated',
            title: 'Atividade atualizada',
            description: self::adminName() . ' atualizou a atividade "' . $title . '".',
            subject: $activity,
            subjectName: $title,
            properties: [
                'activity_id' => $activity->id,
                'publication_id' => $activity->publication_id,
                'title' => $title,
                'slug' => $activity->publication?->slug,
                'changed_fields' => array_keys($changedValues),
                'old_values' => AuditDiff::oldValuesFromChanges($changedValues),
                'new_values' => AuditDiff::newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    public static function deleted(
        Activity $activity,
        array $snapshot,
        Request $request
    ): void {
        if (! self::shouldLog()) {
            return;
        }

        $activity->loadMissing([
            'publication.media',
            'publication.admin',
            'schedules',
        ]);

        $title = self::activityTitle($activity, $snapshot);

        AdminActionLogger::log(
            action: 'activity.deleted',
            title: 'Atividade removida',
            description: self::adminName() . ' removeu a atividade "' . $title . '".',
            subject: $activity,
            subjectName: $title,
            properties: [
                'activity_id' => $activity->id,
                'publication_id' => $activity->publication_id,
                'title' => $title,
                'slug' => $activity->publication?->slug,
                'old_values' => AuditDiff::filterFilled($snapshot),
                'request' => AuditDiff::requestContext($request),
            ]
        );
    }

    private static function scheduleSnapshot(Activity $activity): array
    {
        $weekdays = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        return $activity->schedules
            ->map(fn ($schedule) => [
                'weekday' => $schedule->weekday,
                'start_time' => substr((string) $schedule->start_time, 0, 5),
                'end_time' => substr((string) $schedule->end_time, 0, 5),
            ])
            ->sortBy(fn ($schedule) => sprintf(
                '%02d-%s-%s',
                $weekdays[$schedule['weekday']] ?? 99,
                $schedule['start_time'],
                $schedule['end_time']
            ))
            ->values()
            ->all();
    }

    private static function activityTitle(Activity $activity, array $values = []): string
    {
        return $values['title']
            ?? $activity->publication?->title
            ?? 'Atividade #' . $activity->id;
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
