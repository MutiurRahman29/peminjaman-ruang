<?php

namespace Tests\Feature;

use App\Enums\StatusRuangan;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRuanganTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_admin_room_routes(): void
    {
        $ruangan = Ruangan::factory()->create();

        $this->get(route('admin.ruangan.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.ruangan.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.ruangan.edit', $ruangan))->assertRedirect(route('admin.login'));
    }

    public function test_borrower_and_staff_cannot_access_admin_room_crud(): void
    {
        $ruangan = Ruangan::factory()->create();

        foreach ([User::factory()->peminjam()->create(), User::factory()->petugas()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('admin.ruangan.index'))
                ->assertForbidden();

            $this->post(route('admin.ruangan.store'), $this->validPayload())
                ->assertForbidden();

            $this->delete(route('admin.ruangan.destroy', $ruangan))
                ->assertForbidden();
        }
    }

    public function test_admin_can_view_an_ordered_room_list_and_empty_state(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.ruangan.index'))
            ->assertOk()
            ->assertSee('Belum ada ruangan.');

        Ruangan::factory()->create(['nama_ruangan' => 'Zulu']);
        Ruangan::factory()->create(['nama_ruangan' => 'Alpha']);

        $this->get(route('admin.ruangan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha', 'Zulu']);
    }

    public function test_admin_room_list_displays_room_thumbnail(): void
    {
        Storage::fake('public');
        $imagePath = 'ruangan/laboratorium.jpg';
        Storage::disk('public')->put($imagePath, 'image contents');
        $ruangan = Ruangan::factory()->create([
            'nama_ruangan' => 'Laboratorium Komputer',
            'gambar' => $imagePath,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.ruangan.index'))
            ->assertOk()
            ->assertSee($ruangan->gambar_url, false)
            ->assertSee('alt="Foto Laboratorium Komputer"', false);
    }

    public function test_admin_can_create_a_room(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ruangan.store'), $this->validPayload())
            ->assertRedirect(route('admin.ruangan.index'))
            ->assertSessionHas('success', 'Ruangan berhasil ditambahkan.');

        $this->assertDatabaseHas('ruangan', $this->validPayload());
    }

    public function test_admin_can_upload_a_room_thumbnail(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ruangan.store'), [
                ...$this->validPayload(),
                'gambar' => UploadedFile::fake()->image('ruang.jpg'),
            ])
            ->assertRedirect(route('admin.ruangan.index'));

        $ruangan = Ruangan::query()->where('nama_ruangan', 'Laboratorium Komputer')->firstOrFail();

        $this->assertNotEmpty($ruangan->gambar);
        Storage::disk('public')->assertExists($ruangan->gambar);
    }

    public function test_room_thumbnail_larger_than_four_megabytes_is_accepted(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ruangan.store'), [
                ...$this->validPayload(),
                'gambar' => UploadedFile::fake()->image('ruang.jpg')->size(5 * 1024),
            ])
            ->assertRedirect(route('admin.ruangan.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_room_thumbnail_over_ten_megabytes_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), [
                ...$this->validPayload(),
                'gambar' => UploadedFile::fake()->image('ruang.jpg')->size(11 * 1024),
            ])
            ->assertSessionHasErrors([
                'gambar' => 'Ukuran gambar maksimal 10 MB.',
            ]);
    }

    public function test_admin_can_replace_a_room_thumbnail(): void
    {
        Storage::fake('public');
        $gambarLama = UploadedFile::fake()->image('lama.jpg')->storePublicly('ruangan', 'public');
        $ruangan = Ruangan::factory()->create(['gambar' => $gambarLama]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.ruangan.update', $ruangan), [
                ...$this->validPayload(['nama_ruangan' => $ruangan->nama_ruangan]),
                'gambar' => UploadedFile::fake()->image('baru.webp'),
            ])
            ->assertRedirect(route('admin.ruangan.index'));

        $ruangan->refresh();

        $this->assertNotSame($gambarLama, $ruangan->gambar);
        Storage::disk('public')->assertMissing($gambarLama);
        Storage::disk('public')->assertExists($ruangan->gambar);
    }

    public function test_updating_a_room_without_a_new_thumbnail_keeps_the_existing_image(): void
    {
        Storage::fake('public');
        $gambar = UploadedFile::fake()->image('ruang.jpg')->storePublicly('ruangan', 'public');
        $ruangan = Ruangan::factory()->create(['gambar' => $gambar]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.ruangan.update', $ruangan), $this->validPayload([
                'nama_ruangan' => $ruangan->nama_ruangan,
            ]))
            ->assertRedirect(route('admin.ruangan.index'));

        $this->assertSame($gambar, $ruangan->fresh()->gambar);
        Storage::disk('public')->assertExists($gambar);
    }

    public function test_room_thumbnail_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), [
                ...$this->validPayload(),
                'gambar' => UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHasErrors([
                'gambar' => 'File gambar harus berupa gambar yang valid.',
            ]);
    }

    public function test_room_validation_uses_indonesian_messages_for_required_capacity_and_status_rules(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), [])
            ->assertSessionHasErrors([
                'nama_ruangan' => 'Nama ruangan wajib diisi.',
                'kapasitas' => 'Kapasitas wajib diisi.',
                'lokasi' => 'Lokasi wajib diisi.',
                'status' => 'Status ruangan wajib dipilih.',
            ]);

        foreach ([0, 'satu'] as $kapasitas) {
            $this->from(route('admin.ruangan.create'))
                ->post(route('admin.ruangan.store'), $this->validPayload(['kapasitas' => $kapasitas]))
                ->assertSessionHasErrors('kapasitas');
        }

        $this->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), $this->validPayload(['status' => 'Tidak Ada']))
            ->assertSessionHasErrors([
                'status' => 'Status ruangan tidak valid.',
            ]);
    }

    public function test_room_validation_rejects_overlong_and_duplicate_names(): void
    {
        $admin = User::factory()->admin()->create();
        Ruangan::factory()->create(['nama_ruangan' => 'Ruang Sama']);

        $this->actingAs($admin)
            ->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), $this->validPayload([
                'nama_ruangan' => str_repeat('a', 101),
                'lokasi' => str_repeat('b', 151),
            ]))
            ->assertSessionHasErrors([
                'nama_ruangan' => 'Nama ruangan maksimal 100 karakter.',
                'lokasi' => 'Lokasi maksimal 150 karakter.',
            ]);

        $this->from(route('admin.ruangan.create'))
            ->post(route('admin.ruangan.store'), $this->validPayload(['nama_ruangan' => 'Ruang Sama']))
            ->assertSessionHasErrors([
                'nama_ruangan' => 'Nama ruangan sudah digunakan.',
            ]);
    }

    public function test_admin_can_update_a_room_without_failing_its_own_unique_name(): void
    {
        $ruangan = Ruangan::factory()->create(['nama_ruangan' => 'Ruang Lama']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.ruangan.update', $ruangan), $this->validPayload([
                'nama_ruangan' => 'Ruang Lama',
                'kapasitas' => 50,
            ]))
            ->assertRedirect(route('admin.ruangan.index'))
            ->assertSessionHas('success', 'Ruangan berhasil diperbarui.');

        $this->assertDatabaseHas('ruangan', [
            'id_ruangan' => $ruangan->id_ruangan,
            'nama_ruangan' => 'Ruang Lama',
            'kapasitas' => 50,
        ]);
    }

    public function test_admin_cannot_update_a_room_to_another_rooms_name(): void
    {
        $pertama = Ruangan::factory()->create(['nama_ruangan' => 'Ruang Pertama']);
        $kedua = Ruangan::factory()->create(['nama_ruangan' => 'Ruang Kedua']);

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.ruangan.edit', $kedua))
            ->put(route('admin.ruangan.update', $kedua), $this->validPayload([
                'nama_ruangan' => $pertama->nama_ruangan,
            ]))
            ->assertSessionHasErrors([
                'nama_ruangan' => 'Nama ruangan sudah digunakan.',
            ]);

        $this->assertDatabaseHas('ruangan', [
            'id_ruangan' => $kedua->id_ruangan,
            'nama_ruangan' => 'Ruang Kedua',
        ]);
    }

    public function test_admin_can_delete_a_room_without_loan_history(): void
    {
        $ruangan = Ruangan::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.ruangan.destroy', $ruangan))
            ->assertRedirect(route('admin.ruangan.index'))
            ->assertSessionHas('success', 'Ruangan berhasil dihapus.');

        $this->assertDatabaseMissing('ruangan', ['id_ruangan' => $ruangan->id_ruangan]);
    }

    public function test_deleting_a_room_also_deletes_its_thumbnail(): void
    {
        Storage::fake('public');
        $gambar = UploadedFile::fake()->image('ruang.jpg')->storePublicly('ruangan', 'public');
        $ruangan = Ruangan::factory()->create(['gambar' => $gambar]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.ruangan.destroy', $ruangan))
            ->assertRedirect(route('admin.ruangan.index'));

        Storage::disk('public')->assertMissing($gambar);
    }

    public function test_room_with_loan_history_cannot_be_deleted(): void
    {
        $ruangan = Ruangan::factory()->create();
        Peminjaman::factory()->create([
            'id_user' => User::factory()->peminjam()->create()->id_user,
            'id_ruangan' => $ruangan->id_ruangan,
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->followingRedirects()
            ->delete(route('admin.ruangan.destroy', $ruangan))
            ->assertOk()
            ->assertSee('Ruangan tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');

        $this->assertDatabaseHas('ruangan', ['id_ruangan' => $ruangan->id_ruangan]);
        $this->assertDatabaseCount('peminjaman', 1);
    }

    public function test_dashboard_shows_room_management_link_only_to_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee('Kelola Ruangan');

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertDontSee('Kelola Ruangan');
        }
    }

    /**
     * Get valid room input.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'nama_ruangan' => 'Laboratorium Komputer',
            'kapasitas' => 30,
            'lokasi' => 'Gedung A',
            'status' => StatusRuangan::Tersedia->value,
            ...$overrides,
        ];
    }
}
