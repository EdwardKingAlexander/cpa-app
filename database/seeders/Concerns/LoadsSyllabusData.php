<?php

namespace Database\Seeders\Concerns;

use RuntimeException;

trait LoadsSyllabusData
{
    private const DATASET_PATH = 'seeders/data/lecp_syllabus_v2022_10_full.json';

    private const SUBJECT_CODES = ['FAR', 'AFAR', 'MS', 'AUD', 'RFBT', 'TAX'];

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
        $path = database_path(self::DATASET_PATH);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read syllabus dataset: '.$path);
        }

        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException('Syllabus dataset is not a valid JSON object.');
        }

        $version = $this->normalizeVersion($data);
        $nodes = $this->normalizeNodes($data);

        $normalized = [
            'version' => $version,
            'nodes' => $nodes,
        ];

        $this->validateSyllabusData($normalized);

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{code: string, effective_date: string, source: string|null}
     */
    private function normalizeVersion(array $data): array
    {
        if (isset($data['version']) && is_array($data['version'])) {
            $version = $data['version'];
            $code = $version['code'] ?? null;
            $effectiveDate = $version['effective_date'] ?? null;

            if (! is_string($code) || trim($code) === '') {
                throw new RuntimeException('Syllabus dataset version code is missing.');
            }

            if (! is_string($effectiveDate) || trim($effectiveDate) === '') {
                throw new RuntimeException('Syllabus dataset effective date is missing.');
            }

            return [
                'code' => $code,
                'effective_date' => $effectiveDate,
                'source' => isset($version['source']) && is_string($version['source']) ? $version['source'] : null,
            ];
        }

        $versionCode = $data['syllabus_version'] ?? null;

        if (! is_string($versionCode) || trim($versionCode) === '') {
            throw new RuntimeException('Syllabus dataset version code is missing.');
        }

        return [
            'code' => $versionCode,
            'effective_date' => $this->inferEffectiveDate($versionCode),
            'source' => isset($data['source']) && is_string($data['source']) ? $data['source'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function normalizeNodes(array $data): array
    {
        $nodes = $data['nodes'] ?? null;

        if (! is_array($nodes) || $nodes === []) {
            throw new RuntimeException('Syllabus dataset is missing nodes.');
        }

        return $nodes;
    }

    private function inferEffectiveDate(string $versionCode): string
    {
        if (! preg_match('/^(\d{4})-(\d{2})$/', $versionCode, $matches)) {
            throw new RuntimeException('Syllabus dataset version code must be in YYYY-MM format.');
        }

        return $matches[1].'-'.$matches[2].'-01';
    }

    /**
     * @param array{
     *     version: array{code: string, effective_date: string, source: string|null},
     *     nodes: list<array<string, mixed>>
     * } $data
     */
    private function validateSyllabusData(array $data): void
    {
        $nodes = $data['nodes'];
        $nodesByCode = [];
        $rootsBySubject = [];
        $subjectCodes = [];

        foreach ($nodes as $index => $node) {
            if (! is_array($node)) {
                throw new RuntimeException('Syllabus node at index '.$index.' is invalid.');
            }

            $subjectCode = $this->requireNonEmptyString($node, 'subject_code', $index);

            if (! in_array($subjectCode, self::SUBJECT_CODES, true)) {
                throw new RuntimeException('Invalid subject code: '.$subjectCode);
            }
            $topicCode = $this->requireNonEmptyString($node, 'topic_code', $index);
            $title = $this->requireNonEmptyString($node, 'title', $index);
            $depth = $this->requireInt($node, 'depth', $index);

            if (array_key_exists($topicCode, $nodesByCode)) {
                throw new RuntimeException('Duplicate topic code found: '.$topicCode);
            }

            $subjectCodes[$subjectCode] = true;
            $nodesByCode[$topicCode] = $node + ['subject_code' => $subjectCode, 'topic_code' => $topicCode, 'title' => $title, 'depth' => $depth];

            $expectedDepth = $this->expectedDepth($subjectCode, $topicCode);
            if ($depth !== $expectedDepth) {
                throw new RuntimeException('Depth mismatch for topic code: '.$topicCode);
            }

            $parentCode = $node['parent_topic_code'] ?? null;

            if ($parentCode !== null && (! is_string($parentCode) || trim($parentCode) === '')) {
                throw new RuntimeException('Parent topic code is invalid for topic code: '.$topicCode);
            }

            if ($depth === 0) {
                if ($parentCode !== null) {
                    throw new RuntimeException('Root topic should not define a parent: '.$topicCode);
                }

                $rootsBySubject[$subjectCode][] = $topicCode;
            } elseif ($parentCode === null) {
                throw new RuntimeException('Non-root topic is missing a parent: '.$topicCode);
            }
        }

        foreach ($nodesByCode as $topicCode => $node) {
            $parentCode = $node['parent_topic_code'] ?? null;

            if ($parentCode === null) {
                continue;
            }

            $parent = $nodesByCode[$parentCode] ?? null;

            if ($parent === null) {
                throw new RuntimeException('Missing parent topic for code: '.$parentCode);
            }

            if (($parent['subject_code'] ?? null) !== ($node['subject_code'] ?? null)) {
                throw new RuntimeException('Parent subject mismatch for topic code: '.$topicCode);
            }
        }

        foreach (array_keys($subjectCodes) as $subjectCode) {
            $roots = $rootsBySubject[$subjectCode] ?? [];

            if (count($roots) !== 1) {
                throw new RuntimeException('Subject must have exactly one root node: '.$subjectCode);
            }

            if ($roots[0] !== $subjectCode) {
                throw new RuntimeException('Root topic code must match subject code: '.$subjectCode);
            }
        }

        foreach (array_keys($nodesByCode) as $topicCode) {
            $seen = [];
            $currentCode = $topicCode;

            while ($currentCode !== null) {
                if (array_key_exists($currentCode, $seen)) {
                    throw new RuntimeException('Cycle detected at topic code: '.$currentCode);
                }

                $seen[$currentCode] = true;
                $currentNode = $nodesByCode[$currentCode] ?? null;
                $currentCode = $currentNode['parent_topic_code'] ?? null;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function requireNonEmptyString(array $node, string $key, int $index): string
    {
        $value = $node[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException('Syllabus node at index '.$index.' is missing '.$key.'.');
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function requireInt(array $node, string $key, int $index): int
    {
        $value = $node[$key] ?? null;

        if (! is_int($value)) {
            if (is_numeric($value)) {
                return (int) $value;
            }

            throw new RuntimeException('Syllabus node at index '.$index.' is missing '.$key.'.');
        }

        return $value;
    }

    private function expectedDepth(string $subjectCode, string $topicCode): int
    {
        if ($topicCode === $subjectCode) {
            return 0;
        }

        $prefix = $subjectCode.'-';

        if (! str_starts_with($topicCode, $prefix)) {
            throw new RuntimeException('Topic code does not match subject code: '.$topicCode);
        }

        $suffix = substr($topicCode, strlen($prefix));

        if ($suffix === '') {
            throw new RuntimeException('Topic code is missing depth segments: '.$topicCode);
        }

        $segments = explode('.', $suffix);

        foreach ($segments as $segment) {
            if ($segment === '') {
                throw new RuntimeException('Topic code has empty depth segment: '.$topicCode);
            }
        }

        return count($segments);
    }
}
