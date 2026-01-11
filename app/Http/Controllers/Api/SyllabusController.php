<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SyllabusController extends Controller
{
    public function subjects(): JsonResponse
    {
        $subjects = Subject::query()
            ->with('defaultSyllabusVersion')
            ->orderBy('code')
            ->get();

        $payload = $subjects->map(function (Subject $subject): array {
            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'syllabus_code' => $subject->syllabus_code,
                'name' => $subject->name,
                'exam_question_count' => $subject->exam_question_count,
                'default_syllabus_version' => $this->formatVersion($subject->defaultSyllabusVersion),
            ];
        });

        return response()->json(['data' => $payload]);
    }

    public function subjectTree(Request $request, Subject $subject): JsonResponse
    {
        $version = $this->resolveVersion($request->query('version'), $subject->default_syllabus_version_id);

        $topics = SyllabusTopic::query()
            ->where('subject_id', $subject->id)
            ->where('syllabus_version_id', $version->id)
            ->orderBy('depth')
            ->orderBy('display_order')
            ->get();

        $root = $topics->firstWhere('topic_code', $subject->syllabus_code);

        if (! $root) {
            abort(404);
        }

        return response()->json([
            'subject' => [
                'id' => $subject->id,
                'code' => $subject->code,
                'syllabus_code' => $subject->syllabus_code,
                'name' => $subject->name,
                'exam_question_count' => $subject->exam_question_count,
            ],
            'syllabus_version' => $this->formatVersion($version),
            'tree' => $this->buildTopicTree($topics, $root->id),
        ]);
    }

    public function topic(Request $request, string $topicCode): JsonResponse
    {
        $version = $this->resolveVersion($request->query('version'), null);

        $topic = SyllabusTopic::query()
            ->with(['subject', 'parent'])
            ->where('syllabus_version_id', $version->id)
            ->where('topic_code', $topicCode)
            ->firstOrFail();

        $topics = SyllabusTopic::query()
            ->where('subject_id', $topic->subject_id)
            ->where('syllabus_version_id', $version->id)
            ->orderBy('depth')
            ->orderBy('display_order')
            ->get();

        $tree = $this->buildTopicTree($topics, $topic->id);

        if ($tree === []) {
            abort(404);
        }

        return response()->json([
            'subject' => [
                'id' => $topic->subject->id,
                'code' => $topic->subject->code,
                'syllabus_code' => $topic->subject->syllabus_code,
                'name' => $topic->subject->name,
                'exam_question_count' => $topic->subject->exam_question_count,
            ],
            'syllabus_version' => $this->formatVersion($version),
            'topic' => $tree[0] + [
                'parent_topic_code' => $topic->parent?->topic_code,
            ],
        ]);
    }

    private function resolveVersion(?string $versionCode, ?string $fallbackVersionId): SyllabusVersion
    {
        if ($versionCode) {
            $version = SyllabusVersion::query()->where('code', $versionCode)->first();

            if (! $version) {
                abort(404);
            }

            return $version;
        }

        if ($fallbackVersionId) {
            $version = SyllabusVersion::query()->find($fallbackVersionId);

            if ($version) {
                return $version;
            }
        }

        return SyllabusVersion::query()
            ->orderByDesc('effective_date')
            ->firstOrFail();
    }

    private function buildTopicTree(Collection $topics, ?string $rootId = null): array
    {
        $topicsByParent = $topics->groupBy('parent_id');

        $build = function (?string $parentId) use (&$build, $topicsByParent): array {
            return ($topicsByParent->get($parentId) ?? collect())
                ->sortBy('display_order')
                ->map(function (SyllabusTopic $topic) use (&$build): array {
                    return [
                        'id' => $topic->id,
                        'topic_code' => $topic->topic_code,
                        'title' => $topic->title,
                        'depth' => $topic->depth,
                        'display_order' => $topic->display_order,
                        'is_leaf' => $topic->is_leaf,
                        'children' => $build($topic->id),
                    ];
                })
                ->values()
                ->all();
        };

        if ($rootId === null) {
            return $build(null);
        }

        $root = $topics->firstWhere('id', $rootId);

        if (! $root) {
            return [];
        }

        return [[
            'id' => $root->id,
            'topic_code' => $root->topic_code,
            'title' => $root->title,
            'depth' => $root->depth,
            'display_order' => $root->display_order,
            'is_leaf' => $root->is_leaf,
            'children' => $build($root->id),
        ]];
    }

    private function formatVersion(?SyllabusVersion $version): ?array
    {
        if (! $version) {
            return null;
        }

        return [
            'id' => $version->id,
            'code' => $version->code,
            'effective_date' => $version->effective_date?->toDateString(),
            'source' => $version->source,
        ];
    }
}
