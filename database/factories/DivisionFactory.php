<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Division;
use App\Enum\DivisionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Division>
 */
class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::inRandomOrder()->first(),
            'name' => $this->faker->state,
            'status' => DivisionStatus::Active,
        ];
    }
}
