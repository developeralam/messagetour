<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\Country;
use App\Enum\TourStatus;
use App\Models\District;
use App\Models\Division;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    protected static array $tourImages = [];

    public function definition(): array
    {
        // Load and cache 5 images once
        if (empty(self::$tourImages)) {
            $sourceImages = File::files(public_path('images/tour-seeder'));

            if (count($sourceImages) < 5) {
                throw new \Exception('Need at least 5 images in public/images/tour-seeder');
            }

            $selectedImages = collect($sourceImages)->random(5);

            foreach ($selectedImages as $img) {
                $uuid = (string) Str::uuid();
                $filename = $uuid . '.' . $img->getExtension();
                $path = 'tour/' . $filename;

                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->put($path, File::get($img->getPathname()));
                }

                self::$tourImages[] = [
                    'uuid' => $uuid,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                ];
            }
        }

        // Use one of the cached images for thumbnail
        $thumbnailImage = Arr::random(self::$tourImages);
        $thumbnail = $thumbnailImage['path'];

        // Use 2-4 random images for the 'images' field
        $countryId = Country::inRandomOrder()->value('id'); // Directly get the ID
        $division = Division::factory()->create(['country_id' => $countryId]);
        $district = District::factory()->create(['division_id' => $division->id]);
        $images = collect(self::$tourImages)->shuffle()->take(rand(2, 4))->values()->all();

        $start_date = $this->faker->dateTimeBetween('now', '+20 days'); // Start date between now and 20 days later

        // Generate end_date which is guaranteed to be at least 2 days after start_date
        $end_date = (clone $start_date)->modify('+2 days'); // end_date is 2 days later than start_date

        // If end_date is after the start_date but still violates the previous condition, regenerate
        if ($end_date < $start_date) {
            $end_date = (clone $start_date)->modify('+2 days', '+5 days'); // Ensure end_date is at least 2 days after start_date and can be up to 1 month later
        }

        return [
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->sentence),
            'location' => $this->faker->city,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'validity' => $this->faker->dateTimeBetween('now', '+1 year'),
            'member_range' => $this->faker->numberBetween(5, 20),
            'minimum_passenger' => $this->faker->numberBetween(1, 5),
            'description' => $this->faker->paragraph,
            'country_id' => $countryId,
            'division_id' => $division->id,
            'district_id' => $district->id,
            'is_featured' => $this->faker->boolean,
            'type' => $this->faker->randomElement([10, 11, 12, 13, 14]), // TourType enum values
            'regular_price' => $this->faker->randomFloat(2, 1000, 10000),
            'offer_price' => function ($attributes) {
                return $this->faker->randomFloat(2, 800, $attributes['regular_price']); // ensure offer_price is <= regular_price
            },
            'thumbnail' => $thumbnail,
            'images' => $images, // Now structured
            'status' => $this->faker->randomElement([1, 2])
        ];
    }
}
