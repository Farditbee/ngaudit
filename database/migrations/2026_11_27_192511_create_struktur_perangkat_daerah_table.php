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
        Schema::create('struktur_perangkat_daerah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('nama_unit');
            $table->unsignedTinyInteger('level')->default(0)->index();
            $table->string('jenis_unit', 100)->index();
            $table->string('pimpinan')->nullable();
            $table->string('kode_unit', 50)->nullable()->index();
            $table->string('kode_wilayah', 50)->nullable()->index();
            $table->string('provinsi')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('struktur_perangkat_daerah')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('struktur_perangkat_daerah');
    }
};
