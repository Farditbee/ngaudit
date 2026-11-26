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
        Schema::create('template_kkas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_template');
            $table->string('lingkup'); // Nasional / Wilayah
            $table->foreignId('plotting_id')->nullable()->constrained('plottings')->nullOnDelete();
            $table->string('kode_wilayah', 10)->nullable();
            $table->longText('tujuan')->nullable();
            $table->longText('langkah')->nullable();
            $table->longText('hasil')->nullable();
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
        Schema::dropIfExists('template_kkas');
    }
};
