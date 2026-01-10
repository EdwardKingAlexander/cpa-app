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
        Schema::create('syllabus_topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('syllabus_version_id')->constrained('syllabus_versions')->cascadeOnDelete();
            $table->string('topic_code');
            $table->string('title');
            $table->foreignUuid('parent_id')->nullable()->constrained('syllabus_topics')->nullOnDelete();
            $table->unsignedTinyInteger('depth');
            $table->unsignedInteger('display_order');
            $table->boolean('is_leaf')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['syllabus_version_id', 'topic_code']);
            $table->index(['subject_id', 'parent_id', 'topic_code']);
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabus_topics');
    }
};
