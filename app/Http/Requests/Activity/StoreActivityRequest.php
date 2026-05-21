<?php

namespace App\Http\Requests\Activity;

use App\Models\ActivitySchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'required',
                'string',
            ],

            'image_url' => [
                'required',
                'url',
                'max:2048',
            ],

            'image_description' => [
                'required',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'schedules' => [
                'required',
                'array',
                'min:1',
            ],

            'schedules.*.weekday' => [
                'required',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            ],

            'schedules.*.start_time' => [
                'required',
                'date_format:H:i',
            ],

            'schedules.*.end_time' => [
                'required',
                'date_format:H:i',
                'after:schedules.*.start_time',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
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

