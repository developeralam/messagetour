<?php

namespace Database\Factories;

use App\Models\GroupFlight;
use Illuminate\Support\Str;
use App\Enum\GroupFlightType;
use App\Enum\GroupFlightStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupFlight>
 */
class GroupFlightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = GroupFlight::class;

    public function definition()
    {
        // Get all image files from the public/banner-seeder directory
        $bannerImages = File::files(public_path('images/hotel-seeder'));

        // Check if there are any images in the directory
        if (empty($bannerImages)) {
            throw new \Exception('No images found in the banner-seeder directory.');
        }

        // Select a random image file
        $randomImage = $bannerImages[array_rand($bannerImages)];

        // Generate a new filename for the copied image
        $newFilename = 'group-flight/' . Str::random(10) . '-' . $randomImage->getFilename();

        $journey_date = $this->faker->dateTimeBetween('now', '+20 days'); // Journey date between now and 20 days later
        $return_date = (clone $journey_date)->modify('+7 days'); // journey_date is 2 days later than journey_date

        // If return_date is after the journey_date but still violates the previous condition, regenerate
        if ($return_date < $journey_date) {
            $return_date = (clone $journey_date)->modify('+7 days', '+5 days'); // Ensure return_date is at least 2 days after journey_date and can be up to 1 month later
        }


        // Copy the image to the public disk with the new filename
        Storage::disk('public')->put($newFilename, File::get($randomImage->getPathname()));

        return [
            'title' => $this->faker->sentence(3),
            'slug' => Str::slug($this->faker->sentence(3)),
            'thumbnail' => 'group-flight/placeholder.jpg', // Placeholder image path
            'description' => $this->faker->paragraph,
            'type' => $this->faker->randomElement([0, 1]), // GroupFlightType enum values
            'journey_route' => $this->faker->city . ' to ' . $this->faker->city,
            'journey_transit' => $this->faker->city,
            'return_route' => $this->faker->city . ' to ' . $this->faker->city,
            'return_transit' => $this->faker->city,
            'journey_date' => $journey_date,
            'return_date' =>  $return_date,
            'airline_name' => $this->faker->company,
            'airline_code' => $this->faker->bothify('??-###'), // Airline code pattern like 'XY-123'
            'baggage_weight' => $this->faker->numberBetween(15, 50) . ' kg', // Baggage weight between 15-50 kg
            'is_food' => $this->faker->boolean, // Random true/false for food availability
            'available_seat' => $this->faker->numberBetween(50, 200), // Seats between 50 and 200
            'status' => GroupFlightStatus::Active, // Assuming Active as the default status
        ];
    }
}
