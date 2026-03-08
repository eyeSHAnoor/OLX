<?php
// app/Http/Requests/ChangePasswordRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages()
    {
        return [
            'current_password.current_password' => 'The current password is incorrect.',
            'new_password.different' => 'New password must be different from current password.',
        ];
    }
}