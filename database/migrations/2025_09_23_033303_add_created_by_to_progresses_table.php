<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progresses', function (Blueprint $table) {
            // tambah kolom created_by (nullable dulu supaya aman untuk data lama)
            if (!Schema::hasColumn('progresses', 'created_by')) {
                $table->foreignId('created_by')
                      ->nullable()
                      ->constrained('users')   // refer ke tabel users (kolom id)
                      ->nullOnDelete();        // kalau user dihapus, set NULL
            }
        });
    }

    public function down(): void
    {
        Schema::table('progresses', function (Blueprint $table) {
            if (Schema::hasColumn('progresses', 'created_by')) {
                // drop FK lalu kolom
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
