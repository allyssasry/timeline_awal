<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rename hanya jika finished_at ada dan completed_at belum ada
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'finished_at') && !Schema::hasColumn('projects', 'completed_at')) {
                $table->renameColumn('finished_at', 'completed_at');
            }
        });
    }

    public function down(): void
    {
        // Balikkan rename kalau mau rollback, aman-kan dengan pengecekan
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'completed_at') && !Schema::hasColumn('projects', 'finished_at')) {
                $table->renameColumn('completed_at', 'finished_at');
            }
        });
    }
};
