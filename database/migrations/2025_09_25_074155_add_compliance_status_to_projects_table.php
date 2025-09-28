<?php
// database/migrations/2025_09_25_074155_add_compliance_status_to_projects_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Tambah completed_at hanya jika belum ada
            if (!Schema::hasColumn('projects', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('description');
            }

            // Tambah meets_requirement hanya jika belum ada
            if (!Schema::hasColumn('projects', 'meets_requirement')) {
                $table->boolean('meets_requirement')->nullable()->after('completed_at');
            }
        });

        // Index juga aman-aman saja kalau dicek dulu
        Schema::table('projects', function (Blueprint $table) {
            // Laravel tidak punya helper "hasIndex", jadi biarkan tanpa index
            // atau buat index hanya sekali melalui migration pertama kali dibuat.
            // Jika tetap butuh index, kamu bisa bikin nama index spesifik dan bungkus try-catch,
            // tapi yang paling aman: biarkan tanpa index ganda.
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'meets_requirement')) {
                $table->dropColumn('meets_requirement');
            }
            if (Schema::hasColumn('projects', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
