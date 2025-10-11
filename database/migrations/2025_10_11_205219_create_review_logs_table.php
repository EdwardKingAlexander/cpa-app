<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            $table->enum('grade', ['again', 'hard', 'good', 'easy']); // user button tapped
            $table->unsignedInteger('duration_ms')->default(0);    // optional timing
            $table->unsignedInteger('interval_before')->default(0);
            $table->unsignedInteger('interval_after')->default(0);
            $table->unsignedSmallInteger('ease_before')->default(250);
            $table->unsignedSmallInteger('ease_after')->default(250);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
