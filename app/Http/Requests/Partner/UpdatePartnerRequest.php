<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,svg',
                'max:2048',
            ],
            'website_url' => [
                'nullable',
                'url',
            ],
            'bg_color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'order' => [
                'nullable',
                'integer',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
