<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_id')->constrained('progresses')->cascadeOnDelete();
$table->unsignedBigInteger('updated_by')->default(0)->change();
            $table->date('update_date');
            $table->unsignedTinyInteger('percent')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['progress_id','update_date'], 'pu_progress_date_unique');
        });
    }

     public function down(): void
    {
        Schema::table('progress_updates', function (Blueprint $table) {
            $table->unsignedBigInteger('updated_by')->nullable(false)->change();
        });
    }
};
