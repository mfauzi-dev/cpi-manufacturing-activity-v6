<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            // 'work_date' => ['required', 'date', 'after_or_equal:'. now()->toDateString()],
            'work_date' => ['required', 'date'],
            'hours' => ['required', 'integer', 'min:1'],
            'rate_per_hour' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_date.after_or_equal' => 'Tanggal lembur tidak boleh sebelum hari ini.',
        ];
    }
}
