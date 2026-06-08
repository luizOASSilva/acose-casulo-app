<?php

namespace App\Http\Requests\Activity;

use App\Models\ActivitySchedule;
use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => RichTextSanitizer::clean($this->input('content')),
            ]);
        }
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
                'string',
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

                    if ($schedule['end_time'] <= $schedule['start_time']) {
                        $validator->errors()->add(
                            "schedules.$index.end_time",
                            'O horário de fim deve ser depois do horário de início.'
                        );

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
                            empty($otherSchedule['weekday']) ||
                            empty($otherSchedule['start_time']) ||
                            empty($otherSchedule['end_time'])
                        ) {
                            continue;
                        }

                        if (
                            $schedule['weekday'] === $otherSchedule['weekday'] &&
                            $schedule['start_time'] < $otherSchedule['end_time'] &&
                            $schedule['end_time'] > $otherSchedule['start_time']
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
