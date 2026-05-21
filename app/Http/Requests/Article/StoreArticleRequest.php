<?php

namespace App\Http\Requests\Article;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
                'url',
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
