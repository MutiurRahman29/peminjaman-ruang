<?php

namespace Tests\Feature;

use App\Enums\KondisiFasilitas;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFasilitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_admin_facility_routes(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $this->get(route('admin.fasilitas.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.fasilitas.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.fasilitas.edit', $fasilitas))->assertRedirect(route('admin.login'));
    }

    public function test_borrower_and_staff_cannot_access_admin_facility_crud(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        foreach ([User::factory()->peminjam()->create(), User::factory()->petugas()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('admin.fasilitas.index'))
                ->assertForbidden();

            $this->post(route('admin.fasilitas.store'), $this->validPayload())
                ->assertForbidden();

            $this->delete(route('admin.fasilitas.destroy', $fasilitas))
                ->assertForbidden();
        }
    }

    public function test_admin_can_view_an_ordered_facility_list_and_empty_state(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.fasilitas.index'))
            ->assertOk()
            ->assertSee('Belum ada fasilitas.');

        Fasilitas::factory()->create(['nama_fasilitas' => 'Zulu']);
        Fasilitas::factory()->create(['nama_fasilitas' => 'Alpha', 'keterangan' => null]);

        $this->get(route('admin.fasilitas.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha', '-', 'Zulu']);
    }

    public function test_admin_can_create_a_facility_and_blank_description_is_stored_as_null(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.fasilitas.store'), $this->validPayload(['keterangan' => '']))
            ->assertRedirect(route('admin.fasilitas.index'))
            ->assertSessionHas('success', 'Fasilitas berhasil ditambahkan.');

        $this->assertDatabaseHas('fasilitas', [
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 3,
            'kondisi' => KondisiFasilitas::Baik->value,
            'keterangan' => null,
        ]);
    }

    public function test_facility_validation_uses_indonesian_messages_for_required_quantity_and_condition_rules(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.fasilitas.create'))
            ->post(route('admin.fasilitas.store'), [])
            ->assertSessionHasErrors([
                'nama_fasilitas' => 'Nama fasilitas wajib diisi.',
                'jumlah' => 'Jumlah fasilitas wajib diisi.',
                'kondisi' => 'Kondisi fasilitas wajib dipilih.',
            ]);

        foreach ([-1, 'banyak'] as $jumlah) {
            $this->from(route('admin.fasilitas.create'))
                ->post(route('admin.fasilitas.store'), $this->validPayload(['jumlah' => $jumlah]))
                ->assertSessionHasErrors('jumlah');
        }

        $this->from(route('admin.fasilitas.create'))
            ->post(route('admin.fasilitas.store'), $this->validPayload(['kondisi' => 'Tidak Ada']))
            ->assertSessionHasErrors([
                'kondisi' => 'Kondisi fasilitas tidak valid.',
            ]);
    }

    public function test_facility_validation_rejects_overlong_values_and_duplicate_names(): void
    {
        $admin = User::factory()->admin()->create();
        Fasilitas::factory()->create(['nama_fasilitas' => 'Proyektor']);

        $this->actingAs($admin)
            ->from(route('admin.fasilitas.create'))
            ->post(route('admin.fasilitas.store'), $this->validPayload([
                'nama_fasilitas' => str_repeat('a', 101),
                'keterangan' => str_repeat('b', 1001),
            ]))
            ->assertSessionHasErrors([
                'nama_fasilitas' => 'Nama fasilitas maksimal 100 karakter.',
                'keterangan' => 'Keterangan maksimal 1000 karakter.',
            ]);

        $this->from(route('admin.fasilitas.create'))
            ->post(route('admin.fasilitas.store'), $this->validPayload(['nama_fasilitas' => 'Proyektor']))
            ->assertSessionHasErrors([
                'nama_fasilitas' => 'Nama fasilitas sudah digunakan.',
            ]);
    }

    public function test_admin_can_update_a_facility_without_failing_its_own_unique_name(): void
    {
        $fasilitas = Fasilitas::factory()->create(['nama_fasilitas' => 'Laptop']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.fasilitas.update', $fasilitas), $this->validPayload([
                'nama_fasilitas' => 'Laptop',
                'jumlah' => 5,
                'kondisi' => KondisiFasilitas::Rusak->value,
            ]))
            ->assertRedirect(route('admin.fasilitas.index'))
            ->assertSessionHas('success', 'Fasilitas berhasil diperbarui.');

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'nama_fasilitas' => 'Laptop',
            'jumlah' => 5,
            'kondisi' => KondisiFasilitas::Rusak->value,
        ]);
    }

    public function test_admin_cannot_update_a_facility_to_another_facilitys_name(): void
    {
        $pertama = Fasilitas::factory()->create(['nama_fasilitas' => 'Proyektor']);
        $kedua = Fasilitas::factory()->create(['nama_fasilitas' => 'Laptop']);

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.fasilitas.edit', $kedua))
            ->put(route('admin.fasilitas.update', $kedua), $this->validPayload([
                'nama_fasilitas' => $pertama->nama_fasilitas,
            ]))
            ->assertSessionHasErrors([
                'nama_fasilitas' => 'Nama fasilitas sudah digunakan.',
            ]);

        $this->assertDatabaseHas('fasilitas', [
            'id_fasilitas' => $kedua->id_fasilitas,
            'nama_fasilitas' => 'Laptop',
        ]);
    }

    public function test_admin_can_delete_a_facility_without_loan_history(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.fasilitas.destroy', $fasilitas))
            ->assertRedirect(route('admin.fasilitas.index'))
            ->assertSessionHas('success', 'Fasilitas berhasil dihapus.');

        $this->assertDatabaseMissing('fasilitas', ['id_fasilitas' => $fasilitas->id_fasilitas]);
    }

    public function test_facility_with_loan_detail_history_cannot_be_deleted(): void
    {
        $fasilitas = Fasilitas::factory()->create();
        $peminjaman = Peminjaman::factory()->create([
            'id_user' => User::factory()->peminjam()->create()->id_user,
        ]);
        $peminjaman->detailPeminjaman()->create([
            'id_fasilitas' => $fasilitas->id_fasilitas,
            'jumlah' => 1,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->followingRedirects()
            ->delete(route('admin.fasilitas.destroy', $fasilitas))
            ->assertOk()
            ->assertSee('Fasilitas tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');

        $this->assertDatabaseHas('fasilitas', ['id_fasilitas' => $fasilitas->id_fasilitas]);
        $this->assertDatabaseCount('detail_peminjaman', 1);
    }

    public function test_dashboard_shows_facility_management_link_only_to_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee('Kelola Fasilitas');

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertDontSee('Kelola Fasilitas');
        }
    }

    /**
     * Get valid facility input.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'nama_fasilitas' => 'Proyektor',
            'jumlah' => 3,
            'kondisi' => KondisiFasilitas::Baik->value,
            'keterangan' => 'Untuk presentasi',
            ...$overrides,
        ];
    }
}
