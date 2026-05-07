<?php

namespace App\Http\Requests\Donation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255'],
            'cpf'          => ['sometimes', 'string', 'min:11', 'max:14'],
            'zip_code'     => ['sometimes', 'nullable', 'string', 'max:9'],
            'city'         => ['sometimes', 'nullable', 'string', 'max:100'],
            'street'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'number'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'neighborhood' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'        => ['sometimes', 'nullable', 'string', 'max:2'],
            'size'         => ['sometimes', 'nullable', Rule::in(['PP', 'P', 'M', 'G', 'GG', '3G'])],
        ];
    }
}

