<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'nik' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'nik')->ignore($id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'department_id' => [
                'required',
                'integer',
                'exists:departments,id',
            ],

            'position_id' => [
                'required',
                'integer',
                'exists:positions,id',
            ],

            'foundation_id' => [
                'nullable',
                'integer',
                'exists:foundations,id',
            ],

            'employee_type' => [
                'required',
                Rule::in(['BORONGAN', 'OUTSOURCING']),
            ],

            'status' => [
                'nullable',
                Rule::in(['ACTIVE', 'INACTIVE', 'RESIGN']),
            ],

            'base_salary' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
}
