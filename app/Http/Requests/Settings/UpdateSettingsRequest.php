<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->role === 'master';
    }

    protected function prepareForValidation(): void
    {
        $settings = $this->input('settings', []);

        if (! is_array($settings)) {
            return;
        }

        $urlKeysToNormalize = [
            'facebook_url',
            'instagram_url',
            'youtube_url',
            'google_maps_url',
            'google_maps_embed_url',
        ];

        $normalizedSettings = collect($settings)
            ->map(function ($setting) use ($urlKeysToNormalize) {
                if (! is_array($setting)) {
                    return $setting;
                }

                $key = $setting['key'] ?? null;
                $value = $setting['value'] ?? null;

                if (
                    is_string($key) &&
                    in_array($key, $urlKeysToNormalize, true) &&
                    is_string($value)
                ) {
                    $cleanValue = trim($value);

                    if (
                        $cleanValue !== '' &&
                        ! str_starts_with($cleanValue, 'http://') &&
                        ! str_starts_with($cleanValue, 'https://')
                    ) {
                        $setting['value'] = 'https://' . $cleanValue;
                    } else {
                        $setting['value'] = $cleanValue;
                    }
                }

                return $setting;
            })
            ->values()
            ->toArray();

        $this->merge([
            'settings' => $normalizedSettings,
        ]);
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],

            'settings.*.key' => [
                'required',
                'string',
                'max:100',
                'exists:settings,key',
            ],

            'settings.*.value' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Nenhuma configuração foi enviada.',
            'settings.array' => 'As configurações devem ser enviadas em formato de lista.',
            'settings.min' => 'Envie ao menos uma configuração.',

            'settings.*.key.required' => 'A chave da configuração é obrigatória.',
            'settings.*.key.string' => 'A chave da configuração deve ser um texto.',
            'settings.*.key.max' => 'A chave da configuração deve ter no máximo 100 caracteres.',
            'settings.*.key.exists' => 'Uma das configurações enviadas não existe.',

            'settings.*.value.string' => 'O valor da configuração deve ser um texto.',
            'settings.*.value.max' => 'O valor da configuração deve ter no máximo 2048 caracteres.',
        ];
    }
}
