<?php

namespace App\Http\Requests\Peminjam;

use App\Models\Peminjaman;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccessPeminjamanRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id_peminjaman' => [
                'required',
                'integer',
                Rule::exists(Peminjaman::class, 'id_peminjaman'),
            ],
            'akses_password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * Get custom validation messages for errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_peminjaman.required' => 'Nomor pengajuan wajib diisi.',
            'id_peminjaman.integer' => 'Nomor pengajuan tidak valid.',
            'id_peminjaman.exists' => 'Nomor pengajuan tidak ditemukan.',
            'akses_password.required' => 'Kata sandi wajib diisi.',
            'akses_password.min' => 'Kata sandi minimal 6 karakter.',
        ];
    }
}
