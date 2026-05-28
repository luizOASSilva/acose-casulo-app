<?php

namespace App\Support;

use App\Models\AdminActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminAudit
{
    public static function log(
        Request $request,
        string $action,
        ?Model $subject,
        string $title,
        string $description,
        array $properties = []
    ): void {
        $admin = $request->user('admin') ?? $request->user();

        AdminActionLog::query()->create([
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name ?? 'Sistema',

            'action' => $action,

            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_name' => self::subjectName($subject),

            'title' => $title,
            'description' => $description,

            'properties' => $properties,

            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private static function subjectName(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        foreach (['title', 'name', 'filename', 'original_name', 'slug'] as $field) {
            if (isset($subject->{$field}) && filled($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }

        if (method_exists($subject, 'publication')) {
            $publication = $subject->relationLoaded('publication')
                ? $subject->publication
                : $subject->publication()->first();

            if ($publication && filled($publication->title)) {
                return (string) $publication->title;
            }
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }
}
