<?php

namespace Database\Factories;

use App\Models\Visa;
use App\Models\Country;
use App\Enum\VisaStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visa>
 */
class VisaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Visa::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'slug' => Str::slug($this->faker->sentence(3)),
            'sku_code' => $this->faker->unique()->numerify('SKU-#####'),
            'origin_country' => Country::inRandomOrder()->value('id'), // Assuming country ID 1 exists in the countries table
            'destination_country' => Country::inRandomOrder()->value('id'), // Assuming country ID 2 exists in the countries table
            'processing_time' => $this->faker->numberBetween(1, 30), // Processing time between 1 and 30 days
            'application_form' => $this->faker->url, // Assuming a URL for application form
            'convenient_fee' => $this->faker->numberBetween(100, 1000), // Random fee between 100 and 1000
            'basic_info' => $this->faker->paragraph,
            'depurture_requirements' => $this->faker->paragraph,
            'destination_requirements' => $this->faker->paragraph,
            'checklists' => $this->faker->sentence,
            'faq' => $this->faker->sentence,
            'type' => $this->faker->randomElement([1, 2, 3, 4, 5, 6]), // Assuming the default type is Tourist
            'created_by' => 1, // Assuming user ID 1 exists in the users table
            'status' => VisaStatus::Active, // Assuming VisaStatus enum
        ];
    }
}
