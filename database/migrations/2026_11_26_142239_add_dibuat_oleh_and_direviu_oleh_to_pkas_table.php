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
        Schema::table('pkas', function (Blueprint $table): void {
            $table->foreignId('dibuat_oleh_id')->nullable()->after('lampiran_name')->constrained('users')->nullOnDelete();
            $table->foreignId('direviu_oleh_id')->nullable()->after('dibuat_oleh_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pkas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('direviu_oleh_id');
            $table->dropConstrainedForeignId('dibuat_oleh_id');
        });
    }
};
