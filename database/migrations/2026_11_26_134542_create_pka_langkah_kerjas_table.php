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
        Schema::create('pka_langkah_kerjas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pka_id')->constrained('pkas')->cascadeOnDelete();
            $table->string('bidang');
            $table->longText('uraian');
            $table->foreignId('dilaksanakan_rencana_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dilaksanakan_realisasi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('waktu_rencana')->nullable();
            $table->unsignedInteger('waktu_realisasi')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('pka_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pka_langkah_kerjas');
    }
};
