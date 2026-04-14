<?php

namespace Cosmoferrigno\PasswordRotation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required', 'string', 'current_password'],
            'password'              => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Hash::check($value, $this->user()->password)) {
                        $fail('La nuova password deve essere diversa da quella attuale.');
                    }
                },
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'La password attuale non è corretta.',
        ];
    }
}
