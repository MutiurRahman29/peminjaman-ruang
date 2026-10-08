<?php

namespace App\Models;

use App\Enums\StatusPeminjaman;
use Database\Factories\PeminjamanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    /** @use HasFactory<PeminjamanFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'peminjaman';

    /** @var string */
    protected $primaryKey = 'id_peminjaman';

    /** @var list<string> */
    protected $fillable = [
        'id_user',
        'id_ruangan',
        'nama_pemohon',
        'email_pemohon',
        'whatsapp_pemohon',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'keperluan',
        'status',
        'akses_password',
    ];

    /**
     * Get the user who made the loan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Get the room being borrowed.
     *
     * @return BelongsTo<Ruangan, $this>
     */
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruangan');
    }

    /**
     * Get the facility details for the loan.
     *
     * @return HasMany<DetailPeminjaman, $this>
     */
    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => StatusPeminjaman::class,
        ];
    }
}
