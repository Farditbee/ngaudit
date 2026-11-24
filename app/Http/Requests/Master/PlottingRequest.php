<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlottingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $plotting = $this->route('plotting');

        return [
            'plot' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plottings', 'plot')->ignore($plotting),
            ],
            'provinsi_ids' => ['required', 'array', 'min:1'],
            'provinsi_ids.*' => ['integer', 'distinct', Rule::exists('wilayah', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'plot' => 'nama plot',
            'provinsi_ids' => 'daftar provinsi',
            'provinsi_ids.*' => 'provinsi',
        ];
    }
}
