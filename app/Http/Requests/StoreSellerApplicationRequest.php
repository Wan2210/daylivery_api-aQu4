<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSellerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'shop_address' => ['required', 'string', 'max:2000'],
            'business_category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'documents' => ['sometimes', 'array', 'max:8'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Data pengajuan tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}