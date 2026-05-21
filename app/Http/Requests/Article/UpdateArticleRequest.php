<?php

namespace App\Http\Requests\Article;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'sometimes',
                'string',
            ],

            'image_url' => [
                'sometimes',
                'url',
                'max:2048',
            ],

            'image_description' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

            'summary' => [
                'sometimes',
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
