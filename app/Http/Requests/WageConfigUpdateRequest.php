<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WageConfigUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'tahun'              => ['required', 'integer', 'digits:4', 'unique:wage_configs,tahun,' . $id],
            'ump'                => ['required', 'numeric', 'min:0'],
            'hari_kerja_standar' => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
}