<?php

use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Database\Seeders\SyllabusTopicSeeder;
use Database\Seeders\SyllabusVersionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedSyllabusSubjects(): void
{
    $subjects = [
        ['code' => 'FAR', 'syllabus_code' => 'FAR', 'name' => 'Financial Accounting and Reporting', 'exam_question_count' => 70],
        ['code' => 'AFAR', 'syllabus_code' => 'AFAR', 'name' => 'Advanced Financial Accounting and Reporting', 'exam_question_count' => 70],
        ['code' => 'MAS', 'syllabus_code' => 'MS', 'name' => 'Management Accounting and Services', 'exam_question_count' => 70],
        ['code' => 'AUDIT', 'syllabus_code' => 'AUD', 'name' => 'Auditing', 'exam_question_count' => 70],
        ['code' => 'RFBT', 'syllabus_code' => 'RFBT', 'name' => 'Regulatory Framework for Business Transactions', 'exam_question_count' => 100],
        ['code' => 'TAX', 'syllabus_code' => 'TAX', 'name' => 'Taxation', 'exam_question_count' => 70],
    ];

    foreach ($subjects as $subject) {
        Subject::create($subject);
    }
}

it('seeds syllabus topics idempotently', function () {
    seedSyllabusSubjects();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    $initialCount = SyllabusTopic::count();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    expect(SyllabusTopic::count())->toBe($initialCount);
});

it('keeps the syllabus topic tree consistent', function () {
    seedSyllabusSubjects();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    $topics = SyllabusTopic::query()->get()->keyBy('id');

    foreach ($topics as $topic) {
        if (! $topic->parent_id) {
            continue;
        }

        expect($topics->has($topic->parent_id))->toBeTrue();
        expect($topics->get($topic->parent_id)->syllabus_version_id)
            ->toBe($topic->syllabus_version_id);
    }

    foreach ($topics as $topic) {
        $seen = [];
        $currentId = $topic->id;

        while ($currentId) {
            expect($seen)->not->toHaveKey($currentId);
            $seen[$currentId] = true;
            $currentId = $topics->get($currentId)?->parent_id;
        }
    }
});

it('enforces unique topic codes per version', function () {
    $subject = Subject::create([
        'code' => 'FAR',
        'syllabus_code' => 'FAR',
        'name' => 'Financial Accounting and Reporting',
        'exam_question_count' => 70,
    ]);

    $version = SyllabusVersion::factory()->create([
        'code' => '2022-10',
        'effective_date' => '2022-10-01',
    ]);

    SyllabusTopic::create([
        'subject_id' => $subject->id,
        'syllabus_version_id' => $version->id,
        'topic_code' => 'FAR',
        'title' => 'Financial Accounting and Reporting',
        'parent_id' => null,
        'depth' => 0,
        'display_order' => 1,
        'is_leaf' => true,
    ]);

    expect(function () use ($subject, $version): void {
        SyllabusTopic::create([
            'subject_id' => $subject->id,
            'syllabus_version_id' => $version->id,
            'topic_code' => 'FAR',
            'title' => 'Duplicate',
            'parent_id' => null,
            'depth' => 0,
            'display_order' => 2,
            'is_leaf' => true,
        ]);
    })->toThrow(QueryException::class);
});
