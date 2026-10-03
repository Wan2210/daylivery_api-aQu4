<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreDriverApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'identity_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('driver_applications', 'identity_number')->ignore($this->user()?->driverApplication?->id),
            ],
            'license_number' => ['required', 'string', 'max:50'],
            'vehicle_type' => ['required', 'string', 'max:80'],
            'vehicle_number' => ['required', 'string', 'max:30'],
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