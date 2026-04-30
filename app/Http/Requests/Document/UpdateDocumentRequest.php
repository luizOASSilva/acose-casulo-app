<?php

namespace App\Http\Requests\Document;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('document');

        return [
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],

            'file_url' => [
                'sometimes',
                'url',
                'max:2048',
                'regex:/^https:\/\//',
                Rule::unique('documents', 'file_url')->ignore($id),
            ],
        ];
    }
}
