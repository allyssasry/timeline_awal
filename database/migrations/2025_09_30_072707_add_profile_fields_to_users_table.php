<?php

// database/migrations/2025_01_01_000000_add_profile_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gender')) $table->enum('gender', ['male','female'])->nullable()->after('password');
            if (!Schema::hasColumn('users', 'first_name')) $table->string('first_name', 100)->nullable()->after('gender');
            if (!Schema::hasColumn('users', 'last_name')) $table->string('last_name', 100)->nullable()->after('first_name');
            if (!Schema::hasColumn('users', 'address')) $table->string('address', 255)->nullable()->after('last_name');
            if (!Schema::hasColumn('users', 'username')) $table->string('username', 100)->nullable()->unique()->after('email');
            if (!Schema::hasColumn('users', 'phone')) $table->string('phone', 50)->nullable()->after('username');
            if (!Schema::hasColumn('users', 'avatar')) $table->string('avatar', 255)->nullable()->after('phone'); // path pada disk public
            // Jika Anda pakai Jetstream/Breeze yang sudah punya profile_photo_path, biarkan saja.
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar')) $table->dropColumn('avatar');
            if (Schema::hasColumn('users', 'phone')) $table->dropColumn('phone');
            if (Schema::hasColumn('users', 'username')) $table->dropUnique(['username']); // drop index unik dulu
            if (Schema::hasColumn('users', 'username')) $table->dropColumn('username');
            if (Schema::hasColumn('users', 'address')) $table->dropColumn('address');
            if (Schema::hasColumn('users', 'last_name')) $table->dropColumn('last_name');
            if (Schema::hasColumn('users', 'first_name')) $table->dropColumn('first_name');
            if (Schema::hasColumn('users', 'gender')) $table->dropColumn('gender');
        });
    }
};
