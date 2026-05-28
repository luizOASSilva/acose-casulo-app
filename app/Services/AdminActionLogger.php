<?php

namespace App\Services;

use App\Models\AdminActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AdminActionLogger
{
    public static function log(
        string $action,
        string $title,
        ?string $description = null,
        ?Model $subject = null,
        ?string $subjectName = null,
        array $properties = [],
        ?Request $request = null
    ): ?AdminActionLog {
        try {
            $request = $request ?: request();

            $admin = Auth::guard('admin')->user();

            return AdminActionLog::query()->create([
                'admin_id' => $admin?->id,
                'admin_name' => $admin?->name,

                'action' => $action,

                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'subject_name' => $subjectName ?: self::guessSubjectName($subject),

                'title' => $title,
                'description' => $description,

                'properties' => empty($properties) ? null : $properties,

                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private static function guessSubjectName(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        foreach (['title', 'name', 'original_name', 'filename', 'slug'] as $field) {
            if (isset($subject->{$field}) && filled($subject->{$field})) {
                return (string) $subject->{$field};
            }
        }

        if (method_exists($subject, 'publication') && $subject->relationLoaded('publication')) {
            $publication = $subject->getRelation('publication');

            if ($publication && isset($publication->title) && filled($publication->title)) {
                return (string) $publication->title;
            }
        }

        return class_basename($subject) . ' #' . $subject->getKey();
    }
}
