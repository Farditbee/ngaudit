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
        Schema::create('sprins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('no_sprin');
            $table->string('no_surat_pemberitahuan');
            $table->string('file_surat_perintah_id')->nullable();
            $table->string('file_surat_perintah_name')->nullable();
            $table->string('file_surat_pemberitahuan_id')->nullable();
            $table->string('file_surat_pemberitahuan_name')->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_akhir');
            $table->foreignId('plotting_id')->nullable()->constrained('plottings')->nullOnDelete();
            $table->char('kode_wilayah', 2)->nullable();
            $table->foreignId('tim_id')->constrained('tims')->cascadeOnDelete();
            $table->foreignId('katim')->constrained('users')->cascadeOnDelete();
            $table->foreignId('katim_2')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dalnis')->constrained('users')->cascadeOnDelete();
            $table->foreignId('daltu')->constrained('users')->cascadeOnDelete();
            $table->string('tahap', 20);
            $table->string('sprin_tipe', 50);
            $table->text('keterangan')->nullable();
            $table->dateTime('approved_by_daltu_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprins');
    }
};
