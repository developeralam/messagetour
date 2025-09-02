<?php

namespace Database\Factories;

use App\Models\HotelRoom;
use App\Enum\HotelRoomType;
use Illuminate\Support\Str;
use App\Enum\HotelRoomStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelRoom>
 */
class HotelRoomFactory extends Factory
{
    protected $model = HotelRoom::class;

    public function definition(): array
    {
        // Get all image files from the public/banner-seeder directory
        $bannerImages = File::files(public_path('images/hotel-room-seeder'));

        // Check if there are any images in the directory
        if (empty($bannerImages)) {
            throw new \Exception('No images found in the banner-seeder directory.');
        }

        // Select a random image file
        $randomImage = $bannerImages[array_rand($bannerImages)];

        // Generate a new filename for the copied image
        $newFilename = 'hotel/room/' . Str::random(10) . '-' . $randomImage->getFilename();

        // Copy the image to the public disk with the new filename
        Storage::disk('public')->put($newFilename, File::get($randomImage->getPathname()));

        return [
            'name' => $this->faker->word . ' Room',
            'room_no' => $this->faker->unique()->numerify('Room ###'),
            'slug' => $this->faker->slug,
            'type' => HotelRoomType::Economy,
            'room_size' => $this->faker->randomElement(['25sqm', '30sqm', '35sqm']),
            'max_occupancy' => rand(1, 4),
            'regular_price' => $this->faker->randomFloat(2, 1000, 10000),
            'offer_price' => function ($attributes) {
                return $this->faker->randomFloat(2, 800, $attributes['regular_price']); // ensure offer_price is <= regular_price
            },
            'thumbnail' => $newFilename, // Placeholder image
            'images' => json_encode([
                'https://via.placeholder.com/300x200',
                'https://via.placeholder.com/300x200'
            ]),
            'status' => HotelRoomStatus::Available,
            'action_id' => null, // No action ID for now
        ];
    }
}
