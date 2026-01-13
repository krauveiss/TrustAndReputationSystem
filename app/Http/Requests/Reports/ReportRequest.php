<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_name' => ['required', 'string', 'min:4', 'exists:users,name'],
            'reason' => ['required', 'string', 'max:50'],
        ];
    }
}
