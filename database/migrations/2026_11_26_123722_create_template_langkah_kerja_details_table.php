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
        Schema::create('template_langkah_kerja_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_langkah_kerja_id')
                ->constrained('template_langkah_kerjas')
                ->cascadeOnDelete();
            $table->longText('uraian');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('template_langkah_kerja_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_langkah_kerja_details');
    }
};
