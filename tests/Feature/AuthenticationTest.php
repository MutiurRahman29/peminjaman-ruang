<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_the_admin_login_page(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee('Username');
    }

    public function test_guest_is_redirected_to_admin_login_from_admin_root(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_open_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->peminjam()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_log_in_with_a_valid_username_and_password(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'pengguna-valid',
        ]);

        $this->post(route('admin.login.store'), [
            'username' => $user->username,
            'password' => 'password',
            'remember' => true,
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_ignores_a_previously_intended_borrower_url(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'admin-login',
        ]);

        $this->get(route('peminjam.peminjaman.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.login.store'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_normalizes_whitespace_and_letter_case_in_username(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'pengguna-valid',
        ]);

        $this->post(route('admin.login.store'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_an_invalid_password_without_authenticating_the_user(): void
    {
        $user = User::factory()->create();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'password-salah',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_return_to_the_admin_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_guest_can_open_the_public_user_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Selamat datang di ruang')
            ->assertSee('untuk ide yang mulai berkembang.')
            ->assertSee('Jelajahi katalog')
            ->assertSee('Fitur utama');
    }

    public function test_guest_is_redirected_to_admin_login_when_opening_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_open_the_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create([
            'nama' => 'Admin Dashboard',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('Kelola seluruh data sistem');
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $user = User::factory()->create([
            'nama' => 'Pengguna Dashboard',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengguna Dashboard');
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create([
            'username' => 'dibatasi',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('admin.login'))
                ->post(route('admin.login.store'), [
                    'username' => $user->username,
                    'password' => 'password-salah',
                ])
                ->assertSessionHasErrors('username');
        }

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'password-salah',
            ])
            ->assertSessionHasErrors([
                'username' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam satu menit.',
            ]);

        $this->assertGuest();
    }

    public function test_role_middleware_allows_a_matching_role(): void
    {
        Route::middleware(['auth', 'role:admin'])->get('/role-check/admin', fn () => 'allowed');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/role-check/admin')
            ->assertOk()
            ->assertSee('allowed');
    }

    public function test_role_middleware_returns_forbidden_for_a_non_matching_role(): void
    {
        Route::middleware(['auth', 'role:admin'])->get('/role-check/forbidden', fn () => 'allowed');

        $this->actingAs(User::factory()->peminjam()->create())
            ->get('/role-check/forbidden')
            ->assertForbidden();
    }

    public function test_role_middleware_allows_any_role_in_a_combination(): void
    {
        Route::middleware(['auth', 'role:admin,petugas'])->get('/role-check/staff', fn () => 'allowed');

        $this->actingAs(User::factory()->petugas()->create())
            ->get('/role-check/staff')
            ->assertOk()
            ->assertSee('allowed');
    }
}
