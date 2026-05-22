<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->role === 'master';
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
