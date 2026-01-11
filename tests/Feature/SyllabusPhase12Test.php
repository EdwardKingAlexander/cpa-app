<?php

use App\Filament\Resources\SyllabusTopics\Pages\ListSyllabusTopics;
use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use App\Models\User;
use Database\Seeders\SyllabusTopicSeeder;
use Database\Seeders\SyllabusVersionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedSyllabusSubjects();

    $this->seed([
        SyllabusVersionSeeder::class,
        SyllabusTopicSeeder::class,
    ]);

    $version = SyllabusVersion::query()->orderByDesc('effective_date')->first();

    if ($version) {
        Subject::query()
            ->whereNotNull('syllabus_code')
            ->update(['default_syllabus_version_id' => $version->id]);
    }
});

it('loads the syllabus topics resource with seeded data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Filament::setCurrentPanel('admin');

    $version = SyllabusVersion::query()->orderByDesc('effective_date')->firstOrFail();
    $topic = SyllabusTopic::query()
        ->where('syllabus_version_id', $version->id)
        ->orderBy('depth')
        ->orderBy('display_order')
        ->firstOrFail();

    Livewire::test(ListSyllabusTopics::class)
        ->filterTable('syllabusVersion', $version->id)
        ->searchTable($topic->topic_code)
        ->assertCanSeeTableRecords([$topic]);
});

it('returns ordered trees and leaf nodes without children', function () {
    $subject = Subject::query()->where('code', 'FAR')->firstOrFail();

    $version = SyllabusVersion::create([
        'code' => '2021-01',
        'effective_date' => '2021-01-01',
        'source' => null,
    ]);

    $root = SyllabusTopic::create([
        'subject_id' => $subject->id,
        'syllabus_version_id' => $version->id,
        'topic_code' => 'FAR',
        'title' => 'Legacy FAR',
        'parent_id' => null,
        'depth' => 0,
        'display_order' => 0,
        'is_leaf' => false,
        'notes' => null,
    ]);

    SyllabusTopic::create([
        'subject_id' => $subject->id,
        'syllabus_version_id' => $version->id,
        'topic_code' => 'FAR-1',
        'title' => 'Legacy Topic A',
        'parent_id' => $root->id,
        'depth' => 1,
        'display_order' => 2,
        'is_leaf' => true,
        'notes' => null,
    ]);

    SyllabusTopic::create([
        'subject_id' => $subject->id,
        'syllabus_version_id' => $version->id,
        'topic_code' => 'FAR-2',
        'title' => 'Legacy Topic B',
        'parent_id' => $root->id,
        'depth' => 1,
        'display_order' => 1,
        'is_leaf' => true,
        'notes' => null,
    ]);

    $response = $this->getJson(route('api.v1.syllabus.subject.tree', [
        'subject' => $subject->code,
        'version' => $version->code,
    ]));

    $response->assertSuccessful();
    expect($response->json('syllabus_version.code'))->toBe($version->code);

    $tree = $response->json('tree');
    expect($tree)->toHaveCount(1);
    expect($tree[0]['topic_code'])->toBe('FAR');
    expect(array_column($tree[0]['children'], 'topic_code'))->toBe(['FAR-2', 'FAR-1']);

    $flatten = function (array $nodes) use (&$flatten): array {
        $flattened = [];

        foreach ($nodes as $node) {
            $flattened[] = $node;
            $flattened = array_merge($flattened, $flatten($node['children'] ?? []));
        }

        return $flattened;
    };

    $nodes = $flatten($tree);

    foreach ($nodes as $node) {
        foreach (['id', 'topic_code', 'title', 'depth', 'is_leaf', 'children'] as $key) {
            expect(array_key_exists($key, $node))->toBeTrue();
        }

        if ($node['is_leaf']) {
            expect($node['children'])->toBe([]);
        }
    }
});

it('returns version-scoped topic details', function () {
    $subject = Subject::query()->where('code', 'FAR')->firstOrFail();

    $version = SyllabusVersion::create([
        'code' => '2021-04',
        'effective_date' => '2021-04-01',
        'source' => null,
    ]);

    $topic = SyllabusTopic::create([
        'subject_id' => $subject->id,
        'syllabus_version_id' => $version->id,
        'topic_code' => 'FAR',
        'title' => 'Historical FAR',
        'parent_id' => null,
        'depth' => 0,
        'display_order' => 0,
        'is_leaf' => true,
        'notes' => null,
    ]);

    $response = $this->getJson(route('api.v1.syllabus.topics.show', [
        'topicCode' => $topic->topic_code,
        'version' => $version->code,
    ]));

    $response->assertSuccessful();
    expect($response->json('syllabus_version.code'))->toBe($version->code);
    expect($response->json('topic.id'))->toBe($topic->id);
    expect($response->json('topic.title'))->toBe('Historical FAR');
    expect($response->json('topic.children'))->toBe([]);
});
