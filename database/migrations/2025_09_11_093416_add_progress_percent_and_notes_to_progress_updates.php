<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->integer('progress_percent')->after('progress_id');
            $table->text('notes')->nullable()->after('progress_percent');
        });
    }

    public function down(): void
    {
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->dropColumn(['progress_percent', 'notes']);
        });
    }
};
