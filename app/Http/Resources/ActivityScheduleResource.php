<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);

        return [
            'id' => $this->id,
            'weekday' => $this->weekday,
            'weekday_label' => $this->weekdayLabel($this->weekday, $locale),
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? 'pt-BR'
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => 'en',
            default => 'pt-BR',
        };
    }

    private function weekdayLabel(?string $weekday, string $locale): string
    {
        $labels = [
            'pt-BR' => [
                'monday' => 'Segunda-feira',
                'tuesday' => 'Terça-feira',
                'wednesday' => 'Quarta-feira',
                'thursday' => 'Quinta-feira',
                'friday' => 'Sexta-feira',
                'saturday' => 'Sábado',
                'sunday' => 'Domingo',
            ],

            'en' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
        ];

        return $labels[$locale][$weekday] ?? $weekday ?? '';
    }
}
