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
        Schema::create('kodes', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis', 20)->index();
            $table->foreignId('kode_temuan_id')->nullable()->constrained('kodes')->cascadeOnDelete();
            $table->string('kode', 20);
            $table->string('nama');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis', 'kode_temuan_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kodes');
    }
};
