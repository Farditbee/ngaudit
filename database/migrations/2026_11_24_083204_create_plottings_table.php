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
        Schema::create('plottings', function (Blueprint $table): void {
            $table->id();
            $table->string('plot');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plotting_province', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plotting_id')->constrained('plottings')->cascadeOnDelete();
            $table->unsignedBigInteger('provinsi_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provinsi_id')
                ->references('id')
                ->on('wilayah')
                ->cascadeOnDelete();

            $table->unique(['plotting_id', 'provinsi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plotting_province');
        Schema::dropIfExists('plottings');
    }
};
