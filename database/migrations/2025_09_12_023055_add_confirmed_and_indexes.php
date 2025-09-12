<?php

// database/migrations/2025_09_12_023055_add_confirmed_and_indexes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** check if an index exists on a table (MySQL) */
    private function indexExists(string $table, string $index): bool
    {
        $schema = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT COUNT(1) AS c
               FROM information_schema.statistics
              WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$schema, $table, $index]
        );
        return ($rows[0]->c ?? 0) > 0;
    }

    public function up(): void
    {
        // unique 1x/hari (progress_id + update_date)
        if (!$this->indexExists('progress_updates', 'pu_progress_date_unique')) {
            Schema::table('progress_updates', function (Blueprint $table) {
                $table->unique(['progress_id', 'update_date'], 'pu_progress_date_unique');
            });
        }

        // flag selesai
        if (!Schema::hasColumn('progresses', 'confirmed_at')) {
            Schema::table('progresses', function (Blueprint $table) {
                $table->timestamp('confirmed_at')->nullable()->after('desired_percent');
            });
        }
    }

    public function down(): void
    {
        // drop unique if exists
        if ($this->indexExists('progress_updates', 'pu_progress_date_unique')) {
            Schema::table('progress_updates', function (Blueprint $table) {
                $table->dropUnique('pu_progress_date_unique');
            });
        }

        // drop column if exists
        if (Schema::hasColumn('progresses', 'confirmed_at')) {
            Schema::table('progresses', function (Blueprint $table) {
                $table->dropColumn('confirmed_at');
            });
        }
    }
};
