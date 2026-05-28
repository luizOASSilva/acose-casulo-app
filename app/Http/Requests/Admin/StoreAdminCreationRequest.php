<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminCreationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'master';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email'),
            ],

            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'master']),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome é obrigatório.',
            'name.min' => 'Nome deve ter ao menos 3 caracteres.',
            'email.required' => 'E-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso por outro administrador.',
            'role.required' => 'Nível de acesso é obrigatório.',
            'role.in' => 'Nível de acesso inválido.',
            'is_active.boolean' => 'Status inválido.',
        ];
    }
}
