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
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('syllabus_code')->nullable()->unique();
            $table->foreignUuid('default_syllabus_version_id')->nullable()
                ->constrained('syllabus_versions')
                ->nullOnDelete();
            $table->unsignedSmallInteger('exam_question_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['default_syllabus_version_id']);
            $table->dropUnique(['syllabus_code']);
            $table->dropColumn(['syllabus_code', 'default_syllabus_version_id', 'exam_question_count']);
        });
    }
};
