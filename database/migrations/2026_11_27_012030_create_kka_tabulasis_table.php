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
        Schema::create('kka_tabulasis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kka_id')->constrained('kkas')->cascadeOnDelete();
            $table->string('jenis_tabulasi', 30)->default('Temuan');
            $table->string('bidang')->nullable();
            $table->string('nomor', 100);
            $table->string('tipe_temuan', 30)->nullable();
            $table->foreignId('kode_temuan_id')->nullable()->constrained('kodes')->nullOnDelete();
            $table->foreignId('sub_kode_temuan_id')->nullable()->constrained('sub_kodes')->nullOnDelete();
            $table->decimal('nilai_temuan', 18, 2)->nullable();
            $table->longText('uraian_temuan')->nullable();
            $table->foreignId('kriteria_id')->nullable()->constrained('kriterias')->nullOnDelete();
            $table->longText('uraian_kriteria')->nullable();
            $table->foreignId('kode_sebab_id')->nullable()->constrained('kodes')->nullOnDelete();
            $table->foreignId('sub_kode_sebab_id')->nullable()->constrained('sub_kodes')->nullOnDelete();
            $table->longText('uraian_sebab')->nullable();
            $table->foreignId('kode_akibat_id')->nullable()->constrained('kodes')->nullOnDelete();
            $table->foreignId('sub_kode_akibat_id')->nullable()->constrained('sub_kodes')->nullOnDelete();
            $table->longText('uraian_akibat')->nullable();
            $table->foreignId('kode_rekomendasi_id')->nullable()->constrained('kodes')->nullOnDelete();
            $table->foreignId('sub_kode_rekomendasi_id')->nullable()->constrained('sub_kodes')->nullOnDelete();
            $table->longText('uraian_rekomendasi')->nullable();
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kka_id', 'jenis_tabulasi', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kka_tabulasis');
    }
};
