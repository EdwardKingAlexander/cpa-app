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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // SM-2 scheduling fields
            $table->dateTime('due_at')->index();              // next time this card is due
            $table->unsignedInteger('interval')->default(0);  // days until next review
            $table->unsignedInteger('repetitions')->default(0);
            $table->unsignedInteger('lapses')->default(0);
            $table->unsignedSmallInteger('ease')->default(250); // ease*100 (2.5 => 250)

            // State
            $table->boolean('suspended')->default(false);
            $table->json('meta')->nullable(); // tags, deck, custom flags
            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
