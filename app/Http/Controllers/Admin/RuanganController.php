<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusRuangan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuanganRequest;
use App\Models\Ruangan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RuanganController extends Controller
{
    /**
     * Display the room list.
     */
    public function index(): View
    {
        $ruangan = Ruangan::query()
            ->orderBy('nama_ruangan')
            ->get();

        return view('admin.ruangan.index', compact('ruangan'));
    }

    /**
     * Display the room creation form.
     */
    public function create(): View
    {
        return view('admin.ruangan.create', ['statusOptions' => StatusRuangan::cases()]);
    }

    /**
     * Store a newly created room.
     */
    public function store(RuanganRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->storePublicly('ruangan', 'public');
        }

        Ruangan::query()->create($validated);

        return redirect()
            ->route('admin.ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Display the room editing form.
     */
    public function edit(Ruangan $ruangan): View
    {
        return view('admin.ruangan.edit', [
            'ruangan' => $ruangan,
            'statusOptions' => StatusRuangan::cases(),
        ]);
    }

    /**
     * Update the specified room.
     */
    public function update(RuanganRequest $request, Ruangan $ruangan): RedirectResponse
    {
        $validated = $request->validated();
        $gambarLama = $ruangan->gambar;

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->storePublicly('ruangan', 'public');
        }

        $ruangan->update($validated);

        if ($request->hasFile('gambar') && $gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.ruangan.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    /**
     * Remove a room that has no loan history.
     */
    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        if ($ruangan->peminjaman()->exists()) {
            return redirect()
                ->route('admin.ruangan.index')
                ->with('error', 'Ruangan tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');
        }

        try {
            $ruangan->delete();

            if ($ruangan->gambar) {
                Storage::disk('public')->delete($ruangan->gambar);
            }
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }

            return redirect()
                ->route('admin.ruangan.index')
                ->with('error', 'Ruangan tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');
        }

        return redirect()
            ->route('admin.ruangan.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
