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
        return (bool) ($this->user('admin') ?? $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $document = $this->route('document');
        $documentId = is_object($document) ? $document->id : $document;

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'file_url' => [
                'sometimes',
                'required',
                'url',
                'max:2048',
                'regex:/^https:\/\//',
                Rule::unique('documents', 'file_url')->ignore($documentId),
            ],

            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:document_categories,id',
            ],

            'year' => [
                'sometimes',
                'required',
                'integer',
                'min:2000',
                'max:' . (date('Y') + 1),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Informe o título do documento.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',

            'file_url.required' => 'Informe o link do documento.',
            'file_url.url' => 'Informe uma URL válida para o documento.',
            'file_url.regex' => 'O link do documento precisa começar com https://.',
            'file_url.unique' => 'Já existe um documento cadastrado com esse link.',
            'file_url.max' => 'O link do documento está muito longo.',

            'category_id.required' => 'Selecione uma categoria.',
            'category_id.integer' => 'A categoria selecionada é inválida.',
            'category_id.exists' => 'A categoria selecionada não existe.',

            'year.required' => 'Informe o ano do documento.',
            'year.integer' => 'O ano precisa ser um número válido.',
            'year.min' => 'O ano informado é muito antigo.',
            'year.max' => 'O ano informado é inválido.',
        ];
    }
}
