<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestAdminEmailChangeRequest extends FormRequest
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
        $admin = $this->route('admin');
        $adminId = is_object($admin) ? $admin->id : $admin;

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'E-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'E-mail deve ter no máximo 255 caracteres.',
            'email.unique' => 'Este e-mail já está em uso por outro administrador.',
        ];
    }
}
