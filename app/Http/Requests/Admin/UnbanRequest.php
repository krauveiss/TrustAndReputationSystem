<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UnbanRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'reputation' => ['required', 'integer'],
        ];
    }
}
