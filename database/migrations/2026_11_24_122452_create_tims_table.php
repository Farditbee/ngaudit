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
        Schema::create('tims', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_tim');
            $table->string('lingkup', 50);
            $table->foreignId('plotting_id')->nullable()->constrained('plottings')->nullOnDelete();
            $table->char('province_code', 2)->nullable();
            $table->foreignId('daltu_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dalnis_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ketua_tim_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ketua_tim_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('jumlah_anggota')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tim_auditor', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tim_id')->constrained('tims')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tim_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_auditor');
        Schema::dropIfExists('tims');
    }
};
