<?php

// database/migrations/2025_09_15_000001_fix_role_enum_on_progress_notes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
// php artisan make:migration alter_progress_notes_role_userid --table=progress_notes

public function up(): void
{
    Schema::table('progress_notes', function (Blueprint $table) {
        // ubah role -> enum yang benar
        $table->enum('role', ['digital_banking','it'])->nullable()->change();
        // ganti created_by -> user_id kalau sebelumnya created_by
        if (Schema::hasColumn('progress_notes','created_by')) {
            $table->renameColumn('created_by','user_id');
        }
    });
}

public function down(): void
{
    Schema::table('progress_notes', function (Blueprint $table) {
        $table->string('role', 50)->nullable()->change();
        if (Schema::hasColumn('progress_notes','user_id')) {
            $table->renameColumn('user_id','created_by');
        }
    });
}

};
