<?php

namespace Tests\Feature;

use App\Enums\StatusPeminjaman;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_history_and_completion_routes(): void
    {
        $peminjaman = $this->createLoan();

        $this->get(route('petugas.peminjaman.history'))->assertRedirect(route('admin.login'));
        $this->patch(route('petugas.peminjaman.complete', $peminjaman))->assertRedirect(route('admin.login'));
    }

    public function test_admin_and_borrower_are_forbidden_from_history_and_completion_routes(): void
    {
        $peminjaman = $this->createLoan(status: StatusPeminjaman::Disetujui);

        foreach ([User::factory()->admin()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('petugas.peminjaman.history'))
                ->assertForbidden();

            $this->patch(route('petugas.peminjaman.complete', $peminjaman))
                ->assertForbidden();
        }
    }

    public function test_staff_can_view_processed_history_while_queue_stays_pending_only(): void
    {
        $pending = $this->createLoan(keperluan: 'Menunggu dalam antrean');
        $approved = $this->createLoan(status: StatusPeminjaman::Disetujui, keperluan: 'Disetujui dalam riwayat');
        $rejected = $this->createLoan(status: StatusPeminjaman::Ditolak, keperluan: 'Ditolak dalam riwayat');
        $completed = $this->createLoan(status: StatusPeminjaman::Selesai, keperluan: 'Selesai dalam riwayat');
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->get(route('petugas.peminjaman.index'))
            ->assertOk()
            ->assertSee($pending->keperluan)
            ->assertDontSee($approved->keperluan)
            ->assertDontSee($rejected->keperluan)
            ->assertDontSee($completed->keperluan);

        $this->get(route('petugas.peminjaman.history'))
            ->assertOk()
            ->assertSee($approved->keperluan)
            ->assertSee($rejected->keperluan)
            ->assertSee($completed->keperluan)
            ->assertDontSee($pending->keperluan);

        $this->get(route('petugas.peminjaman.show', $completed))
            ->assertOk()
            ->assertSee($completed->keperluan);
    }

    public function test_staff_can_complete_an_approved_loan_that_has_ended_without_changing_related_data(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 1, 2, 10, 0, 0, 'Asia/Jakarta'));

        try {
            $fasilitas = Fasilitas::factory()->create(['jumlah' => 5]);
            $peminjaman = $this->createLoan(
                status: StatusPeminjaman::Disetujui,
                tanggal: '2026-01-02',
                jamMulai: '08:00',
                jamSelesai: '09:00',
                fasilitas: [$fasilitas->id_fasilitas => 2],
            );
            $idUser = $peminjaman->id_user;
            $idRuangan = $peminjaman->id_ruangan;

            $this->actingAs(User::factory()->petugas()->create())
                ->patch(route('petugas.peminjaman.complete', $peminjaman))
                ->assertRedirect(route('petugas.peminjaman.show', $peminjaman))
                ->assertSessionHas('success', 'Peminjaman berhasil ditandai selesai.');

            $this->assertDatabaseHas('peminjaman', [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'id_user' => $idUser,
                'id_ruangan' => $idRuangan,
                'status' => StatusPeminjaman::Selesai->value,
            ]);
            $this->assertSame(5, $fasilitas->fresh()->jumlah);
            $this->assertDatabaseHas('detail_peminjaman', [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'id_fasilitas' => $fasilitas->id_fasilitas,
                'jumlah' => 2,
            ]);
        } finally {
            $this->travelBack();
        }
    }

    public function test_pending_rejected_and_completed_loans_cannot_be_completed(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 1, 2, 10, 0, 0, 'Asia/Jakarta'));

        try {
            $petugas = User::factory()->petugas()->create();

            foreach ([StatusPeminjaman::Menunggu, StatusPeminjaman::Ditolak, StatusPeminjaman::Selesai] as $status) {
                $peminjaman = $this->createLoan(status: $status, tanggal: '2026-01-02', jamSelesai: '09:00');

                $this->actingAs($petugas)
                    ->patch(route('petugas.peminjaman.complete', $peminjaman))
                    ->assertRedirect(route('petugas.peminjaman.show', $peminjaman))
                    ->assertSessionHas('error', 'Peminjaman ini tidak dapat diselesaikan.');

                $this->assertDatabaseHas('peminjaman', [
                    'id_peminjaman' => $peminjaman->id_peminjaman,
                    'status' => $status->value,
                ]);
            }
        } finally {
            $this->travelBack();
        }
    }

    public function test_approved_loan_cannot_be_completed_before_its_end_time(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 1, 2, 9, 0, 0, 'Asia/Jakarta'));

        try {
            $peminjaman = $this->createLoan(
                status: StatusPeminjaman::Disetujui,
                tanggal: '2026-01-02',
                jamSelesai: '10:00',
            );

            $this->actingAs(User::factory()->petugas()->create())
                ->patch(route('petugas.peminjaman.complete', $peminjaman))
                ->assertRedirect(route('petugas.peminjaman.show', $peminjaman))
                ->assertSessionHas('error', 'Peminjaman belum dapat diselesaikan sebelum jadwal berakhir.');

            $this->assertDatabaseHas('peminjaman', [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'status' => StatusPeminjaman::Disetujui->value,
            ]);
        } finally {
            $this->travelBack();
        }
    }

    public function test_completion_at_the_exact_end_time_uses_the_application_timezone(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 1, 2, 10, 0, 0, 'UTC'));

        try {
            $this->assertSame('Asia/Jakarta', config('app.timezone'));
            $this->assertSame('2026-01-02 17:00', now(config('app.timezone'))->format('Y-m-d H:i'));

            $peminjaman = $this->createLoan(
                status: StatusPeminjaman::Disetujui,
                tanggal: '2026-01-02',
                jamSelesai: '17:00',
            );

            $this->actingAs(User::factory()->petugas()->create())
                ->patch(route('petugas.peminjaman.complete', $peminjaman))
                ->assertSessionHas('success', 'Peminjaman berhasil ditandai selesai.');

            $this->assertDatabaseHas('peminjaman', [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'status' => StatusPeminjaman::Selesai->value,
            ]);
        } finally {
            $this->travelBack();
        }
    }

    public function test_complete_button_only_appears_for_approved_loans_and_disappears_after_completion(): void
    {
        $this->travelTo(CarbonImmutable::create(2026, 1, 2, 10, 0, 0, 'Asia/Jakarta'));

        try {
            $approved = $this->createLoan(status: StatusPeminjaman::Disetujui, tanggal: '2026-01-02', jamSelesai: '09:00');
            $pending = $this->createLoan(status: StatusPeminjaman::Menunggu, tanggal: '2026-01-02', jamSelesai: '09:00');
            $completed = $this->createLoan(status: StatusPeminjaman::Selesai, tanggal: '2026-01-02', jamSelesai: '09:00');
            $petugas = User::factory()->petugas()->create();

            $this->actingAs($petugas)
                ->get(route('petugas.peminjaman.show', $approved))
                ->assertSee('Tandai Selesai');

            $this->get(route('petugas.peminjaman.show', $pending))
                ->assertDontSee('Tandai Selesai');

            $this->get(route('petugas.peminjaman.show', $completed))
                ->assertDontSee('Tandai Selesai');

            $this->patch(route('petugas.peminjaman.complete', $approved))
                ->assertSessionHas('success');

            $this->get(route('petugas.peminjaman.show', $approved))
                ->assertDontSee('Tandai Selesai');
        } finally {
            $this->travelBack();
        }
    }

    /**
     * Create a loan fixture with a DATE value that mirrors the MySQL column.
     *
     * @param  array<int, int>  $fasilitas
     */
    private function createLoan(
        StatusPeminjaman $status = StatusPeminjaman::Menunggu,
        string $tanggal = '2026-01-02',
        string $jamMulai = '08:00',
        string $jamSelesai = '09:00',
        array $fasilitas = [],
        string $keperluan = 'Peminjaman untuk pengujian',
    ): Peminjaman {
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => User::factory()->peminjam()->create()->id_user,
            'id_ruangan' => Ruangan::factory()->create()->id_ruangan,
            'tanggal' => $tanggal,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'keperluan' => $keperluan,
            'status' => $status,
        ]);

        DB::table('peminjaman')
            ->where('id_peminjaman', $peminjaman->id_peminjaman)
            ->update(['tanggal' => $tanggal]);

        foreach ($fasilitas as $idFasilitas => $jumlah) {
            $peminjaman->detailPeminjaman()->create([
                'id_fasilitas' => $idFasilitas,
                'jumlah' => $jumlah,
            ]);
        }

        return $peminjaman;
    }
}
