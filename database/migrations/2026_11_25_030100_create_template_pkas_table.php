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
        Schema::create('template_pkas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_template');
            $table->string('lingkup'); // Nasional / Wilayah
            $table->foreignId('plotting_id')->nullable()->constrained('plottings')->nullOnDelete();
            $table->string('kode_wilayah', 10)->nullable();
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

            $table->index(['lingkup', 'plotting_id']);
            $table->index(['lingkup', 'kode_wilayah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_pkas');
    }
};
