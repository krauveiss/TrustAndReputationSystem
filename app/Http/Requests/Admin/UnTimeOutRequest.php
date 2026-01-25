<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UnTimeOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->role->name == "admin") {
            return true;
        }
        return false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
