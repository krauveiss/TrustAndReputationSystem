<?php

namespace App\Http\Requests\Violations;

use Illuminate\Foundation\Http\FormRequest;

class AddViolationRequest extends FormRequest
{

    public function authorize(): bool
    {
        if ($this->user()->role->name == "moderator" || $this->user()->role->name == "admin") {
            return true;
        }
        return false;
    }


    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:spam,abuse,cheating'],
            'severity' => ['required', 'in:major,minor,critical'],
        ];
    }
}
