<?php

namespace Tests\Feature;

use App\Enums\KondisiFasilitas;
use App\Enums\StatusPeminjaman;
use App\Enums\StatusRuangan;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PetugasPeminjamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_staff_routes(): void
    {
        $peminjaman = $this->createLoan();

        $this->get(route('petugas.peminjaman.index'))->assertRedirect(route('admin.login'));
        $this->get(route('petugas.peminjaman.show', $peminjaman))->assertRedirect(route('admin.login'));
    }

    public function test_borrower_and_admin_are_forbidden_from_staff_routes(): void
    {
        $peminjaman = $this->createLoan();

        foreach ([User::factory()->peminjam()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('petugas.peminjaman.index'))
                ->assertForbidden();

            $this->patch(route('petugas.peminjaman.approve', $peminjaman))
                ->assertForbidden();
        }
    }

    public function test_staff_can_view_pending_queue_and_loan_detail(): void
    {
        $pending = $this->createLoan(keperluan: 'Pengajuan menunggu');
        $approved = $this->createLoan(status: StatusPeminjaman::Disetujui, keperluan: 'Pengajuan disetujui');
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->get(route('petugas.peminjaman.index'))
            ->assertOk()
            ->assertSee($pending->keperluan)
            ->assertDontSee($approved->keperluan);

        $this->get(route('petugas.peminjaman.show', $pending))
            ->assertOk()
            ->assertSee($pending->keperluan);
    }

    public function test_staff_can_approve_a_valid_pending_loan(): void
    {
        $peminjaman = $this->createLoan();

        $this->actingAs(User::factory()->petugas()->create())
            ->patch(route('petugas.peminjaman.approve', $peminjaman))
            ->assertRedirect(route('petugas.peminjaman.show', $peminjaman))
            ->assertSessionHas('success', 'Pengajuan peminjaman disetujui.');

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'status' => StatusPeminjaman::Disetujui->value,
        ]);
    }

    public function test_staff_can_reject_a_valid_pending_loan(): void
    {
        $peminjaman = $this->createLoan();

        $this->actingAs(User::factory()->petugas()->create())
            ->patch(route('petugas.peminjaman.reject', $peminjaman))
            ->assertRedirect(route('petugas.peminjaman.show', $peminjaman))
            ->assertSessionHas('success', 'Pengajuan peminjaman ditolak.');

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'status' => StatusPeminjaman::Ditolak->value,
        ]);
    }

    public function test_non_pending_loans_cannot_be_processed_again(): void
    {
        $petugas = User::factory()->petugas()->create();

        foreach ([StatusPeminjaman::Disetujui, StatusPeminjaman::Ditolak, StatusPeminjaman::Selesai] as $status) {
            $peminjaman = $this->createLoan(status: $status);

            $this->actingAs($petugas)
                ->from(route('petugas.peminjaman.show', $peminjaman))
                ->patch(route('petugas.peminjaman.reject', $peminjaman))
                ->assertSessionHas('error', 'Pengajuan ini tidak dapat diproses lagi.');

            $this->assertDatabaseHas('peminjaman', [
                'id_peminjaman' => $peminjaman->id_peminjaman,
                'status' => $status->value,
            ]);
        }
    }

    public function test_pending_loan_cannot_be_approved_after_its_schedule_has_started(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-09 10:00:00', config('app.timezone')));

        $peminjaman = $this->createLoan(
            tanggal: '2026-09-09',
            jamMulai: '09:00',
            jamSelesai: '11:00',
        );

        $this->actingAs(User::factory()->petugas()->create())
            ->patch(route('petugas.peminjaman.approve', $peminjaman))
            ->assertSessionHas('error', 'Pengajuan tidak dapat disetujui karena jadwal sudah dimulai atau berlalu.');

        $this->assertPendingAndMasterStockUnchanged($peminjaman);
    }

    public function test_second_overlapping_approval_for_the_same_room_fails_and_stays_pending(): void
    {
        $ruangan = Ruangan::factory()->create();
        $pertama = $this->createLoan(ruangan: $ruangan);
        $kedua = $this->createLoan(ruangan: $ruangan);
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->patch(route('petugas.peminjaman.approve', $pertama))
            ->assertSessionHas('success');

        $this->from(route('petugas.peminjaman.show', $kedua))
            ->patch(route('petugas.peminjaman.approve', $kedua))
            ->assertSessionHas('error', 'Jadwal ruangan sudah bentrok dengan peminjaman yang disetujui.');

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $kedua->id_peminjaman,
            'status' => StatusPeminjaman::Menunggu->value,
        ]);
    }

    public function test_room_that_is_no_longer_available_cannot_be_approved(): void
    {
        $ruangan = Ruangan::factory()->create();
        $peminjaman = $this->createLoan(ruangan: $ruangan);
        $ruangan->update(['status' => StatusRuangan::Digunakan]);

        $this->actingAs(User::factory()->petugas()->create())
            ->from(route('petugas.peminjaman.show', $peminjaman))
            ->patch(route('petugas.peminjaman.approve', $peminjaman))
            ->assertSessionHas('error', 'Ruangan tidak tersedia untuk disetujui.');

        $this->assertPendingAndMasterStockUnchanged($peminjaman);
    }

    public function test_damaged_facility_cannot_be_approved(): void
    {
        $fasilitas = Fasilitas::factory()->create(['jumlah' => 3]);
        $peminjaman = $this->createLoan(fasilitas: [$fasilitas->id_fasilitas => 1]);
        $fasilitas->update(['kondisi' => KondisiFasilitas::Rusak]);

        $this->actingAs(User::factory()->petugas()->create())
            ->from(route('petugas.peminjaman.show', $peminjaman))
            ->patch(route('petugas.peminjaman.approve', $peminjaman))
            ->assertSessionHas('error', 'Fasilitas pada pengajuan tidak tersedia untuk disetujui.');

        $this->assertPendingAndMasterStockUnchanged($peminjaman, $fasilitas, 3);
    }

    public function test_approved_usage_in_another_room_reduces_available_facility_stock(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 3,
        ]);
        $pertama = $this->createLoan(
            ruangan: Ruangan::factory()->create(),
            fasilitas: [$fasilitas->id_fasilitas => 2],
        );
        $kedua = $this->createLoan(
            ruangan: Ruangan::factory()->create(),
            fasilitas: [$fasilitas->id_fasilitas => 2],
        );
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->patch(route('petugas.peminjaman.approve', $pertama))
            ->assertSessionHas('success');

        $this->from(route('petugas.peminjaman.show', $kedua))
            ->patch(route('petugas.peminjaman.approve', $kedua))
            ->assertSessionHas('error', 'Stok Proyektor pada jadwal tersebut hanya tersedia 1.');

        $this->assertPendingAndMasterStockUnchanged($kedua, $fasilitas, 3);
    }

    public function test_touching_time_ranges_can_both_be_approved(): void
    {
        $ruangan = Ruangan::factory()->create();
        $pertama = $this->createLoan(ruangan: $ruangan, jamMulai: '08:00', jamSelesai: '09:00');
        $kedua = $this->createLoan(ruangan: $ruangan, jamMulai: '09:00', jamSelesai: '10:00');
        $petugas = User::factory()->petugas()->create();

        $this->actingAs($petugas)
            ->patch(route('petugas.peminjaman.approve', $pertama))
            ->assertSessionHas('success');

        $this->patch(route('petugas.peminjaman.approve', $kedua))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $kedua->id_peminjaman,
            'status' => StatusPeminjaman::Disetujui->value,
        ]);
    }

    public function test_loan_without_facilities_can_be_approved(): void
    {
        $peminjaman = $this->createLoan();

        $this->actingAs(User::factory()->petugas()->create())
            ->patch(route('petugas.peminjaman.approve', $peminjaman))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'status' => StatusPeminjaman::Disetujui->value,
        ]);
        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_borrower_cannot_view_another_borrowers_loan_after_policy_expansion(): void
    {
        $peminjaman = $this->createLoan();

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertForbidden();
    }

    public function test_dashboard_only_shows_the_queue_link_to_staff(): void
    {
        $this->actingAs(User::factory()->petugas()->create())
            ->get(route('dashboard'))
            ->assertSee('Antrean Peminjaman');

        foreach ([User::factory()->admin()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertDontSee('Antrean Peminjaman');
        }
    }

    /**
     * Create a pending or historical loan fixture using a DATE value that mirrors MySQL.
     *
     * @param  array<int, int>  $fasilitas
     */
    private function createLoan(
        ?Ruangan $ruangan = null,
        StatusPeminjaman $status = StatusPeminjaman::Menunggu,
        array $fasilitas = [],
        string $jamMulai = '08:00',
        string $jamSelesai = '09:00',
        string $keperluan = 'Rapat pengembangan aplikasi',
        ?string $tanggal = null,
    ): Peminjaman {
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => User::factory()->peminjam()->create()->id_user,
            'id_ruangan' => ($ruangan ?? Ruangan::factory()->create())->id_ruangan,
            'tanggal' => $tanggal ?? now(config('app.timezone'))->addDay()->toDateString(),
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'keperluan' => $keperluan,
            'status' => $status,
        ]);

        DB::table('peminjaman')
            ->where('id_peminjaman', $peminjaman->id_peminjaman)
            ->update(['tanggal' => $tanggal ?? now(config('app.timezone'))->addDay()->toDateString()]);

        foreach ($fasilitas as $idFasilitas => $jumlah) {
            $peminjaman->detailPeminjaman()->create([
                'id_fasilitas' => $idFasilitas,
                'jumlah' => $jumlah,
            ]);
        }

        return $peminjaman;
    }

    private function assertPendingAndMasterStockUnchanged(
        Peminjaman $peminjaman,
        ?Fasilitas $fasilitas = null,
        ?int $jumlah = null,
    ): void {
        $this->assertDatabaseHas('peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'status' => StatusPeminjaman::Menunggu->value,
        ]);

        if ($fasilitas instanceof Fasilitas && $jumlah !== null) {
            $this->assertSame($jumlah, $fasilitas->fresh()->jumlah);
        }
    }
}
