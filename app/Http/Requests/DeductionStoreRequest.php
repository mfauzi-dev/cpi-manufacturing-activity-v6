<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeductionStoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'employee_id'  => ['required', 'exists:employees,id'],
            'type'         => ['required', 'in:BPJS_KESEHATAN,BPJS_KETENAGAKERJAAN,PPH21,TELAT,PINJAMAN,OTHER'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'description'  => ['nullable', 'string', 'max:255'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'  => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required'  => 'Karyawan wajib dipilih.',
            'employee_id.exists'    => 'Karyawan tidak ditemukan.',
            'type.required'         => 'Tipe bonus wajib dipilih.',
            'type.in'               => 'Tipe bonus tidak valid.',
            'amount.required'       => 'Jumlah bonus wajib diisi.',
            'amount.numeric'        => 'Jumlah bonus harus berupa angka.',
            'amount.min'            => 'Jumlah bonus minimal 1.',
            'period_month.required' => 'Bulan periode wajib dipilih.',
            'period_month.min'      => 'Bulan tidak valid.',
            'period_month.max'      => 'Bulan tidak valid.',
            'period_year.required'  => 'Tahun periode wajib diisi.',
            'period_year.min'       => 'Tahun minimal 2000.',
            'period_year.max'       => 'Tahun tidak boleh melebihi tahun ini.',
        ];
    }
}
