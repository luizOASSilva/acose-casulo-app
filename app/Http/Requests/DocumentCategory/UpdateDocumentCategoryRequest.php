<?php

namespace App\Http\Requests\DocumentCategory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentCategoryRequest extends FormRequest
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
        $id = $this->route('document_category');

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                Rule::unique('document_categories', 'name')->ignore($id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'featured' => [
                'boolean',
            ],
            'order' => [
                'integer',
                'min:0',
            ],
        ];
    }
}
