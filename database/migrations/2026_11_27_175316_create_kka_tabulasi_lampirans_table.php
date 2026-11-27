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
        Schema::create('kka_tabulasi_lampirans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kka_tabulasi_id')->constrained('kka_tabulasis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis_unggahan', 20)->default('file');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link_url', 1000)->nullable();
            $table->string('keterangan', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kka_tabulasi_lampirans');
    }
};
