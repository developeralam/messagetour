<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Enum\HotelStatus;
use App\Models\HotelRoom;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    protected static array $hotelImages = [];

    public function definition(): array
    {
        $countryId = Country::inRandomOrder()->value('id'); // Directly get the ID
        $division = Division::factory()->create(['country_id' => $countryId]);
        $district = District::factory()->create(['division_id' => $division->id]);

        // Load and cache 5 images once
        if (empty(self::$hotelImages)) {
            $sourceImages = File::files(public_path('images/hotel-seeder'));

            if (count($sourceImages) < 5) {
                throw new \Exception('Need at least 5 images in public/images/hotel-seeder');
            }

            $selectedImages = collect($sourceImages)->random(5);

            foreach ($selectedImages as $img) {
                $uuid = (string) Str::uuid();
                $filename = $uuid . '.' . $img->getExtension();
                $path = 'hotel/' . $filename;

                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->put($path, File::get($img->getPathname()));
                }

                self::$hotelImages[] = [
                    'uuid' => $uuid,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                ];
            }
        }

        // Thumbnail = random one from the structured image array
        $thumbnailImage = Arr::random(self::$hotelImages);
        $thumbnail = $thumbnailImage['path'];

        // Images = random 2-4 from the structured image array
        $images = collect(self::$hotelImages)->shuffle()->take(rand(2, 4))->values()->all();

        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'address' => $this->faker->address,
            'country_id' => $countryId,
            'division_id' => $division->id,
            'district_id' => $district->id,
            'zipcode' => 1703,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'website' => $this->faker->url,
            'checkin_time' => $this->faker->time(),
            'checkout_time' => $this->faker->time(),
            'is_featured' => $this->faker->boolean,
            'description' => $this->faker->paragraph,
            'type' => $this->faker->randomElement([1, 2, 3]), // TourType enum values
            'thumbnail' => $thumbnail,
            'images' => $images, // Structured JSON
            'google_map_iframe' => '<iframe src="https://www.google.com/maps/embed?..."></iframe>',
            'status' => HotelStatus::Active,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Hotel $hotel) {
            HotelRoom::factory()->count(10)->create([
                'hotel_id' => $hotel->id,
            ]);
        });
    }
}
