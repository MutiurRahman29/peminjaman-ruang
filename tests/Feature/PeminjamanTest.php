<?php

namespace Tests\Feature;

use App\Enums\StatusPeminjaman;
use App\Enums\StatusRuangan;
use App\Enums\KondisiFasilitas;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use App\Notifications\PeminjamanBaru;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_uses_the_jakarta_application_timezone(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
    }

    public function test_guest_can_open_the_public_loan_form(): void
    {
        $this->get(route('peminjam.peminjaman.create'))->assertOk();
    }

    public function test_guest_can_access_loan_progress_with_number_and_password(): void
    {
        $peminjaman = Peminjaman::factory()->create([
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'akses_password' => bcrypt('akses-ruang-123'),
        ]);

        $this->get(route('peminjam.peminjaman.access'))
            ->assertOk()
            ->assertSee('Cek peminjamanmu.')
            ->assertSee('Nomor pengajuan')
            ->assertSee('Kata sandi');

        $this->followingRedirects()
            ->post(route('peminjam.peminjaman.access'), [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'akses_password' => 'akses-ruang-123',
            ])
            ->assertOk()
            ->assertSee($peminjaman->keperluan);
    }

    public function test_guest_cannot_access_loan_with_an_incorrect_password(): void
    {
        $peminjaman = Peminjaman::factory()->create([
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'akses_password' => bcrypt('akses-ruang-123'),
        ]);

        $this->post(route('peminjam.peminjaman.access'), [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'akses_password' => 'salah1',
        ])
            ->assertRedirect(route('peminjam.peminjaman.access'))
            ->assertSessionHasErrors('akses_password');
    }

    public function test_loan_access_password_is_stored_as_a_hash(): void
    {
        $peminjaman = Peminjaman::factory()->create([
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'akses_password' => bcrypt('akses-ruang-123'),
        ]);

        $this->assertNotSame('akses-ruang-123', $peminjaman->fresh()->akses_password);
        $this->assertTrue(Hash::check('akses-ruang-123', $peminjaman->fresh()->akses_password));
    }

    public function test_admin_and_staff_cannot_open_borrower_loan_routes(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->petugas()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('peminjam.peminjaman.index'))
                ->assertForbidden();
        }
    }

    public function test_borrower_can_open_the_request_form_and_only_available_rooms_are_listed(): void
    {
        $tersedia = Ruangan::factory()->create([
            'nama_ruangan' => 'Ruang Tersedia',
            'status' => StatusRuangan::Tersedia,
            'gambar' => 'ruangan/ruang-tersedia.jpg',
        ]);
        $digunakan = Ruangan::factory()->create([
            'nama_ruangan' => 'Ruang Digunakan',
            'status' => StatusRuangan::Digunakan,
        ]);

        $this->get(route('peminjam.peminjaman.create'))
            ->assertOk()
            ->assertSee($tersedia->nama_ruangan)
            ->assertSee('/storage/ruangan/ruang-tersedia.jpg', false)
            ->assertDontSee($digunakan->nama_ruangan)
            ->assertSee('Nama pemohon')
            ->assertSee('Email pemohon')
            ->assertSee('Nomor WhatsApp');
    }

    public function test_request_form_displays_the_uploaded_facility_image(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Kamera Dokumentasi',
            'kondisi' => KondisiFasilitas::Baik,
            'jumlah' => 1,
            'gambar' => 'fasilitas/kamera-dokumentasi.jpg',
        ]);

        $this->get(route('peminjam.peminjaman.create'))
            ->assertOk()
            ->assertViewHas('fasilitas', fn ($items): bool => $items->contains(
                fn (Fasilitas $item): bool => $item->id_fasilitas === $fasilitas->id_fasilitas
                    && $item->gambar === 'fasilitas/kamera-dokumentasi.jpg'
                    && str_ends_with($item->gambar_url, '/fasilitas/kamera-dokumentasi.jpg'),
            ))
            ->assertSee($fasilitas->nama_fasilitas);
    }

    public function test_request_form_caps_facility_quantity_at_available_stock(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Mikrofon',
            'kondisi' => KondisiFasilitas::Baik,
            'jumlah' => 3,
        ]);

        $response = $this->get(route('peminjam.peminjaman.create'));

        $response
            ->assertOk()
            ->assertSee('maks. 3 unit')
            ->assertSee('Mikrofon');
    }

    public function test_request_rejects_facility_quantity_above_stock_remaining_for_schedule(): void
    {
        $ruanganExisting = Ruangan::factory()->create();
        $ruanganRequest = Ruangan::factory()->create();
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Speaker',
            'kondisi' => KondisiFasilitas::Baik,
            'jumlah' => 5,
        ]);
        $approvedLoan = $this->createExistingLoan($ruanganExisting, StatusPeminjaman::Disetujui);
        $approvedLoan->detailPeminjaman()->create([
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'jumlah' => 4,
        ]);

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruanganRequest, [
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:00',
                'fasilitas' => [$fasilitas->id_fasilitas => 2],
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors([
                'fasilitas.'.$fasilitas->id_fasilitas => 'Stok Speaker pada jadwal tersebut hanya tersedia 1.',
            ]);
    }

    public function test_valid_request_can_be_created_without_an_account_and_is_pending(): void
    {
        $ruangan = Ruangan::factory()->create();

        $response = $this->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan) + [
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'status' => StatusPeminjaman::Disetujui->value,
            'konfirmasi' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('peminjaman', [
            'id_user' => null,
            'id_ruangan' => $ruangan->id_ruangan,
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'status' => StatusPeminjaman::Menunggu->value,
        ]);
        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_new_loan_request_notifies_all_admins(): void
    {
        $ruangan = Ruangan::factory()->create();
        $adminA = User::factory()->admin()->create();
        $adminB = User::factory()->admin()->create();

        $response = $this->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan) + [
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'konfirmasi' => '1',
        ]);

        $response->assertRedirect();

        $notification = PeminjamanBaru::class;
        $adminANotification = $adminA->notifications()->first();
        $adminBNotification = $adminB->notifications()->first();

        $this->assertNotNull($adminANotification);
        $this->assertNotNull($adminBNotification);
        $this->assertSame($notification, $adminANotification->type);
        $this->assertSame($notification, $adminBNotification->type);
        $this->assertSame('Budi Santoso', $adminANotification->data['nama_pemohon']);
        $this->assertSame('Budi Santoso', $adminBNotification->data['nama_pemohon']);
    }

    public function test_missing_confirmation_is_rejected(): void
    {
        $ruangan = Ruangan::factory()->create();

        $this->followingRedirects()
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan))
            ->assertOk()
            ->assertSee('Konfirmasi persetujuan wajib dipilih.');

        $this->assertDatabaseCount('peminjaman', 0);
    }

    public function test_past_dates_are_rejected(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'tanggal' => now(config('app.timezone'))->subDay()->toDateString(),
            ]))
            ->assertRedirect(route('peminjam.peminjaman.create'))
            ->assertSessionHasErrors([
                'tanggal' => 'Tanggal harus hari ini atau setelahnya.',
            ]);
    }

    public function test_today_schedule_that_has_started_is_rejected(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-09 10:00:00', config('app.timezone')));

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'tanggal' => '2026-09-09',
                'jam_mulai' => '09:00',
                'jam_selesai' => '11:00',
                'konfirmasi' => '1',
            ]))
            ->assertRedirect(route('peminjam.peminjaman.create'))
            ->assertSessionHasErrors([
                'jam_mulai' => 'Jam mulai harus setelah waktu saat ini.',
            ]);

        $this->assertDatabaseCount('peminjaman', 0);
    }

    public function test_end_time_must_be_later_than_start_time(): void
    {
        $user = User::factory()->peminjam()->create();
        $ruangan = Ruangan::factory()->create();

        $this->actingAs($user)
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '09:00',
                'jam_selesai' => '09:00',
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors([
                'jam_selesai' => 'Jam selesai harus setelah jam mulai.',
            ]);

        $this->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '09:00',
                'jam_selesai' => '08:30',
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_invalid_or_unavailable_rooms_are_rejected(): void
    {
        $user = User::factory()->peminjam()->create();
        $digunakan = Ruangan::factory()->create(['status' => StatusRuangan::Digunakan]);

        $this->actingAs($user)
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($digunakan, [
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors([
                'id_ruangan' => 'Ruangan yang dipilih tidak valid atau tidak tersedia.',
            ]);

        $this->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($digunakan, [
                'id_ruangan' => 99999,
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors('id_ruangan');
    }

    public function test_overlapping_approved_loan_is_rejected(): void
    {
        $ruangan = Ruangan::factory()->create();
        $this->createExistingLoan($ruangan, StatusPeminjaman::Disetujui);

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '09:30',
                'jam_selesai' => '10:30',
                'konfirmasi' => '1',
            ]))
            ->assertSessionHasErrors('id_ruangan');
    }

    public function test_a_request_can_start_when_an_approved_loan_ends(): void
    {
        $ruangan = Ruangan::factory()->create();
        $this->createExistingLoan($ruangan, StatusPeminjaman::Disetujui);

        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:00',
                'konfirmasi' => '1',
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('peminjaman', 2);
    }

    public function test_pending_loan_does_not_block_a_new_request(): void
    {
        $ruangan = Ruangan::factory()->create();
        $this->createExistingLoan($ruangan, StatusPeminjaman::Menunggu);

        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '09:30',
                'jam_selesai' => '10:30',
                'konfirmasi' => '1',
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('peminjaman', 2);
    }

    public function test_index_only_displays_loans_owned_by_the_authenticated_user(): void
    {
        $user = User::factory()->peminjam()->create();
        $own = Peminjaman::factory()->create([
            'id_user' => $user->id_user,
            'keperluan' => 'Keperluan milik saya',
        ]);
        $other = Peminjaman::factory()->create([
            'keperluan' => 'Keperluan milik pengguna lain',
        ]);

        $this->actingAs($user)
            ->get(route('peminjam.peminjaman.index'))
            ->assertOk()
            ->assertSee($own->keperluan)
            ->assertDontSee($other->keperluan);
    }

    public function test_user_can_view_their_own_loan_detail(): void
    {
        $user = User::factory()->peminjam()->create();
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => $user->id_user,
            'keperluan' => 'Detail milik saya',
        ]);

        $this->actingAs($user)
            ->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Detail milik saya');
    }

    public function test_user_cannot_view_another_users_loan_detail(): void
    {
        $peminjaman = Peminjaman::factory()->create();

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertForbidden();
    }

    public function test_empty_purpose_is_rejected_by_server_validation(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'keperluan' => '',
            ]))
            ->assertSessionHasErrors([
                'keperluan' => 'Keperluan wajib diisi.',
            ]);
    }

    public function test_history_and_detail_display_times_without_seconds(): void
    {
        $user = User::factory()->peminjam()->create();
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => $user->id_user,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('peminjam.peminjaman.index'))
            ->assertOk()
            ->assertSee('08:00–10:00')
            ->assertDontSee('08:00:00');

        $this->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('08:00–10:00')
            ->assertDontSee('10:00:00');
    }

    public function test_validation_error_is_only_displayed_once_in_the_form(): void
    {
        $response = $this->actingAs(User::factory()->peminjam()->create())
            ->followingRedirects()
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'keperluan' => '',
            ]));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'Keperluan wajib diisi.'));
        $response->assertSee('Periksa kembali formulir');
        $response->assertSee('Tautan merah menunjukkan field yang belum lengkap atau tidak valid.');
    }

    /**
     * Get a valid loan request payload.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(Ruangan $ruangan, array $overrides = []): array
    {
        return [
            'id_ruangan' => $ruangan->id_ruangan,
            'tanggal' => now(config('app.timezone'))->addDay()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
            'keperluan' => 'Rapat pengembangan aplikasi',
            'nama_pemohon' => 'Budi Santoso',
            'email_pemohon' => 'budi@example.com',
            'whatsapp_pemohon' => '6281234567890',
            'akses_password' => 'akses-ruang-123',
            'akses_password_confirmation' => 'akses-ruang-123',
            ...$overrides,
        ];
    }

    /**
     * Create a loan fixture with a DATE value that mirrors the MySQL column.
     */
    private function createExistingLoan(Ruangan $ruangan, StatusPeminjaman $status): Peminjaman
    {
        $peminjaman = Peminjaman::factory()->create([
            'id_ruangan' => $ruangan->id_ruangan,
            'tanggal' => now(config('app.timezone'))->addDay()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'status' => $status,
        ]);

        DB::table('peminjaman')
            ->where('id_peminjaman', $peminjaman->id_peminjaman)
            ->update(['tanggal' => now(config('app.timezone'))->addDay()->toDateString()]);

        return $peminjaman;
    }
}
