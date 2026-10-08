<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    /**
     * Determine whether the user can view the loan.
     */
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Petugas], true)
            || ($user->role === UserRole::Peminjam && $user->id_user === $peminjaman->id_user);
    }

    /**
     * Determine whether the user can approve the loan.
     */
    public function approve(User $user, Peminjaman $peminjaman): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Petugas], true);
    }

    /**
     * Determine whether the user can reject the loan.
     */
    public function reject(User $user, Peminjaman $peminjaman): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Petugas], true);
    }

    /**
     * Determine whether the user can complete the loan.
     */
    public function complete(User $user, Peminjaman $peminjaman): bool
    {
        return $user->role === UserRole::Petugas;
    }
}
