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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('role')->default('Auditor')->after('phone');
            $table->string('level')->nullable()->after('role');
            $table->foreignId('plotting_id')->nullable()->after('level')->constrained('plottings')->nullOnDelete();
            $table->char('province_code', 2)->nullable()->after('plotting_id');
            $table->char('city_code', 4)->nullable()->after('province_code');
            $table->char('district_code', 7)->nullable()->after('city_code');
            $table->char('village_code', 10)->nullable()->after('district_code');
            $table->string('bidang')->nullable()->after('village_code');
            $table->string('jabatan')->nullable()->after('bidang');
            $table->string('ttd_path')->nullable()->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('plotting_id');
            $table->dropColumn([
                'username',
                'phone',
                'role',
                'level',
                'province_code',
                'city_code',
                'district_code',
                'village_code',
                'bidang',
                'jabatan',
                'ttd_path',
            ]);
        });
    }
};
