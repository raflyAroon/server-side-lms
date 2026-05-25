<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => 'Kode OTP harus 6 digit.',
        ];
    }
}