<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'min:3', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],

            // Antispam
            'website' => ['nullable', 'string', 'max:200'],
            'started_at' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'O nome precisa ter pelo menos :min caracteres.',
            'name.max' => 'O nome pode ter no máximo :max caracteres.',

            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail pode ter no máximo :max caracteres.',

            'phone.max' => 'O telefone pode ter no máximo :max caracteres.',

            'subject.required' => 'Informe o assunto.',
            'subject.min' => 'O assunto precisa ter pelo menos :min caracteres.',
            'subject.max' => 'O assunto pode ter no máximo :max caracteres.',

            'message.required' => 'Informe sua mensagem.',
            'message.min' => 'A mensagem precisa ter pelo menos :min caracteres.',
            'message.max' => 'A mensagem pode ter no máximo :max caracteres.',

            'started_at.required' => 'Não foi possível validar o envio.',
            'started_at.integer' => 'Não foi possível validar o envio.',
        ];
    }
}
