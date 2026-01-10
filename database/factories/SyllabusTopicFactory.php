<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\SyllabusTopic;
use App\Models\SyllabusVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyllabusTopic>
 */
class SyllabusTopicFactory extends Factory
{
    protected $model = SyllabusTopic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = Subject::query()->firstOrCreate(
            ['code' => 'FAR'],
            [
                'name' => 'Financial Accounting and Reporting',
                'syllabus_code' => 'FAR',
                'exam_question_count' => 70,
            ]
        );

        $syllabusVersion = SyllabusVersion::factory()->create();
        $topicCode = $subject->syllabus_code.'-'.$this->faker->unique()->numberBetween(1, 50);

        return [
            'subject_id' => $subject->id,
            'syllabus_version_id' => $syllabusVersion->id,
            'topic_code' => $topicCode,
            'title' => $this->faker->sentence(3),
            'parent_id' => null,
            'depth' => 1,
            'display_order' => 1,
            'is_leaf' => true,
            'notes' => null,
        ];
    }
}
