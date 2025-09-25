<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_completion_status_to_projects_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('description');
            $table->boolean('meets_requirement')->nullable()->after('completed_at'); // null: belum diputuskan
            $table->index(['completed_at','meets_requirement']);
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['completed_at','meets_requirement']);
        });
    }
};
