<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('nama_pemohon', 100)->after('id_user');
            $table->string('email_pemohon', 255)->after('nama_pemohon');
            $table->string('whatsapp_pemohon', 20)->after('email_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_pemohon', 'email_pemohon', 'nama_pemohon']);
        });
    }
};
