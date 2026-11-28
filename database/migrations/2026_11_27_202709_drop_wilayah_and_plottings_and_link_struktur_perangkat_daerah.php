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
        // 1. Drop foreign keys and columns referencing plottings, and add struktur_perangkat_daerah_id
        if (Schema::hasTable('tims')) {
            Schema::table('tims', function (Blueprint $table): void {
                if (Schema::hasColumn('tims', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('tims', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('nama_tim')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('sprins')) {
            Schema::table('sprins', function (Blueprint $table): void {
                if (Schema::hasColumn('sprins', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('sprins', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('tahap')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('template_pkas')) {
            Schema::table('template_pkas', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['lingkup', 'plotting_id']);
                } catch (Throwable $e) {
                }
                if (Schema::hasColumn('template_pkas', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('template_pkas', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('lingkup')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('template_langkah_kerjas')) {
            Schema::table('template_langkah_kerjas', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['lingkup', 'plotting_id']);
                } catch (Throwable $e) {
                }
                if (Schema::hasColumn('template_langkah_kerjas', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('template_langkah_kerjas', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('lingkup')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('template_kkas')) {
            Schema::table('template_kkas', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['lingkup', 'plotting_id']);
                } catch (Throwable $e) {
                }
                if (Schema::hasColumn('template_kkas', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('template_kkas', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('lingkup')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('kkas')) {
            Schema::table('kkas', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['auditee_level', 'auditee_plotting_id']);
                } catch (Throwable $e) {
                }
                if (Schema::hasColumn('kkas', 'auditee_plotting_id')) {
                    try {
                        $table->dropForeign(['auditee_plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('auditee_plotting_id');
                }
                if (! Schema::hasColumn('kkas', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('auditee_level')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['plotting_id']);
                } catch (Throwable $e) {
                }
                if (Schema::hasColumn('users', 'plotting_id')) {
                    try {
                        $table->dropForeign(['plotting_id']);
                    } catch (Throwable $e) {
                    }
                    $table->dropColumn('plotting_id');
                }
                if (! Schema::hasColumn('users', 'struktur_perangkat_daerah_id')) {
                    $table->foreignId('struktur_perangkat_daerah_id')
                        ->nullable()
                        ->after('level')
                        ->constrained('struktur_perangkat_daerah')
                        ->nullOnDelete();
                }
            });
        }

        // 2. Drop obsolete tables
        Schema::dropIfExists('plotting_province');
        Schema::dropIfExists('plottings');
        Schema::dropIfExists('wilayah');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables dropped per user request
    }
};
