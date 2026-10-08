<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KondisiFasilitas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FasilitasRequest;
use App\Models\Fasilitas;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    /**
     * Display the facility list.
     */
    public function index(): View
    {
        $fasilitas = Fasilitas::query()
            ->orderBy('nama_fasilitas')
            ->get();

        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    /**
     * Display the facility creation form.
     */
    public function create(): View
    {
        return view('admin.fasilitas.create', ['kondisiOptions' => KondisiFasilitas::cases()]);
    }

    /**
     * Store a newly created facility.
     */
    public function store(FasilitasRequest $request): RedirectResponse
    {
        Fasilitas::query()->create($this->normalizedData($request));

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    /**
     * Display the facility editing form.
     */
    public function edit(Fasilitas $fasilitas): View
    {
        return view('admin.fasilitas.edit', [
            'fasilitas' => $fasilitas,
            'kondisiOptions' => KondisiFasilitas::cases(),
        ]);
    }

    /**
     * Update the specified facility.
     */
    public function update(FasilitasRequest $request, Fasilitas $fasilitas): RedirectResponse
    {
        $gambarLama = $fasilitas->gambar;
        $fasilitas->update($this->normalizedData($request));

        if ($request->hasFile('gambar') && $gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    /**
     * Remove a facility that has no loan detail history.
     */
    public function destroy(Fasilitas $fasilitas): RedirectResponse
    {
        if ($fasilitas->detailPeminjaman()->exists()) {
            return $this->historyErrorRedirect();
        }

        try {
            $fasilitas->delete();

            if ($fasilitas->gambar) {
                Storage::disk('public')->delete($fasilitas->gambar);
            }
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }

            return $this->historyErrorRedirect();
        }

        return redirect()
            ->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedData(FasilitasRequest $request): array
    {
        $data = $request->validated();
        $data['keterangan'] = filled($data['keterangan'] ?? null) ? $data['keterangan'] : null;

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->storePublicly('fasilitas', 'public');
        }

        return $data;
    }

    private function historyErrorRedirect(): RedirectResponse
    {
        return redirect()
            ->route('admin.fasilitas.index')
            ->with('error', 'Fasilitas tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');
    }
}
