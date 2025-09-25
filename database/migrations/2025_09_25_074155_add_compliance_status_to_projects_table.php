<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_completion_status_to_projects_table.php

// database/migrations/xxxx_xx_xx_xxxxxx_add_completion_status_to_projects_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('description');
            }
            if (!Schema::hasColumn('projects', 'meets_requirement')) {
                $table->boolean('meets_requirement')->nullable()->after('completed_at');
            }
        });
    }
    public function down(): void {
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
