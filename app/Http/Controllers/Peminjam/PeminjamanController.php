<?php

namespace App\Http\Controllers\Peminjam;

use App\Enums\KondisiFasilitas;
use App\Enums\StatusPeminjaman;
use App\Enums\StatusRuangan;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peminjam\AccessPeminjamanRequest;
use App\Http\Requests\Peminjam\StorePeminjamanRequest;
use App\Models\Fasilitas;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use App\Notifications\PeminjamanBaru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Display the authenticated user's loan history.
     */
    public function index(Request $request): View
    {
        $peminjaman = $request->user()
            ->peminjaman()
            ->with(['ruangan', 'detailPeminjaman.fasilitas'])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        return view('peminjam.peminjaman.index', compact('peminjaman'));
    }

    /**
     * Display the room loan request form.
     */
    public function create(): View
    {
        $ruangan = Ruangan::query()
            ->where('status', StatusRuangan::Tersedia)
            ->orderBy('nama_ruangan')
            ->get();

        $fasilitas = Fasilitas::query()
            ->where('kondisi', KondisiFasilitas::Baik)
            ->where('jumlah', '>', 0)
            ->orderBy('nama_fasilitas')
            ->get();

        return view('peminjam.peminjaman.create', compact('ruangan', 'fasilitas'));
    }

    /**
     * Store a new room loan request for the authenticated user.
     */
    public function store(StorePeminjamanRequest $request): RedirectResponse
    {
        $peminjaman = DB::transaction(function () use ($request) {
            $peminjaman = Peminjaman::query()->create([
                'id_user' => $request->user()?->id_user,
                ...$request->safe()->only([
                    'id_ruangan',
                    'tanggal',
                    'jam_mulai',
                    'jam_selesai',
                    'keperluan',
                    'nama_pemohon',
                    'email_pemohon',
                    'whatsapp_pemohon',
                ]),
                'akses_password' => Hash::make($request->input('akses_password')),
                'status' => StatusPeminjaman::Menunggu,
            ]);

            $peminjaman->detailPeminjaman()->createMany($request->selectedFasilitas());

            return $peminjaman;
        });

        User::query()
            ->where('role', UserRole::Admin->value)
            ->chunkById(100, function ($admins) use ($peminjaman) {
                foreach ($admins as $admin) {
                    $admin->notify(new PeminjamanBaru($peminjaman, $peminjaman->ruangan));
                }
            });

        return redirect()
            ->route('peminjam.peminjaman.success', $peminjaman)
            ->with('success', 'Pengajuan peminjaman berhasil dikirim.');
    }

    /**
     * Display the public success page after submitting a guest loan request.
     */
    public function success(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['ruangan', 'detailPeminjaman.fasilitas']);

        return view('peminjam.peminjaman.success', compact('peminjaman'));
    }

    /**
     * Display the public loan progress access form.
     */
    public function access(): View
    {
        return view('peminjam.peminjaman.access');
    }

    /**
     * Verify the loan number and access password, then open its progress.
     */
    public function accessStore(AccessPeminjamanRequest $request): RedirectResponse
    {
        $peminjaman = Peminjaman::query()
            ->whereKey($request->input('id_peminjaman'))
            ->whereNotNull('akses_password')
            ->first();

        if ($peminjaman === null || ! Hash::check($request->input('akses_password'), $peminjaman->akses_password)) {
            return redirect()
                ->route('peminjam.peminjaman.access')
                ->withErrors(['akses_password' => 'Nomor pengajuan atau kata sandi tidak benar.']);
        }

        session()->put('accessed_peminjaman_id', $peminjaman->id_peminjaman);

        return redirect()->route('peminjam.peminjaman.progress', $peminjaman);
    }

    /**
     * Display progress for a verified guest access session.
     */
    public function progress(Peminjaman $peminjaman): View
    {
        $accessedId = session('accessed_peminjaman_id');

        if ($accessedId !== $peminjaman->id_peminjaman) {
            abort(403, 'Akses peminjaman tidak ditemukan.');
        }

        if (auth()->check()) {
            Gate::authorize('view', $peminjaman);
        }

        $peminjaman->load(['ruangan', 'detailPeminjaman.fasilitas']);

        return view('peminjam.peminjaman.show', compact('peminjaman'));
    }

    /**
     * Display a loan owned by the authenticated user.
     */
    public function show(Peminjaman $peminjaman): View
    {
        Gate::authorize('view', $peminjaman);

        $peminjaman->load(['ruangan', 'detailPeminjaman.fasilitas']);

        return view('peminjam.peminjaman.show', compact('peminjaman'));
    }
}
