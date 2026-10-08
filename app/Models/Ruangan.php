<?php

namespace App\Models;

use App\Enums\StatusRuangan;
use Database\Factories\RuanganFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Ruangan extends Model
{
    /** @use HasFactory<RuanganFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'ruangan';

    /** @var string */
    protected $primaryKey = 'id_ruangan';

    /** @var list<string> */
    protected $fillable = [
        'nama_ruangan',
        'kapasitas',
        'lokasi',
        'gambar',
        'status',
    ];

    /**
     * Get the public URL for the room image.
     *
     * @return Attribute<string|null, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->gambar
            ? Storage::disk('public')->url($this->gambar)
            : null);
    }

    /**
     * Get the loans for the room.
     *
     * @return HasMany<Peminjaman, $this>
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_ruangan', 'id_ruangan');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusRuangan::class,
        ];
    }
}
