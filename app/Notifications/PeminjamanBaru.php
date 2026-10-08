<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use App\Models\Ruangan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanBaru extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Peminjaman $peminjaman,
        public readonly Ruangan $ruangan,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id_peminjaman' => $this->peminjaman->id_peminjaman,
            'nama_pemohon' => $this->peminjaman->nama_pemohon,
            'nama_ruangan' => $this->ruangan->nama_ruangan,
            'tanggal' => $this->peminjaman->tanggal->toDateString(),
            'url' => route('admin.peminjaman.show', $this->peminjaman),
        ];
    }
}
