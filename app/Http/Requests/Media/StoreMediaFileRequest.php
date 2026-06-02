<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMediaFileRequest extends FormRequest
{
    private array $allowedExtensions = [
        'svg',
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    private array $allowedMimeTypes = [
        'image/svg+xml',
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/pjpeg',
        'image/webp',
    ];

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
                'max:8192',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('file');

            if (! $file || ! $file->isValid()) {
                return;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $clientMimeType = strtolower((string) $file->getClientMimeType());
            $detectedMimeType = strtolower((string) $file->getMimeType());

            $hasAllowedExtension = in_array(
                $extension,
                $this->allowedExtensions,
                true
            );

            $hasAllowedMimeType =
                in_array($clientMimeType, $this->allowedMimeTypes, true) ||
                in_array($detectedMimeType, $this->allowedMimeTypes, true);

            if (! $hasAllowedExtension && ! $hasAllowedMimeType) {
                $validator->errors()->add(
                    'file',
                    'A imagem deve ser SVG, PNG, JPG, JPEG ou WEBP.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Envie uma imagem.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.max' => 'A imagem deve ter no máximo 8MB.',
        ];
    }
}
