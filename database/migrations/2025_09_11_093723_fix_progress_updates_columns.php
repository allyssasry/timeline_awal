<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Pastikan kolom percent ada (nullable sementara)
        if (!Schema::hasColumn('progress_updates', 'percent')) {
            Schema::table('progress_updates', function (Blueprint $table) {
                $table->unsignedTinyInteger('percent')->nullable()->after('update_date');
            });
        }

        // 2) Kalau ada progress_percent, copy ke percent
        if (Schema::hasColumn('progress_updates', 'progress_percent')) {
            DB::statement('UPDATE progress_updates SET percent = COALESCE(percent, progress_percent)');
        }

        // 3) Longgarkan kolom-kolom lain agar tidak error saat insert
        Schema::table('progress_updates', function (Blueprint $table) {
            // biar nggak wajib diisi
            if (Schema::hasColumn('progress_updates', 'progress_percent')) {
                $table->unsignedTinyInteger('progress_percent')->nullable()->change();
            }
            if (Schema::hasColumn('progress_updates', 'note')) {
                $table->text('note')->nullable()->change();
            }
            if (Schema::hasColumn('progress_updates', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->default(0)->change();
            }
        });

        // 4) Hapus kolom progress_percent supaya skema bersih (opsional tapi disarankan)
        if (Schema::hasColumn('progress_updates', 'progress_percent')) {
            Schema::table('progress_updates', function (Blueprint $table) {
                $table->dropColumn('progress_percent');
            });
        }

        // 5) Jadikan percent NOT NULL setelah migrasi data
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->unsignedTinyInteger('percent')->default(0)->change();
        });
    }

    public function down(): void
    {
        // Balikkan perubahan seminimal mungkin
        Schema::table('progress_updates', function (Blueprint $table) {
            if (!Schema::hasColumn('progress_updates', 'progress_percent')) {
                $table->unsignedTinyInteger('progress_percent')->nullable()->after('update_date');
            }
        });

        DB::statement('UPDATE progress_updates SET progress_percent = percent');

        Schema::table('progress_updates', function (Blueprint $table) {
            $table->dropColumn('percent');
        });

    }
    
};
