<?php

namespace App\Http\Requests\Admin;

use App\Enums\KondisiFasilitas;
use App\Models\Fasilitas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class FasilitasRequest extends FormRequest
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
        $fasilitas = $this->route('fasilitas');

        return [
            'nama_fasilitas' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fasilitas', 'nama_fasilitas')->ignore(
                    $fasilitas instanceof Fasilitas ? $fasilitas->id_fasilitas : null,
                    'id_fasilitas',
                ),
            ],
            'jumlah' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', new Enum(KondisiFasilitas::class)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
            'nama_fasilitas.required' => 'Nama fasilitas wajib diisi.',
            'nama_fasilitas.string' => 'Nama fasilitas harus berupa teks.',
            'nama_fasilitas.max' => 'Nama fasilitas maksimal 100 karakter.',
            'nama_fasilitas.unique' => 'Nama fasilitas sudah digunakan.',
            'jumlah.required' => 'Jumlah fasilitas wajib diisi.',
            'jumlah.integer' => 'Jumlah fasilitas harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah fasilitas minimal 0.',
            'kondisi.required' => 'Kondisi fasilitas wajib dipilih.',
            'kondisi.enum' => 'Kondisi fasilitas tidak valid.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'gambar.image' => 'File gambar harus berupa gambar yang valid.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 10 MB.',
        ];
    }
}
