<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
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
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email'],
            'phone'             => ['nullable', 'string'],
            'program_id'        => ['required', 'exists:programs,id'],
            'date_of_birth'     => ['nullable', 'date', 'before:-16 years'],
            'desired_start_date' => ['nullable', 'date', 'after:today'],
            'id_document'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'transcript'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
