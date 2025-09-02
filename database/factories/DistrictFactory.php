<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Division;
use App\Enum\DistrictStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\District>
 */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'division_id' => Division::factory(), // Dynamically create division
            'name' => $this->faker->city,
            'status' => DistrictStatus::Active,
        ];
    }
}
