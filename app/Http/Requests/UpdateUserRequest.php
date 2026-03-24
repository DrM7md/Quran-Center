<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($userId)],
            'password' => ['nullable','string','min:6','confirmed'], // لو فاضي ما نغيّره
            'role' => ['nullable','string'],
            'permissions'   => ['nullable','array'],
            'permissions.*' => ['string'],

        ];
    }
}
