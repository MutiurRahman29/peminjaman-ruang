<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_both_catalogs(): void
    {
        $this->get(route('peminjam.ruangan.index'))->assertOk();
        $this->get(route('peminjam.fasilitas.index'))->assertOk();
    }

    public function test_borrower_can_open_both_catalogs(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.ruangan.index'))
            ->assertOk();

        $this->get(route('peminjam.fasilitas.index'))
            ->assertOk();
    }

    public function test_admin_can_open_both_catalogs(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('peminjam.ruangan.index'))
            ->assertOk();

        $this->get(route('peminjam.fasilitas.index'))
            ->assertOk();
    }

    public function test_staff_can_open_both_catalogs(): void
    {
        $this->actingAs(User::factory()->petugas()->create())
            ->get(route('peminjam.ruangan.index'))
            ->assertOk();

        $this->get(route('peminjam.fasilitas.index'))
            ->assertOk();
    }

    public function test_room_catalog_displays_rooms_in_name_order(): void
    {
        Ruangan::factory()->create(['nama_ruangan' => 'Zulu']);
        Ruangan::factory()->create(['nama_ruangan' => 'Alpha']);

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.ruangan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha', 'Zulu']);
    }

    public function test_facility_catalog_displays_nullable_description_and_name_order(): void
    {
        Fasilitas::factory()->create([
            'nama_fasilitas' => 'Zulu',
            'keterangan' => 'Fasilitas terakhir',
        ]);
        Fasilitas::factory()->create([
            'nama_fasilitas' => 'Alpha',
            'keterangan' => null,
        ]);

        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.fasilitas.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha', '-', 'Zulu']);
    }

    public function test_catalogs_display_empty_states_when_the_tables_are_empty(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('peminjam.ruangan.index'))
            ->assertOk()
            ->assertSee('Tidak ada ruangan tersedia.');

        $this->get(route('peminjam.fasilitas.index'))
            ->assertOk()
            ->assertSee('Tidak ada fasilitas tersedia.');
    }

    public function test_borrower_dashboard_displays_catalog_links(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('peminjam.ruangan.index'))
            ->assertSee(route('peminjam.fasilitas.index'));
    }

    public function test_admin_and_staff_dashboards_do_not_display_catalog_links(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->petugas()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee(route('peminjam.ruangan.index'))
                ->assertDontSee(route('peminjam.fasilitas.index'));
        }
    }
}
