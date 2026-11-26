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
        Schema::create('pkas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sprin_id')->constrained('sprins')->cascadeOnDelete();
            $table->string('nomor_pka');
            $table->date('tanggal');
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->string('bidang');
            $table->string('jenis_audit');
            $table->string('sasaran_auditi');
            $table->string('lampiran_path')->nullable();
            $table->string('lampiran_name')->nullable();
            $table->foreignId('template_pka_id')->nullable()->constrained('template_pkas')->nullOnDelete();
            $table->longText('pendahuluan')->nullable();
            $table->longText('tujuan')->nullable();
            $table->longText('ruang_lingkup')->nullable();
            $table->longText('sasaran')->nullable();
            $table->longText('waktu_pelaksanaan')->nullable();
            $table->longText('periode')->nullable();
            $table->longText('penutup')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sprin_id');
            $table->index('bidang');
            $table->index('jenis_audit');
            $table->index('tanggal');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkas');
    }
};
