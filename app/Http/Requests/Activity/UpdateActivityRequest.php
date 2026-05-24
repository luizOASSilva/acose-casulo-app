<?php

namespace App\Http\Requests\Activity;

use App\Models\Activity;
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
                'required',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'sometimes',
                'required',
                'string',
            ],

            'image_url' => [
                'sometimes',
                'required',
                'string',
                'max:2048',
            ],

            'image_description' => [
                'sometimes',
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
                'sometimes',
                'required',
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
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->has('schedules')) {
                    return;
                }

                $activityParam = $this->route('activity');

                $activityModel = Activity::query()
                    ->where('id', $activityParam)
                    ->orWhereHas('publication', function ($query) use ($activityParam) {
                        $query->where('slug', $activityParam);
                    })
                    ->first();

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

                    $conflictQuery = ActivitySchedule::query()
                        ->where('weekday', $schedule['weekday'])
                        ->where('start_time', '<', $schedule['end_time'])
                        ->where('end_time', '>', $schedule['start_time']);

                    if ($activityModel) {
                        $conflictQuery->where('activity_id', '!=', $activityModel->id);
                    }

                    $hasConflict = $conflictQuery->exists();

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
