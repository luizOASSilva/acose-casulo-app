<?php

namespace App\Http\Requests\DocumentCategory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:document_categories,name',
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

