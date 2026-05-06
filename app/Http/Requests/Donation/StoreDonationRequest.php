<?php

namespace App\Http\Requests\Donation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
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
        $hasGift = (float) $this->input('amount', 0) >= 100;

        return [
            'amount' => [
                'required',
                'decimal:0,2',
                'min:5',
            ],
            'name' => [
                $hasGift ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                $hasGift ? 'required' : 'nullable',
                'email',
            ],
            'phone' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'zip_code' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'street' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'number' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'complement' => [
                'nullable',
                'string',
            ],
            'neighborhood' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'city' => [
                $hasGift ? 'required' : 'nullable',
                'string',
            ],
            'state' => [
                $hasGift ? 'required' : 'nullable',
                'string',
                'size:2',
            ]
        ];
    }
}
