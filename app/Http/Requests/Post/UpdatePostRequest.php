<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
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
        return [
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
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

            'likes' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }
}
