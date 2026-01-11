<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Database\Seeders\Concerns\LoadsSyllabusData;
use Illuminate\Database\Seeder;
use RuntimeException;

class SyllabusTopicSeeder extends Seeder
{
    use LoadsSyllabusData;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->loadSyllabusData();
        $version = $data['version'];
        $versionCode = $version['code'];
        $syllabusVersion = SyllabusVersion::query()->where('code', $versionCode)->first();

        if (! $syllabusVersion) {
            throw new RuntimeException('Syllabus version not seeded for code: '.$versionCode);
        }

        $subjects = Subject::query()
            ->whereNotNull('syllabus_code')
            ->get()
            ->keyBy('syllabus_code');

        $nodes = $data['nodes'];
        $childrenByParent = collect($nodes)->groupBy(fn (array $node) => $node['parent_topic_code'] ?? '');
        $topicsByCode = [];

        foreach ($nodes as $node) {
            $subjectCode = $node['subject_code'];
            $subject = $subjects->get($subjectCode);

            if (! $subject) {
                throw new RuntimeException('Missing subject for syllabus code: '.$subjectCode);
            }

            $topicCode = $node['topic_code'];

            $attributes = [
                'subject_id' => $subject->id,
                'title' => $node['title'],
                'depth' => (int) $node['depth'],
                'display_order' => (int) $node['display_order'],
                'notes' => $node['notes'] ?? null,
            ];

            $topic = SyllabusTopic::query()->updateOrCreate([
                'syllabus_version_id' => $syllabusVersion->id,
                'topic_code' => $topicCode,
            ], $attributes);

            $topicsByCode[$topicCode] = $topic;
        }

        foreach ($nodes as $node) {
            $topicCode = $node['topic_code'];
            $topic = $topicsByCode[$topicCode] ?? null;

            if (! $topic) {
                throw new RuntimeException('Missing topic for code: '.$topicCode);
            }

            $parentCode = $node['parent_topic_code'] ?? null;
            $parentId = null;

            if ($parentCode !== null) {
                $parent = $topicsByCode[$parentCode] ?? null;

                if (! $parent) {
                    throw new RuntimeException('Missing parent topic for code: '.$parentCode);
                }

                $parentId = $parent->id;
            }

            $isLeaf = ! $childrenByParent->has($topicCode);

            $topic->fill([
                'parent_id' => $parentId,
                'is_leaf' => $isLeaf,
            ])->save();
        }
    }
}
