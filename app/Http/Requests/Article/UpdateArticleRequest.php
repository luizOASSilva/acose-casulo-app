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
                'required',
                'string',
                'min:3',
                'max:51',
            ],

            'content' => [
                'sometimes',
                'required',
                'string',
            ],

            'image_url' => [
                'sometimes',
                'required',
                'string',
                'max:2048',
            ],

            'image_description' => [
                'sometimes',
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
                'sometimes',
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
