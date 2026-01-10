<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

trait LoadsSyllabusData
{
    /**
     * @return array{
     *     version: array{code: string, effective_date: string, source: string|null},
     *     nodes: list<array{
     *         subject_code: string,
     *         topic_code: string,
     *         title: string,
     *         depth: int,
     *         parent_topic_code: string|null,
     *         display_order: int,
     *         exam_question_count: int|null,
     *         notes: string|null
     *     }>
     * }
     */
    protected function loadSyllabusData(): array
    {
        $path = database_path('seeders/data/lecp_syllabus_v2022_10.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read syllabus dataset: '.$path);
        }

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! isset($data['version'], $data['nodes'])) {
            throw new RuntimeException('Syllabus dataset is missing required keys.');
        }

        return $data;
    }
}
