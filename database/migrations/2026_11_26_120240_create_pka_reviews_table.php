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
        Schema::create('pka_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pka_id')->constrained('pkas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_type', 20)->default('daltu'); // dalnis / daltu
            $table->string('status', 20); // Disetujui / Koreksi
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pka_id', 'role_type']);
        });

        Schema::table('pkas', function (Blueprint $table): void {
            $table->timestamp('approved_by_dalnis_date')->nullable()->after('status');
            $table->timestamp('approved_by_daltu_date')->nullable()->after('approved_by_dalnis_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pkas', function (Blueprint $table): void {
            $table->dropColumn(['approved_by_dalnis_date', 'approved_by_daltu_date']);
        });

        Schema::dropIfExists('pka_reviews');
    }
};
