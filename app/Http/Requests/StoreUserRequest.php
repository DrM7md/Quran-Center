<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحية نتحكم فيها من routes middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
            'role' => ['nullable','string'], // اسم الدور
            'permissions'   => ['nullable','array'],
            'permissions.*' => ['string'],

        ];
    }
}
