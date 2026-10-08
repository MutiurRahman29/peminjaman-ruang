<?php

namespace App\Http\Requests\Peminjam;

use App\Enums\KondisiFasilitas;
use App\Enums\StatusRuangan;
use App\Models\Fasilitas;
use App\Services\FacilityAvailabilityService;
use App\Services\LoanScheduleService;
use App\Services\RoomAvailabilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePeminjamanRequest extends FormRequest
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
            'id_ruangan' => [
                'required',
                'integer',
                Rule::exists('ruangan', 'id_ruangan')->where(
                    fn ($query) => $query->where('status', StatusRuangan::Tersedia->value),
                ),
            ],
            'nama_pemohon' => ['required', 'string', 'max:100'],
            'email_pemohon' => ['required', 'email', 'max:255'],
            'whatsapp_pemohon' => ['required', 'regex:/^62\d{8,14}$/'],
            'tanggal' => ['required', 'date', 'after_or_equal:'.now(config('app.timezone'))->toDateString()],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'keperluan' => ['required', 'string', 'max:1000'],
            'akses_password' => ['required', 'string', 'min:6', 'confirmed'],
            'konfirmasi' => ['required', 'accepted'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => ['nullable', 'integer', 'min:1'],
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
            'id_ruangan.required' => 'Ruangan wajib dipilih.',
            'id_ruangan.integer' => 'Ruangan yang dipilih tidak valid atau tidak tersedia.',
            'id_ruangan.exists' => 'Ruangan yang dipilih tidak valid atau tidak tersedia.',
            'nama_pemohon.required' => 'Nama pemohon wajib diisi.',
            'nama_pemohon.string' => 'Nama pemohon harus berupa teks.',
            'nama_pemohon.max' => 'Nama pemohon maksimal 100 karakter.',
            'email_pemohon.required' => 'Email wajib diisi.',
            'email_pemohon.email' => 'Email harus berupa alamat email yang valid.',
            'email_pemohon.max' => 'Email maksimal 255 karakter.',
            'whatsapp_pemohon.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_pemohon.regex' => 'Nomor WhatsApp harus diawali 62 diikuti 8–14 digit.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal harus berupa tanggal yang valid.',
            'tanggal.after_or_equal' => 'Tanggal harus hari ini atau setelahnya.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'keperluan.string' => 'Keperluan harus berupa teks.',
            'keperluan.max' => 'Keperluan maksimal 1000 karakter.',
            'akses_password.required' => 'Kata sandi wajib diisi.',
            'akses_password.min' => 'Kata sandi minimal 6 karakter.',
            'akses_password.confirmed' => 'Kata sandi tidak cocok.',
            'konfirmasi.required' => 'Konfirmasi persetujuan wajib dipilih.',
            'konfirmasi.accepted' => 'Anda harus menyetujui bahwa data yang dimasukkan benar.',
            'fasilitas.array' => 'Daftar fasilitas tidak valid.',
            'fasilitas.*.integer' => 'Jumlah fasilitas harus berupa bilangan bulat.',
            'fasilitas.*.min' => 'Jumlah fasilitas minimal 1.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_ruangan' => 'ruangan',
            'nama_pemohon' => 'nama pemohon',
            'email_pemohon' => 'email',
            'whatsapp_pemohon' => 'nomor WhatsApp',
            'tanggal' => 'tanggal',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'keperluan' => 'keperluan',
            'akses_password' => 'kata sandi',
            'akses_password_confirmation' => 'konfirmasi kata sandi',
            'konfirmasi' => 'konfirmasi',
            'fasilitas' => 'fasilitas',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->hasValidFacilityKeys($validator)) {
                return;
            }

            if (app(LoanScheduleService::class)->hasStarted(
                (string) $this->input('tanggal'),
                (string) $this->input('jam_mulai'),
            )) {
                $validator->errors()->add('jam_mulai', 'Jam mulai harus setelah waktu saat ini.');

                return;
            }

            $pilihanFasilitas = $this->selectedFasilitas();

            $hasConflict = app(RoomAvailabilityService::class)->hasConflict(
                (int) $this->input('id_ruangan'),
                (string) $this->input('tanggal'),
                (string) $this->input('jam_mulai'),
                (string) $this->input('jam_selesai'),
            );

            if ($hasConflict) {
                $validator->errors()->add(
                    'id_ruangan',
                    'Ruangan sudah digunakan pada jadwal yang dipilih.',
                );
            }

            if ($pilihanFasilitas === []) {
                return;
            }

            $fasilitas = Fasilitas::query()
                ->whereIn('id_fasilitas', array_column($pilihanFasilitas, 'id_fasilitas'))
                ->get()
                ->keyBy('id_fasilitas');

            foreach ($pilihanFasilitas as $pilihan) {
                $item = $fasilitas->get($pilihan['id_fasilitas']);

                if ($item === null || $item->kondisi !== KondisiFasilitas::Baik || $item->jumlah < 1) {
                    $validator->errors()->add(
                        'fasilitas.'.$pilihan['id_fasilitas'],
                        'Fasilitas yang dipilih tidak valid atau tidak tersedia.',
                    );
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $stokTersedia = app(FacilityAvailabilityService::class)->availableQuantities(
                array_column($pilihanFasilitas, 'id_fasilitas'),
                (string) $this->input('tanggal'),
                (string) $this->input('jam_mulai'),
                (string) $this->input('jam_selesai'),
            );

            foreach ($pilihanFasilitas as $pilihan) {
                $tersedia = $stokTersedia[$pilihan['id_fasilitas']] ?? 0;

                if ($pilihan['jumlah'] > $tersedia) {
                    $validator->errors()->add(
                        'fasilitas.'.$pilihan['id_fasilitas'],
                        "Stok {$fasilitas[$pilihan['id_fasilitas']]->nama_fasilitas} pada jadwal tersebut hanya tersedia {$tersedia}.",
                    );
                }
            }
        });
    }

    /**
     * Get normalized facility selections with blank quantities removed.
     *
     * @return array<int, array{id_fasilitas: int, jumlah: int}>
     */
    public function selectedFasilitas(): array
    {
        return collect($this->input('fasilitas', []))
            ->filter(fn ($jumlah): bool => $jumlah !== null && $jumlah !== '')
            ->map(fn ($jumlah, $id): array => [
                'id_fasilitas' => (int) $id,
                'jumlah' => (int) $jumlah,
            ])
            ->values()
            ->all();
    }

    /**
     * Ensure each submitted facility array key is a positive integer.
     */
    private function hasValidFacilityKeys(Validator $validator): bool
    {
        $normalizedIds = [];

        foreach (array_keys($this->input('fasilitas', [])) as $id) {
            if (! ctype_digit((string) $id) || (int) $id < 1) {
                $validator->errors()->add('fasilitas', 'Fasilitas yang dipilih tidak valid atau tidak tersedia.');

                return false;
            }

            $normalizedId = (int) $id;

            if (in_array($normalizedId, $normalizedIds, true)) {
                $validator->errors()->add('fasilitas', 'Pilihan fasilitas tidak boleh duplikat.');

                return false;
            }

            $normalizedIds[] = $normalizedId;
        }

        return true;
    }
}
