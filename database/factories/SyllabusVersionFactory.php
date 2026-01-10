<?php

namespace Database\Factories;

use App\Models\SyllabusVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyllabusVersion>
 */
class SyllabusVersionFactory extends Factory
{
    protected $model = SyllabusVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2030);
        $month = $this->faker->numberBetween(1, 12);

        return [
            'code' => sprintf('%d-%02d', $year, $month),
            'effective_date' => $this->faker->date(),
            'source' => null,
        ];
    }
}
