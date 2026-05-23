<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'logo_url' => [
                'required',
                'string',
                'max:2048',
            ],

            'website_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'bg_color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],

            'order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do parceiro é obrigatório.',
            'name.string' => 'O nome do parceiro deve ser um texto.',
            'name.min' => 'O nome do parceiro deve ter ao menos 2 caracteres.',
            'name.max' => 'O nome do parceiro deve ter no máximo 255 caracteres.',

            'logo_url.required' => 'Selecione ou envie uma logo para o parceiro.',
            'logo_url.string' => 'A logo selecionada é inválida.',
            'logo_url.max' => 'O caminho da logo deve ter no máximo 2048 caracteres.',

            'website_url.url' => 'Informe uma URL válida para o site do parceiro.',
            'website_url.max' => 'A URL do site deve ter no máximo 2048 caracteres.',

            'bg_color.string' => 'A cor de fundo deve ser um texto.',
            'bg_color.regex' => 'Informe uma cor hexadecimal válida. Exemplo: #ffffff.',

            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem não pode ser negativa.',

            'is_active.boolean' => 'O status do parceiro deve ser verdadeiro ou falso.',
        ];
    }
}

