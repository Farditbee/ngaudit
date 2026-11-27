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
            $table->string('status_tinjut', 40)->default('Belum Ditinjut')->after('status');
            $table->foreignId('kode_tinjut_id')->nullable()->after('status_tinjut')->constrained('kodes')->nullOnDelete();
            $table->foreignId('sub_kode_tinjut_id')->nullable()->after('kode_tinjut_id')->constrained('sub_kodes')->nullOnDelete();
            $table->longText('tindak_lanjut')->nullable()->after('sub_kode_tinjut_id');
            $table->string('kesimpulan_tinjut', 100)->nullable()->after('tindak_lanjut');
            $table->text('alasan_tinjut')->nullable()->after('kesimpulan_tinjut');
            $table->string('lampiran_tinjut_path')->nullable()->after('alasan_tinjut');
            $table->string('lampiran_tinjut_name')->nullable()->after('lampiran_tinjut_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kka_tabulasis', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kode_tinjut_id');
            $table->dropConstrainedForeignId('sub_kode_tinjut_id');
            $table->dropColumn([
                'status_tinjut',
                'tindak_lanjut',
                'kesimpulan_tinjut',
                'alasan_tinjut',
                'lampiran_tinjut_path',
                'lampiran_tinjut_name',
            ]);
        });
    }
};
