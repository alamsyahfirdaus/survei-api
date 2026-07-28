<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     */
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {

            // ==================================================
            // PRIMARY KEY
            // ==================================================
            $table->id();
            // ID unik untuk setiap sesi konseling.

            // ==================================================
            // RELASI LANSIA YANG DIDAMPINGI
            // ==================================================
            $table->foreignId('elderly_counselee_id')
                ->constrained('elderly_counselee')
                ->cascadeOnDelete();

            // ==================================================
            // RELASI KONSELOR
            // ==================================================
            $table->foreignId('counselor_id')
                ->comment('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ==================================================
            // JENIS LAYANAN
            // ==================================================
            $table->enum('service_mode', ['chat', 'video'])
                ->default('chat');

            // ==================================================
            // STATUS SESI
            // ==================================================
            $table->enum('status', [
                'ongoing',
                'completed',
            ])->default('ongoing');
            
            // ==================================================
            // CATATAN KONSELOR / TINDAK LANJUT
            // ==================================================
            $table->text('note')->nullable();

            // ==================================================
            // RESUME KONSELING
            // ==================================================
            $table->json('resume')->nullable();

            // ==================================================
            // PENANDA SESI TERAKHIR
            // ==================================================
            $table->boolean('is_latest')->default(false);

            // ==================================================
            // TIMESTAMP 
            // ==================================================
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};