<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusRuangan;
use App\Models\Ruangan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class RuanganRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $ruangan = $this->route('ruangan');

        return [
            'nama_ruangan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ruangan', 'nama_ruangan')->ignore(
                    $ruangan instanceof Ruangan ? $ruangan->id_ruangan : null,
                    'id_ruangan',
                ),
            ],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'lokasi' => ['required', 'string', 'max:150'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'status' => ['required', new Enum(StatusRuangan::class)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
            'nama_ruangan.string' => 'Nama ruangan harus berupa teks.',
            'nama_ruangan.max' => 'Nama ruangan maksimal 100 karakter.',
            'nama_ruangan.unique' => 'Nama ruangan sudah digunakan.',
            'kapasitas.required' => 'Kapasitas wajib diisi.',
            'kapasitas.integer' => 'Kapasitas harus berupa angka bulat.',
            'kapasitas.min' => 'Kapasitas minimal 1.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'lokasi.string' => 'Lokasi harus berupa teks.',
            'lokasi.max' => 'Lokasi maksimal 150 karakter.',
            'gambar.image' => 'File gambar harus berupa gambar yang valid.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 10 MB.',
            'status.required' => 'Status ruangan wajib dipilih.',
            'status.enum' => 'Status ruangan tidak valid.',
        ];
    }
}
