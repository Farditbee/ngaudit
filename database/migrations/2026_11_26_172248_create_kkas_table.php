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
        Schema::create('kkas', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_kka');
            $table->foreignId('pka_id')->constrained('pkas')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('direviu_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_dibuat')->nullable();
            $table->date('tanggal_direviu')->nullable();
            $table->date('tanggal_kka');
            $table->string('tempat');
            $table->string('auditee_level', 20); // Nasional / Wilayah
            $table->foreignId('auditee_plotting_id')->nullable()->constrained('plottings')->nullOnDelete();
            $table->string('auditee_kode_wilayah', 10)->nullable();
            $table->foreignId('template_kka_id')->nullable()->constrained('template_kkas')->nullOnDelete();
            $table->longText('tujuan')->nullable();
            $table->longText('langkah')->nullable();
            $table->longText('hasil')->nullable();
            $table->string('status', 20)->default('Draft');
            $table->timestamp('approved_by_katim_date')->nullable();
            $table->timestamp('approved_by_dalnis_date')->nullable();
            $table->timestamp('approved_by_daltu_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal_kka');
            $table->index('status');
            $table->index(['auditee_level', 'auditee_plotting_id']);
            $table->index(['auditee_level', 'auditee_kode_wilayah']);
        });

        Schema::create('kka_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kka_id')->constrained('kkas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_type', 20); // katim / dalnis / daltu
            $table->string('status', 20); // Disetujui / Koreksi
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kka_id', 'role_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kka_reviews');
        Schema::dropIfExists('kkas');
    }
};
