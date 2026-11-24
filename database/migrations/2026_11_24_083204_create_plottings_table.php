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
        $prefix = config('laravolt.indonesia.table_prefix', 'indonesia_');

        Schema::create('plottings', function (Blueprint $table): void {
            $table->id();
            $table->string('plot');
            $table->timestamps();
        });

        Schema::create('plotting_province', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('plotting_id')->constrained('plottings')->cascadeOnDelete();
            $table->unsignedBigInteger('provinsi_id');
            $table->timestamps();

            $table->foreign('provinsi_id')
                ->references('id')
                ->on($prefix.'provinces')
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
