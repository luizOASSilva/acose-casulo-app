<?php

namespace App\Http\Requests\Article;

use App\Support\RichTextSanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => RichTextSanitizer::clean($this->input('content')),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'required',
                'string',
            ],

            'image_url' => [
                'required',
                'string',
                'max:2048',
            ],

            'image_description' => [
                'required',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'summary' => [
                'required',
                'string',
                'max:160',
            ],

            'keywords' => [
                'sometimes',
                'array',
            ],

            'keywords.*' => [
                'string',
                'max:255',
                'distinct',
            ],
        ];
    }
}
