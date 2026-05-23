<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin');
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:svg,png,jpg,jpeg,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Envie uma imagem.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.mimes' => 'A imagem deve ser SVG, PNG, JPG, JPEG ou WEBP.',
            'file.max' => 'A imagem deve ter no máximo 4MB.',
        ];
    }
}
