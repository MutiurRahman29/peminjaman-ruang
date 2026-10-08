<?php

namespace Database\Factories;

use App\Enums\StatusPeminjaman;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory(),
            'id_ruangan' => Ruangan::factory(),
            'tanggal' => fake()->date(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'keperluan' => fake()->sentence(),
            'nama_pemohon' => fake()->name(),
            'email_pemohon' => fake()->safeEmail(),
            'whatsapp_pemohon' => '628'.fake()->numerify('########'),
            'status' => StatusPeminjaman::Menunggu,
        ];
    }
}
