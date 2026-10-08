<?php

use App\Http\Controllers\Admin\FasilitasController as AdminFasilitasController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Admin\RuanganController as AdminRuanganController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Peminjam\FasilitasController;
use App\Http\Controllers\Peminjam\PeminjamanController;
use App\Http\Controllers\Peminjam\RuanganController;
use App\Http\Controllers\Petugas\PeminjamanController as PetugasPeminjamanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['admin.auth', 'role:admin'])->get('/admin', function () {
    return view('dashboard');
})->name('admin.dashboard');

Route::middleware('guest')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::prefix('peminjam')
    ->name('peminjam.')
    ->group(function (): void {
        Route::get('/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
        Route::get('/fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');

        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index')->middleware(['auth', 'role:peminjam']);
        Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::get('/peminjaman/access', [PeminjamanController::class, 'access'])->name('peminjaman.access');
        Route::post('/peminjaman/access', [PeminjamanController::class, 'accessStore'])->name('peminjaman.access.store');
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show')->middleware(['auth', 'role:peminjam']);
        Route::get('/peminjaman/{peminjaman}/berhasil', [PeminjamanController::class, 'success'])->name('peminjaman.success');
        Route::get('/peminjaman/{peminjaman}/progress', [PeminjamanController::class, 'progress'])->name('peminjaman.progress');
    });

Route::middleware(['admin.auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function (): void {
        Route::get('/peminjaman', [PetugasPeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/riwayat', [PetugasPeminjamanController::class, 'history'])->name('peminjaman.history');
        Route::get('/peminjaman/{peminjaman}', [PetugasPeminjamanController::class, 'show'])->name('peminjaman.show');
        Route::patch('/peminjaman/{peminjaman}/approve', [PetugasPeminjamanController::class, 'approve'])->name('peminjaman.approve');
        Route::patch('/peminjaman/{peminjaman}/reject', [PetugasPeminjamanController::class, 'reject'])->name('peminjaman.reject');
        Route::patch('/peminjaman/{peminjaman}/complete', [PetugasPeminjamanController::class, 'complete'])->name('peminjaman.complete');
    });

Route::middleware(['admin.auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/peminjaman', [AdminPeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/{peminjaman}', [AdminPeminjamanController::class, 'show'])->name('peminjaman.show');
        Route::patch('/peminjaman/{peminjaman}/approve', [AdminPeminjamanController::class, 'approve'])->name('peminjaman.approve');
        Route::patch('/peminjaman/{peminjaman}/reject', [AdminPeminjamanController::class, 'reject'])->name('peminjaman.reject');
        Route::resource('ruangan', AdminRuanganController::class)->except('show');
        Route::resource('fasilitas', AdminFasilitasController::class)
            ->parameters(['fasilitas' => 'fasilitas'])
            ->except('show');
        Route::resource('users', AdminUserController::class)->except('show');
    });
