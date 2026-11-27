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
        if (! Schema::hasColumn('kka_tabulasis', 'nilai_pengembalian')) {
            Schema::table('kka_tabulasis', function (Blueprint $table): void {
                $table->decimal('nilai_pengembalian', 18, 2)->nullable()->after('tindak_lanjut');
            });
        }

        Schema::create('kka_tabulasi_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kka_tabulasi_id')->constrained('kka_tabulasis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('pesan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kka_tabulasi_messages');

        if (Schema::hasColumn('kka_tabulasis', 'nilai_pengembalian')) {
            Schema::table('kka_tabulasis', function (Blueprint $table): void {
                $table->dropColumn('nilai_pengembalian');
            });
        }
    }
};
