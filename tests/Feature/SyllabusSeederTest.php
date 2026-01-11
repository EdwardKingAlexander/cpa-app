<?php

use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Database\Seeders\SyllabusTopicSeeder;
use Database\Seeders\SyllabusVersionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('creates a single connected tree per subject', function () {
    seedSyllabusSubjects();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    $version = SyllabusVersion::query()->orderByDesc('effective_date')->first();
    $subjects = Subject::query()->whereNotNull('syllabus_code')->get();

    foreach ($subjects as $subject) {
        $topics = SyllabusTopic::query()
            ->where('subject_id', $subject->id)
            ->where('syllabus_version_id', $version->id)
            ->get();

        $roots = $topics->whereNull('parent_id');
        expect($roots)->toHaveCount(1);

        $rootId = $roots->first()->id;
        $topicsById = $topics->keyBy('id');

        foreach ($topics as $topic) {
            $currentId = $topic->id;
            $seen = [];

            while ($currentId !== null) {
                expect($seen)->not->toHaveKey($currentId);
                $seen[$currentId] = true;
                $currentId = $topicsById->get($currentId)?->parent_id;
            }

            expect(array_key_exists($rootId, $seen))->toBeTrue();
        }
    }
});

it('marks leaf topics without children', function () {
    seedSyllabusSubjects();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    $topics = SyllabusTopic::query()->get();
    $childrenByParent = $topics->groupBy('parent_id');

    foreach ($topics as $topic) {
        if (! $topic->is_leaf) {
            continue;
        }

        expect($childrenByParent->has($topic->id))->toBeFalse();
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
