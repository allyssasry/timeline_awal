<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // database/migrations/xxxx_xx_xx_xxxxxx_create_progress_notes_table.php
        Schema::create('progress_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('progress_id');
            $table->enum('role', ['digital_banking', 'it']); // ✅ benar
            $table->text('body');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('progress_id')->references('id')->on('progresses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('progress_notes');
    }
};
