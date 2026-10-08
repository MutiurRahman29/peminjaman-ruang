<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login_from_admin_user_routes(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.users.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.users.edit', $user))->assertRedirect(route('admin.login'));
    }

    public function test_staff_and_borrower_cannot_access_admin_user_crud(): void
    {
        $target = User::factory()->create();

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('admin.users.index'))
                ->assertForbidden();

            $this->post(route('admin.users.store'), $this->validStorePayload())
                ->assertForbidden();

            $this->delete(route('admin.users.destroy', $target))
                ->assertForbidden();
        }
    }

    public function test_admin_can_view_an_ordered_user_list(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->create(['username' => 'zulu']);
        User::factory()->create(['username' => 'alpha']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeInOrder(['alpha', 'zulu']);
    }

    public function test_admin_can_create_accounts_for_each_role(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        foreach (UserRole::cases() as $role) {
            $username = 'user-'.$role->value;

            $this->post(route('admin.users.store'), $this->validStorePayload([
                'username' => $username,
                'role' => $role->value,
            ]))
                ->assertRedirect(route('admin.users.index'));

            $this->assertDatabaseHas('users', [
                'username' => $username,
                'role' => $role->value,
            ]);
        }
    }

    public function test_username_is_trimmed_lowercased_and_password_is_hashed_when_user_is_created(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.store'), $this->validStorePayload([
                'username' => '  Pengguna.Baru  ',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ]))
            ->assertRedirect();

        $user = User::query()->where('username', 'pengguna.baru')->firstOrFail();

        $this->assertSame('pengguna.baru', $user->username);
        $this->assertTrue(Hash::check('password-baru', $user->password));
        $this->assertNotSame('password-baru', $user->password);
    }

    public function test_user_validation_covers_required_fields_username_password_and_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors([
                'nama' => 'Nama wajib diisi.',
                'username' => 'Username wajib diisi.',
                'password' => 'Password wajib diisi.',
                'role' => 'Role wajib dipilih.',
            ]);

        User::factory()->create(['username' => 'sama']);

        $this->from(route('admin.users.create'))
            ->post(route('admin.users.store'), $this->validStorePayload([
                'nama' => str_repeat('a', 101),
                'username' => ' SAMA ',
                'password' => 'pendek',
                'password_confirmation' => 'berbeda',
                'role' => 'bukan-role',
            ]))
            ->assertSessionHasErrors([
                'nama' => 'Nama maksimal 100 karakter.',
                'username' => 'Username sudah digunakan.',
                'password' => 'Password minimal 8 karakter.',
                'role' => 'Role tidak valid.',
            ]);

        $this->from(route('admin.users.create'))
            ->post(route('admin.users.store'), $this->validStorePayload([
                'username' => str_repeat('u', 51),
                'password_confirmation' => 'berbeda',
            ]))
            ->assertSessionHasErrors([
                'username' => 'Username maksimal 50 karakter.',
                'password' => 'Konfirmasi password tidak cocok.',
            ]);
    }

    public function test_admin_can_update_own_username_without_unique_error_and_keep_existing_password(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'admin.lama']);
        $oldPassword = $admin->password;

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), $this->validUpdatePayload([
                'nama' => 'Admin Diperbarui',
                'username' => ' ADMIN.LAMA ',
                'role' => UserRole::Admin->value,
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'Pengguna berhasil diperbarui.');

        $admin->refresh();
        $this->assertSame('admin.lama', $admin->username);
        $this->assertSame($oldPassword, $admin->password);
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_admin_can_replace_a_users_password_with_a_new_hash(): void
    {
        $target = User::factory()->peminjam()->create();
        $oldPassword = $target->password;

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.users.update', $target), $this->validUpdatePayload([
                'nama' => $target->nama,
                'username' => $target->username,
                'password' => 'password-pengganti',
                'password_confirmation' => 'password-pengganti',
                'role' => UserRole::Peminjam->value,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertNotSame($oldPassword, $target->password);
        $this->assertTrue(Hash::check('password-pengganti', $target->password));
    }

    public function test_admin_cannot_change_the_role_of_the_active_admin_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $admin))
            ->put(route('admin.users.update', $admin), $this->validUpdatePayload([
                'nama' => $admin->nama,
                'username' => $admin->username,
                'role' => UserRole::Petugas->value,
            ]))
            ->assertSessionHasErrors([
                'role' => 'Role akun yang sedang digunakan tidak dapat diubah.',
            ]);

        $this->assertDatabaseHas('users', [
            'id_user' => $admin->id_user,
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_admin_cannot_delete_the_active_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->delete(route('admin.users.destroy', $admin))
            ->assertOk()
            ->assertSee('Akun yang sedang digunakan tidak dapat dihapus.');

        $this->assertDatabaseHas('users', ['id_user' => $admin->id_user]);
    }

    public function test_admin_can_delete_a_user_without_loan_history(): void
    {
        $target = User::factory()->peminjam()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'Pengguna berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id_user' => $target->id_user]);
    }

    public function test_user_with_loan_history_cannot_be_deleted(): void
    {
        $target = User::factory()->peminjam()->create();
        $peminjaman = Peminjaman::factory()->create(['id_user' => $target->id_user]);

        $this->actingAs(User::factory()->admin()->create())
            ->followingRedirects()
            ->delete(route('admin.users.destroy', $target))
            ->assertOk()
            ->assertSee('Pengguna tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');

        $this->assertDatabaseHas('users', ['id_user' => $target->id_user]);
        $this->assertDatabaseHas('peminjaman', ['id_peminjaman' => $peminjaman->id_peminjaman]);
    }

    public function test_dashboard_shows_user_management_link_only_to_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee('Kelola Pengguna');

        foreach ([User::factory()->petugas()->create(), User::factory()->peminjam()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertDontSee('Kelola Pengguna');
        }
    }

    /**
     * Get valid user input for storage.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validStorePayload(array $overrides = []): array
    {
        return [
            'nama' => 'Pengguna Baru',
            'username' => 'pengguna.baru',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'role' => UserRole::Peminjam->value,
            ...$overrides,
        ];
    }

    /**
     * Get valid user input for updates without a password change.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(array $overrides = []): array
    {
        return [
            'nama' => 'Pengguna Diperbarui',
            'username' => 'pengguna.diperbarui',
            'role' => UserRole::Peminjam->value,
            ...$overrides,
        ];
    }
}
