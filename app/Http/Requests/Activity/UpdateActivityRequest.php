<?php

namespace App\Http\Requests\Activity;

use App\Models\ActivitySchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'sometimes',
                'string',
            ],

            'image_url' => [
                'sometimes',
                'url',
                'max:2048',
            ],

            'image_description' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'schedules' => [
                'sometimes',
                'array',
                'min:1',
            ],

            'schedules.*.weekday' => [
                'required_with:schedules',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ],

            'schedules.*.start_time' => [
                'required_with:schedules',
                'date_format:H:i',
            ],

            'schedules.*.end_time' => [
                'required_with:schedules',
                'date_format:H:i',
                'after:schedules.*.start_time',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!$this->has('schedules')) {
                    return;
                }

                $activity = $this->route('activity');
                $schedules = $this->input('schedules', []);

                foreach ($schedules as $index => $schedule) {
                    if (
                        empty($schedule['weekday']) ||
                        empty($schedule['start_time']) ||
                        empty($schedule['end_time'])
                    ) {
                        continue;
                    }

                    $hasConflict = ActivitySchedule::query()
                        ->where('weekday', $schedule['weekday'])
                        ->where('start_time', '<', $schedule['end_time'])
                        ->where('end_time', '>', $schedule['start_time'])
                        ->where('activity_id', '!=', $activity->id)
                        ->exists();

                    if ($hasConflict) {
                        $validator->errors()->add(
                            "schedules.$index.start_time",
                            'Já existe uma atividade cadastrada nesse dia e horário.'
                        );
                    }

                    foreach ($schedules as $otherIndex => $otherSchedule) {
                        if ($index === $otherIndex) {
                            continue;
                        }

                        if (
                            ($schedule['weekday'] ?? null) === ($otherSchedule['weekday'] ?? null) &&
                            ($schedule['start_time'] ?? null) < ($otherSchedule['end_time'] ?? null) &&
                            ($schedule['end_time'] ?? null) > ($otherSchedule['start_time'] ?? null)
                        ) {
                            $validator->errors()->add(
                                "schedules.$index.start_time",
                                'Existem horários sobrepostos nesta atividade.'
                            );
                        }
                    }
                }
            },
        ];
    }
}

