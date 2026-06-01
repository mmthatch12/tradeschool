<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_id'     => ['required', 'exists:payments,id'],
            'payment_method' => ['required', 'in:credit_card,bank_transfer,cash'],
        ];
    }
}
