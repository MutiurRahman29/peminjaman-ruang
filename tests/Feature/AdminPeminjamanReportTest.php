<?php

namespace Tests\Feature;

use App\Enums\StatusPeminjaman;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use App\Notifications\PeminjamanBaru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPeminjamanReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_admin_report_routes(): void
    {
        $peminjaman = $this->createLoan();

        $this->get(route('admin.peminjaman.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.peminjaman.show', $peminjaman))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.peminjaman.destroy', $peminjaman))->assertRedirect(route('admin.login'));
    }

    public function test_staff_and_borrower_are_forbidden_from_admin_report_routes(): void
    {
        $peminjaman = $this->createLoan();

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('admin.peminjaman.index'))
                ->assertForbidden();

            $this->get(route('admin.peminjaman.show', $peminjaman))
                ->assertForbidden();

            $this->delete(route('admin.peminjaman.destroy', $peminjaman))
                ->assertForbidden();
        }
    }

    public function test_admin_report_handles_a_loan_without_a_user(): void
    {
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => null,
            'id_ruangan' => Ruangan::factory()->create(),
            'tanggal' => '2026-01-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
            'keperluan' => 'Peminjaman tanpa pengguna',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index'))
            ->assertOk()
            ->assertSee('Tidak tersedia')
            ->assertSee($peminjaman->keperluan);

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.show', $peminjaman))
            ->assertOk()
            ->assertSee('Tidak tersedia');
    }

    public function test_admin_can_view_ordered_report_and_read_only_detail_with_or_without_facilities(): void
    {
        $fasilitas = Fasilitas::factory()->create(['nama_fasilitas' => 'Proyektor']);
        $lama = $this->createLoan(
            tanggal: '2026-01-10',
            jamMulai: '08:00',
            keperluan: 'Peminjaman Lama',
        );
        $terbaru = $this->createLoan(
            tanggal: '2026-01-11',
            jamMulai: '09:00',
            keperluan: 'Peminjaman Terbaru',
            fasilitas: [$fasilitas->id_fasilitas => 2],
        );
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index'))
            ->assertOk()
            ->assertSeeInOrder([$terbaru->keperluan, $lama->keperluan])
            ->assertSee('Setujui')
            ->assertSee('Tolak')
            ->assertSee('Hapus')
            ->assertSee($terbaru->email_pemohon)
            ->assertSee($terbaru->whatsapp_pemohon)
            ->assertDontSee('Tandai Selesai');

        $this->get(route('admin.peminjaman.show', $terbaru))
            ->assertOk()
            ->assertSee('Proyektor: 2')
            ->assertSee('Setujui')
            ->assertSee('Tolak')
            ->assertSee('Hapus Peminjaman')
            ->assertDontSee('Tandai Selesai');

        $this->get(route('admin.peminjaman.show', $lama))
            ->assertOk()
            ->assertSee('Tidak ada fasilitas tambahan.');
    }

    public function test_opening_a_new_loan_notification_marks_it_read_and_hides_it_from_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $peminjaman = $this->createLoan(keperluan: 'Pengajuan notifikasi dibaca');
        $peminjaman->update(['nama_pemohon' => 'Peminjam Notifikasi']);
        $admin->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
        $notification = $admin->unreadNotifications()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertSee('Peminjam Notifikasi mengajukan peminjaman');

        $this->actingAs($admin)
            ->get(route('admin.notifications.open', $notification->id))
            ->assertRedirect(route('admin.peminjaman.show', $peminjaman));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->get(route('dashboard'))
            ->assertDontSee('Peminjam Notifikasi mengajukan peminjaman');
    }

    public function test_admin_cannot_open_another_admins_notification(): void
    {
        $owner = User::factory()->admin()->create();
        $peminjaman = $this->createLoan();
        $owner->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
        $notification = $owner->unreadNotifications()->firstOrFail();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.notifications.open', $notification->id))
            ->assertNotFound();
    }

    public function test_opening_a_notification_for_a_deleted_loan_removes_it_and_returns_to_report(): void
    {
        $admin = User::factory()->admin()->create();
        $peminjaman = $this->createLoan();
        $admin->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
        $notification = $admin->unreadNotifications()->firstOrFail();
        $peminjaman->delete();

        $this->actingAs($admin)
            ->get(route('admin.notifications.open', $notification->id))
            ->assertRedirect(route('admin.peminjaman.index'))
            ->assertSessionHas('error', 'Pengajuan ini sudah tidak tersedia.');

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_opening_a_loan_detail_directly_marks_its_notifications_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        $peminjaman = $this->createLoan();
        $admin->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
        $notification = $admin->unreadNotifications()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.show', $peminjaman))
            ->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_approve_and_reject_pending_loans(): void
    {
        $admin = User::factory()->admin()->create();
        $futureDate = now(config('app.timezone'))->addDay()->toDateString();
        $approved = $this->createLoan(
            status: StatusPeminjaman::Menunggu,
            tanggal: $futureDate,
        );
        $rejected = $this->createLoan(
            status: StatusPeminjaman::Menunggu,
            tanggal: $futureDate,
        );

        $this->actingAs($admin)
            ->patch(route('admin.peminjaman.approve', $approved))
            ->assertRedirect(route('admin.peminjaman.show', $approved->id_peminjaman))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->patch(route('admin.peminjaman.reject', $rejected))
            ->assertRedirect(route('admin.peminjaman.show', $rejected->id_peminjaman))
            ->assertSessionHas('success');

        $this->assertSame(StatusPeminjaman::Disetujui, Peminjaman::findOrFail($approved->id_peminjaman)->status);
        $this->assertSame(StatusPeminjaman::Ditolak, Peminjaman::findOrFail($rejected->id_peminjaman)->status);
    }

    public function test_admin_can_delete_a_loan_and_its_facility_details(): void
    {
        $fasilitas = Fasilitas::factory()->create();
        $peminjaman = $this->createLoan(fasilitas: [$fasilitas->id_fasilitas => 2]);
        $admin = User::factory()->admin()->create();
        $admin->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($admin)
            ->delete('/admin/peminjaman/'.$peminjaman->id_peminjaman)
            ->assertRedirect(route('admin.peminjaman.index'))
            ->assertSessionHas('success', 'Data peminjaman berhasil dihapus.');

        $this->assertDatabaseMissing('peminjaman', ['id_peminjaman' => $peminjaman->id_peminjaman]);
        $this->assertDatabaseMissing('detail_peminjaman', ['id_peminjaman' => $peminjaman->id_peminjaman]);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_each_filter_and_a_combined_filter_limit_the_report(): void
    {
        $userA = User::factory()->peminjam()->create(['nama' => 'Peminjam A']);
        $userB = User::factory()->peminjam()->create(['nama' => 'Peminjam B']);
        $ruanganA = Ruangan::factory()->create(['nama_ruangan' => 'Ruang A']);
        $ruanganB = Ruangan::factory()->create(['nama_ruangan' => 'Ruang B']);
        $menunggu = $this->createLoan($userA, $ruanganA, StatusPeminjaman::Menunggu, '2026-01-10', 'Menunggu A');
        $disetujui = $this->createLoan($userB, $ruanganB, StatusPeminjaman::Disetujui, '2026-01-11', 'Disetujui B');
        $gabungan = $this->createLoan($userA, $ruanganA, StatusPeminjaman::Disetujui, '2026-01-12', 'Disetujui A');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index', ['status' => StatusPeminjaman::Menunggu->value]))
            ->assertSee($menunggu->keperluan)
            ->assertDontSee($disetujui->keperluan);

        $this->get(route('admin.peminjaman.index', ['id_ruangan' => $ruanganB->id_ruangan]))
            ->assertSee($disetujui->keperluan)
            ->assertDontSee($gabungan->keperluan);

        $this->get(route('admin.peminjaman.index', ['id_user' => $userA->id_user]))
            ->assertSee($menunggu->keperluan)
            ->assertSee($gabungan->keperluan)
            ->assertDontSee($disetujui->keperluan);

        $this->get(route('admin.peminjaman.index', [
            'tanggal_mulai' => '2026-01-10',
            'tanggal_selesai' => '2026-01-11',
        ]))
            ->assertSee($menunggu->keperluan)
            ->assertSee($disetujui->keperluan)
            ->assertDontSee($gabungan->keperluan);

        $this->get(route('admin.peminjaman.index', [
            'status' => StatusPeminjaman::Disetujui->value,
            'id_ruangan' => $ruanganA->id_ruangan,
            'id_user' => $userA->id_user,
            'tanggal_mulai' => '2026-01-12',
            'tanggal_selesai' => '2026-01-12',
        ]))
            ->assertSee($gabungan->keperluan)
            ->assertDontSee($menunggu->keperluan)
            ->assertDontSee($disetujui->keperluan);
    }

    public function test_date_range_is_inclusive_and_invalid_filters_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $included = $this->createLoan(tanggal: '2026-01-10', keperluan: 'Batas tanggal');
        $excluded = $this->createLoan(tanggal: '2026-01-11', keperluan: 'Di luar batas');

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index', [
                'tanggal_mulai' => '2026-01-10',
                'tanggal_selesai' => '2026-01-10',
            ]))
            ->assertSee($included->keperluan)
            ->assertDontSee($excluded->keperluan);

        $this->from(route('admin.peminjaman.index'))
            ->get(route('admin.peminjaman.index', [
                'tanggal_mulai' => '2026-01-11',
                'tanggal_selesai' => '2026-01-10',
            ]))
            ->assertSessionHasErrors([
                'tanggal_selesai' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            ]);

        foreach ([
            ['status' => 'Tidak Ada'],
            ['id_ruangan' => 99999],
            ['id_user' => 99999],
            ['tanggal_mulai' => 'tanggal-salah'],
        ] as $filter) {
            $this->from(route('admin.peminjaman.index'))
                ->get(route('admin.peminjaman.index', $filter))
                ->assertSessionHasErrors();
        }
    }

    public function test_pagination_keeps_validated_filter_query_parameters(): void
    {
        $user = User::factory()->peminjam()->create();
        $ruangan = Ruangan::factory()->create();

        for ($number = 1; $number <= 16; $number++) {
            $this->createLoan(
                $user,
                $ruangan,
                StatusPeminjaman::Menunggu,
                '2026-01-10',
                'Peminjaman '.$number,
            );
        }

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.peminjaman.index', [
                'status' => StatusPeminjaman::Menunggu->value,
                'page' => 2,
            ]));

        $response
            ->assertOk()
            ->assertViewHas('peminjaman', fn ($peminjaman): bool => $peminjaman->currentPage() === 2 && $peminjaman->perPage() === 15)
            ->assertSee('status=Menunggu', false);
    }

    public function test_global_summary_includes_every_status_and_zero_counts(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index'))
            ->assertViewHas('ringkasan', fn ($ringkasan): bool => $ringkasan->toArray() === [
                StatusPeminjaman::Menunggu->value => 0,
                StatusPeminjaman::Disetujui->value => 0,
                StatusPeminjaman::Ditolak->value => 0,
                StatusPeminjaman::Selesai->value => 0,
            ]);

        $this->createLoan(status: StatusPeminjaman::Menunggu);
        $this->createLoan(status: StatusPeminjaman::Menunggu);
        $this->createLoan(status: StatusPeminjaman::Disetujui);
        $this->createLoan(status: StatusPeminjaman::Ditolak);
        $this->createLoan(status: StatusPeminjaman::Selesai);

        $this->get(route('admin.peminjaman.index'))
            ->assertViewHas('ringkasan', fn ($ringkasan): bool => $ringkasan->toArray() === [
                StatusPeminjaman::Menunggu->value => 2,
                StatusPeminjaman::Disetujui->value => 1,
                StatusPeminjaman::Ditolak->value => 1,
                StatusPeminjaman::Selesai->value => 1,
            ]);
    }

    public function test_report_does_not_change_loan_data_or_grant_admin_transition_access(): void
    {
        $peminjaman = $this->createLoan(status: StatusPeminjaman::Menunggu);
        $admin = User::factory()->admin()->create();
        $before = Peminjaman::query()->findOrFail($peminjaman->id_peminjaman)->getAttributes();

        $this->actingAs($admin)
            ->get(route('admin.peminjaman.index'))
            ->assertOk();

        $this->get(route('admin.peminjaman.show', $peminjaman))
            ->assertOk();

        $after = Peminjaman::query()->findOrFail($peminjaman->id_peminjaman)->getAttributes();
        $this->assertSame($before, $after);

        $this->actingAs(User::factory()->peminjam()->create())
            ->patch(route('admin.peminjaman.approve', $peminjaman))
            ->assertForbidden();
        $this->patch(route('admin.peminjaman.reject', $peminjaman))->assertForbidden();
        $this->patch(route('petugas.peminjaman.approve', $peminjaman))->assertForbidden();
        $this->patch(route('petugas.peminjaman.reject', $peminjaman))->assertForbidden();
        $this->patch(route('petugas.peminjaman.complete', $peminjaman))->assertForbidden();
    }

    public function test_policy_expansion_does_not_allow_borrower_to_view_another_loan(): void
    {
        $peminjaman = $this->createLoan();

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.peminjaman.show', $peminjaman))
            ->assertForbidden();
    }

    public function test_dashboard_shows_report_link_only_to_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee('Laporan Peminjaman');

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertDontSee('Laporan Peminjaman');
        }
    }

    /**
     * Create a loan fixture with a DATE value that mirrors the MySQL column.
     *
     * @param  array<int, int>  $fasilitas
     */
    private function createLoan(
        ?User $user = null,
        ?Ruangan $ruangan = null,
        StatusPeminjaman $status = StatusPeminjaman::Menunggu,
        string $tanggal = '2026-01-10',
        string $keperluan = 'Peminjaman untuk laporan',
        array $fasilitas = [],
        string $jamMulai = '08:00',
        string $jamSelesai = '09:00',
    ): Peminjaman {
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => ($user ?? User::factory()->peminjam()->create())->id_user,
            'id_ruangan' => ($ruangan ?? Ruangan::factory()->create())->id_ruangan,
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
