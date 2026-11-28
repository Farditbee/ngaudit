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
        Schema::table('kka_tabulasis', function (Blueprint $table): void {
            $table->text('kesimpulan_tinjut')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kka_tabulasis', function (Blueprint $table): void {
            $table->string('kesimpulan_tinjut', 100)->nullable()->change();
        });
    }
};
