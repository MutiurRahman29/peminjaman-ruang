<?php

namespace Tests\Feature;

use App\Enums\KondisiFasilitas;
use App\Enums\StatusPeminjaman;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use App\Services\FacilityAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PeminjamanFacilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_without_facilities_is_successful(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create()))
            ->assertRedirect();

        $this->assertDatabaseCount('peminjaman', 1);
        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_one_or_multiple_facilities_are_stored_as_details(): void
    {
        $user = User::factory()->peminjam()->create();
        $ruangan = Ruangan::factory()->create();
        $proyektor = Fasilitas::factory()->create(['jumlah' => 3]);
        $laptop = Fasilitas::factory()->create(['jumlah' => 4]);

        $this->actingAs($user)
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'fasilitas' => [
                    $proyektor->id_fasilitas => 1,
                    $laptop->id_fasilitas => 2,
                ],
            ]))
            ->assertRedirect();

        $peminjaman = Peminjaman::query()->firstOrFail();
        $this->assertDatabaseCount('detail_peminjaman', 2);
        $this->assertDatabaseHas('detail_peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_fasilitas' => $proyektor->id_fasilitas,
            'jumlah' => 1,
        ]);
        $this->assertDatabaseHas('detail_peminjaman', [
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_fasilitas' => $laptop->id_fasilitas,
            'jumlah' => 2,
        ]);
    }

    public function test_blank_facility_quantities_do_not_create_details(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'fasilitas' => [$fasilitas->id_fasilitas => ''],
            ]))
            ->assertRedirect();

        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_facility_keys_that_normalize_to_the_same_id_are_rejected(): void
    {
        $fasilitas = Fasilitas::factory()->create();
        $duplicateKey = '0'.$fasilitas->id_fasilitas;

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'fasilitas' => [
                    $fasilitas->id_fasilitas => 1,
                    $duplicateKey => 1,
                ],
            ]))
            ->assertSessionHasErrors([
                'fasilitas' => 'Pilihan fasilitas tidak boleh duplikat.',
            ]);

        $this->assertDatabaseCount('peminjaman', 0);
        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_zero_negative_or_non_integer_facility_quantities_are_rejected(): void
    {
        $user = User::factory()->peminjam()->create();
        $fasilitas = Fasilitas::factory()->create();

        foreach ([0, -1, 'satu'] as $jumlah) {
            $this->actingAs($user)
                ->from(route('peminjam.peminjaman.create'))
                ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                    'fasilitas' => [$fasilitas->id_fasilitas => $jumlah],
                ]))
                ->assertSessionHasErrors('fasilitas.'.$fasilitas->id_fasilitas);
        }
    }

    public function test_invalid_damaged_or_empty_stock_facilities_are_rejected(): void
    {
        $user = User::factory()->peminjam()->create();
        $ruangan = Ruangan::factory()->create();
        $rusak = Fasilitas::factory()->create(['kondisi' => KondisiFasilitas::Rusak]);
        $stokKosong = Fasilitas::factory()->create(['jumlah' => 0]);

        foreach ([99999, $rusak->id_fasilitas, $stokKosong->id_fasilitas] as $idFasilitas) {
            $this->actingAs($user)
                ->from(route('peminjam.peminjaman.create'))
                ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                    'fasilitas' => [$idFasilitas => 1],
                ]))
                ->assertSessionHasErrors('fasilitas.'.$idFasilitas);
        }
    }

    public function test_requested_quantity_cannot_exceed_total_stock_and_validation_leaves_no_loan(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 2,
        ]);

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'fasilitas' => [$fasilitas->id_fasilitas => 3],
            ]))
            ->assertSessionHasErrors([
                'fasilitas.'.$fasilitas->id_fasilitas => 'Stok Proyektor pada jadwal tersebut hanya tersedia 2.',
            ]);

        $this->assertDatabaseCount('peminjaman', 0);
        $this->assertDatabaseCount('detail_peminjaman', 0);
    }

    public function test_overlapping_approved_loans_are_grouped_and_reduce_stock_across_rooms(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 5,
        ]);
        $this->createExistingLoan(Ruangan::factory()->create(), $fasilitas, 2, StatusPeminjaman::Disetujui);
        $this->createExistingLoan(Ruangan::factory()->create(), $fasilitas, 2, StatusPeminjaman::Disetujui);

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload(Ruangan::factory()->create(), [
                'fasilitas' => [$fasilitas->id_fasilitas => 2],
            ]))
            ->assertSessionHasErrors([
                'fasilitas.'.$fasilitas->id_fasilitas => 'Stok Proyektor pada jadwal tersebut hanya tersedia 1.',
            ]);
    }

    public function test_negative_remaining_stock_is_reported_as_zero(): void
    {
        $fasilitas = Fasilitas::factory()->create([
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 2,
        ]);
        $ruanganDenganPemakaian = Ruangan::factory()->create();
        $ruanganPengajuan = Ruangan::factory()->create();
        $this->createExistingLoan($ruanganDenganPemakaian, $fasilitas, 3, StatusPeminjaman::Disetujui);

        $stokTersedia = app(FacilityAvailabilityService::class)->availableQuantities(
            [$fasilitas->id_fasilitas],
            now(config('app.timezone'))->addDay()->toDateString(),
            '08:00',
            '09:00',
        );

        $this->assertSame(0, $stokTersedia[$fasilitas->id_fasilitas]);

        $this->actingAs(User::factory()->peminjam()->create())
            ->from(route('peminjam.peminjaman.create'))
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruanganPengajuan, [
                'fasilitas' => [$fasilitas->id_fasilitas => 1],
            ]))
            ->assertSessionHasErrors([
                'fasilitas.'.$fasilitas->id_fasilitas => 'Stok Proyektor pada jadwal tersebut hanya tersedia 0.',
            ]);
    }

    public function test_pending_loans_do_not_reduce_stock_and_boundary_times_allow_full_stock(): void
    {
        $fasilitas = Fasilitas::factory()->create(['jumlah' => 2]);
        $ruangan = Ruangan::factory()->create();
        $this->createExistingLoan($ruangan, $fasilitas, 2, StatusPeminjaman::Menunggu);

        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'fasilitas' => [$fasilitas->id_fasilitas => 2],
            ]))
            ->assertRedirect();

        $approved = $this->createExistingLoan(
            $ruangan,
            $fasilitas,
            2,
            StatusPeminjaman::Disetujui,
            '09:00',
            '10:00',
        );

        $this->actingAs(User::factory()->peminjam()->create())
            ->post(route('peminjam.peminjaman.store'), $this->validPayload($ruangan, [
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:00',
                'fasilitas' => [$fasilitas->id_fasilitas => 2],
            ]))
            ->assertRedirect();

        $this->assertNotNull($approved->id_peminjaman);
    }

    public function test_form_lists_only_healthy_facilities_with_positive_stock(): void
    {
        Ruangan::factory()->create();
        $baik = Fasilitas::factory()->create(['nama_fasilitas' => 'Baik', 'jumlah' => 1]);
        $rusak = Fasilitas::factory()->create(['nama_fasilitas' => 'Rusak', 'kondisi' => KondisiFasilitas::Rusak]);
        $kosong = Fasilitas::factory()->create(['nama_fasilitas' => 'Kosong', 'jumlah' => 0]);

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.peminjaman.create'))
            ->assertOk()
            ->assertSee($baik->nama_fasilitas)
            ->assertDontSee($rusak->nama_fasilitas)
            ->assertDontSee($kosong->nama_fasilitas);
    }

    public function test_history_and_detail_display_facilities_with_eager_loading(): void
    {
        $user = User::factory()->peminjam()->create();
        $fasilitas = Fasilitas::factory()->create(['nama_fasilitas' => 'Proyektor']);
        $peminjaman = $this->createExistingLoan(Ruangan::factory()->create(), $fasilitas, 1, StatusPeminjaman::Menunggu, user: $user);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)
            ->get(route('peminjam.peminjaman.index'))
            ->assertOk()
            ->assertSee('1 jenis fasilitas');

        $facilityQueries = collect(DB::getQueryLog())
            ->filter(function (array $query): bool {
                $sql = str_replace(['`', '"'], '', strtolower($query['query']));

                return str_contains($sql, 'from fasilitas');
            })
            ->count();

        $this->assertSame(1, $facilityQueries);

        $this->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Proyektor: 1');
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
            'konfirmasi' => '1',
            ...$overrides,
        ];
    }

    private function createExistingLoan(
        Ruangan $ruangan,
        Fasilitas $fasilitas,
        int $jumlah,
        StatusPeminjaman $status,
        string $jamMulai = '08:00',
        string $jamSelesai = '09:00',
        ?User $user = null,
    ): Peminjaman {
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => $user?->id_user ?? User::factory()->peminjam()->create()->id_user,
            'id_ruangan' => $ruangan->id_ruangan,
            'tanggal' => now(config('app.timezone'))->addDay()->toDateString(),
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'status' => $status,
        ]);

        DB::table('peminjaman')
            ->where('id_peminjaman', $peminjaman->id_peminjaman)
            ->update(['tanggal' => now(config('app.timezone'))->addDay()->toDateString()]);

        $peminjaman->detailPeminjaman()->create([
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'jumlah' => $jumlah,
        ]);

        return $peminjaman;
    }
}
