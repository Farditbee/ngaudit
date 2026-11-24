<?php

namespace App\Http\Requests\Master;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WilayahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rawCode = (string) $this->input('code', '');
        $normalizedCode = preg_replace('/[^0-9A-Za-z]/', '', $rawCode) ?? '';

        $level = (string) $this->input('level', '');
        if ($level === '') {
            $level = $this->inferLevel();
        }

        $this->merge([
            'code' => $normalizedCode,
            'level' => $level,
        ]);
    }

    protected function inferLevel(): string
    {
        if ($this->filled('village_name')) {
            return 'village';
        }

        if ($this->filled('district_name')) {
            return 'district';
        }

        if ($this->filled('city_name')) {
            return 'city';
        }

        return 'province';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');
        $level = (string) $this->input('level', 'province');
        $originalCode = (string) $this->route('code', '');

        $tableMap = [
            'province' => $prefix.'provinces',
            'city' => $prefix.'cities',
            'district' => $prefix.'districts',
            'village' => $prefix.'villages',
        ];

        $expectedLengths = [
            'province' => 2,
            'city' => 4,
            'district' => 6,
            'village' => 10,
        ];

        $table = $tableMap[$level] ?? ($prefix.'provinces');
        $expectedLength = $expectedLengths[$level] ?? 2;

        return [
            'level' => ['required', 'string', Rule::in(['province', 'city', 'district', 'village'])],
            'province_code' => [
                Rule::requiredIf(in_array($level, ['city', 'district', 'village'], true)),
                'nullable',
                'string',
                Rule::exists($prefix.'provinces', 'code'),
            ],
            'city_code' => [
                Rule::requiredIf(in_array($level, ['district', 'village'], true)),
                'nullable',
                'string',
                Rule::exists($prefix.'cities', 'code'),
                function (string $attribute, mixed $value, Closure $fail) use ($level): void {
                    if (! in_array($level, ['district', 'village'], true)) {
                        return;
                    }
                    $provinceCode = (string) $this->input('province_code', '');
                    if ($provinceCode !== '' && ! str_starts_with((string) $value, $provinceCode)) {
                        $fail('Kabupaten/Kota yang dipilih tidak berada di bawah Provinsi tersebut.');
                    }
                },
            ],
            'district_code' => [
                Rule::requiredIf($level === 'village'),
                'nullable',
                'string',
                Rule::exists($prefix.'districts', 'code'),
                function (string $attribute, mixed $value, Closure $fail) use ($level): void {
                    if ($level !== 'village') {
                        return;
                    }
                    $cityCode = (string) $this->input('city_code', '');
                    if ($cityCode !== '' && ! str_starts_with((string) $value, $cityCode)) {
                        $fail('Kecamatan yang dipilih tidak berada di bawah Kabupaten/Kota tersebut.');
                    }
                },
            ],
            'province_name' => [
                Rule::requiredIf($level === 'province'),
                'nullable',
                'string',
                'max:255',
            ],
            'city_name' => [
                Rule::requiredIf($level === 'city'),
                'nullable',
                'string',
                'max:255',
            ],
            'district_name' => [
                Rule::requiredIf($level === 'district'),
                'nullable',
                'string',
                'max:255',
            ],
            'village_name' => [
                Rule::requiredIf($level === 'village'),
                'nullable',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                "size:{$expectedLength}",
                function (string $attribute, mixed $value, Closure $fail) use ($level, $table, $originalCode): void {
                    $code = (string) $value;

                    $parentCode = match ($level) {
                        'city' => (string) $this->input('province_code', ''),
                        'district' => (string) $this->input('city_code', ''),
                        'village' => (string) $this->input('district_code', ''),
                        default => '',
                    };

                    $parentLabel = match ($level) {
                        'city' => 'Provinsi',
                        'district' => 'Kabupaten/Kota',
                        'village' => 'Kecamatan',
                        default => '',
                    };

                    if ($parentCode !== '' && ! str_starts_with($code, $parentCode)) {
                        $fail("Kode wilayah harus diawali dengan kode induk {$parentLabel} ({$parentCode}).");

                        return;
                    }

                    $existsQuery = DB::table($table)->where('code', $code);
                    if ($originalCode !== '') {
                        $existsQuery->where('code', '!=', $originalCode);
                    }

                    if ($existsQuery->exists()) {
                        $fail("Kode wilayah {$code} sudah terdaftar, tidak boleh ada kode yang sama.");
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode wilayah wajib diisi.',
            'code.regex' => 'Kode wilayah hanya boleh berisi angka (atau dipisahkan titik).',
            'code.size' => 'Panjang kode wilayah harus :size digit sesuai tingkat wilayah.',
            'province_code.required' => 'Provinsi induk wajib dipilih.',
            'city_code.required' => 'Kabupaten/Kota induk wajib dipilih.',
            'district_code.required' => 'Kecamatan induk wajib dipilih.',
            'province_name.required' => 'Nama provinsi wajib diisi.',
            'city_name.required' => 'Nama kabupaten/kota wajib diisi.',
            'district_name.required' => 'Nama kecamatan wajib diisi.',
            'village_name.required' => 'Nama desa/kelurahan wajib diisi.',
        ];
    }
}
