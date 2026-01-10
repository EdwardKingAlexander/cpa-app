<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Database\Seeders\Concerns\LoadsSyllabusData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
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

        $nodes = Collection::make($data['nodes'])
            ->sortBy([
                ['depth', 'asc'],
                ['display_order', 'asc'],
            ])
            ->values();

        $childrenByParent = $nodes->groupBy(fn (array $node) => $node['parent_topic_code'] ?? '');
        $topicIdsByCode = [];

        foreach ($nodes as $node) {
            $subjectCode = $node['subject_code'];
            $subject = $subjects->get($subjectCode);

            if (! $subject) {
                throw new RuntimeException('Missing subject for syllabus code: '.$subjectCode);
            }

            $topicCode = $node['topic_code'];
            $parentCode = $node['parent_topic_code'] ?? null;
            $parentId = null;

            if ($parentCode !== null) {
                if (! array_key_exists($parentCode, $topicIdsByCode)) {
                    throw new RuntimeException('Missing parent topic for code: '.$parentCode);
                }

                $parentId = $topicIdsByCode[$parentCode];
            }

            $topicId = $this->resolveTopicId($versionCode, $topicCode, $node['id'] ?? null);
            $isLeaf = ! $childrenByParent->has($topicCode);

            $attributes = [
                'subject_id' => $subject->id,
                'syllabus_version_id' => $syllabusVersion->id,
                'topic_code' => $topicCode,
                'title' => $node['title'],
                'parent_id' => $parentId,
                'depth' => (int) $node['depth'],
                'display_order' => (int) $node['display_order'],
                'is_leaf' => $isLeaf,
                'notes' => $node['notes'] ?? null,
            ];

            $existing = SyllabusTopic::query()
                ->where('syllabus_version_id', $syllabusVersion->id)
                ->where('topic_code', $topicCode)
                ->first();

            if ($existing) {
                $existing->fill($attributes)->save();
            } else {
                SyllabusTopic::create($attributes + ['id' => $topicId]);
            }

            $topicIdsByCode[$topicCode] = $topicId;
        }
    }

    private function resolveTopicId(string $versionCode, string $topicCode, ?string $providedId): string
    {
        if ($providedId) {
            return $providedId;
        }

        return Uuid::uuid5(Uuid::NAMESPACE_URL, $versionCode.'|'.$topicCode)->toString();
    }
}
