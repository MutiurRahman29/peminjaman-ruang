<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPeminjaman;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterPeminjamanRequest;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Models\User;
use App\Services\LoanApprovalService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Display the read-only loan report.
     */
    public function index(FilterPeminjamanRequest $request): View
    {
        $filters = $request->filters();

        $peminjaman = Peminjaman::query()
            ->with(['user', 'ruangan', 'detailPeminjaman.fasilitas'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['id_ruangan'] ?? null, fn ($query, $idRuangan) => $query->where('id_ruangan', $idRuangan))
            ->when($filters['id_user'] ?? null, fn ($query, $idUser) => $query->where('id_user', $idUser))
            ->when($filters['tanggal_mulai'] ?? null, fn ($query, $tanggalMulai) => $query->where('tanggal', '>=', $tanggalMulai))
            ->when($filters['tanggal_selesai'] ?? null, fn ($query, $tanggalSelesai) => $query->where('tanggal', '<=', $tanggalSelesai))
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $totalPerStatus = Peminjaman::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ringkasan = collect(StatusPeminjaman::cases())
            ->mapWithKeys(fn (StatusPeminjaman $status): array => [
                $status->value => (int) ($totalPerStatus[$status->value] ?? 0),
            ]);

        $ruangan = Ruangan::query()->orderBy('nama_ruangan')->get();
        $users = User::query()->orderBy('username')->get();

        return view('admin.peminjaman.index', compact('peminjaman', 'filters', 'ringkasan', 'ruangan', 'users'));
    }

    /**
     * Display a read-only loan detail for administrators.
     */
    public function show(Peminjaman $peminjaman): View
    {
        Gate::authorize('view', $peminjaman);

        $peminjaman->load(['user', 'ruangan', 'detailPeminjaman.fasilitas']);

        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    /**
     * Approve a pending loan from the admin report.
     */
    public function approve(Peminjaman $peminjaman, LoanApprovalService $approval): RedirectResponse
    {
        Gate::authorize('approve', $peminjaman);

        try {
            $approval->approve($peminjaman);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.peminjaman.show', $peminjaman->id_peminjaman)
            ->with('success', 'Pengajuan peminjaman disetujui.');
    }

    /**
     * Reject a pending loan from the admin report.
     */
    public function reject(Peminjaman $peminjaman, LoanApprovalService $approval): RedirectResponse
    {
        Gate::authorize('reject', $peminjaman);

        try {
            $approval->reject($peminjaman);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.peminjaman.show', $peminjaman->id_peminjaman)
            ->with('success', 'Pengajuan peminjaman ditolak.');
    }
}
