<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
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
            'type' => ['required', Rule::in(['wholesale', 'service', 'cafe', 'contact'])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'message' => ['nullable', 'string', 'max:3000'],
            'privacy_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required_without' => 'Укажите телефон или email.',
            'email.required_without' => 'Укажите email или телефон.',
            'privacy_accepted.accepted' => 'Подтвердите согласие на обработку данных.',
        ];
    }
}
