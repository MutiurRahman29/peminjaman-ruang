<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('notifications', function ($table) {
                $table->unsignedBigInteger('notifiable_id')->change();
            });

            return;
        }

        Schema::create('notifications_repaired', function ($table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        DB::table('notifications')->orderBy('id')->chunkById(100, function ($rows) {
            DB::table('notifications_repaired')->insertUsing([
                'id', 'type', 'notifiable_id', 'notifiable_type', 'data', 'read_at', 'created_at', 'updated_at',
            ], function ($query) {
                $query->select(
                    'id', 'type', DB::raw('CAST(notifiable_id AS INTEGER)'), 'notifiable_type',
                    'data', 'read_at', 'created_at', 'updated_at',
                )->from('notifications');
            });
        });

        Schema::dropIfExists('notifications');
        Schema::rename('notifications_repaired', 'notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
