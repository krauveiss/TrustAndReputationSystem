<?php

namespace App\Http\Requests\Violations;

use Illuminate\Foundation\Http\FormRequest;

class ChangeViolationStatus extends FormRequest
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
            'violation_id' => ['required', 'exists:violations,id'],
            'status' => ['required', 'string', 'in:active,canceled'],
            'comment' => ['string', 'max:50']
        ];
    }
}
