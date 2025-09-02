<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\TravelProduct;
use App\Enum\TravelProductStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelProduct>
 */
class TravelProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = TravelProduct::class;

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
        $newFilename = 'travelproduct/' . Str::random(10) . '-' . $randomImage->getFilename();

        // Copy the image to the public disk with the new filename
        Storage::disk('public')->put($newFilename, File::get($randomImage->getPathname()));

        return [
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->sentence),
            'sku' => $this->faker->unique()->numerify('SKU-#####'),
            'brand' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'regular_price' => $this->faker->randomFloat(2, 1000, 10000),
            'offer_price' => function ($attributes) {
                return $this->faker->randomFloat(2, 800, $attributes['regular_price']); // ensure offer_price is <= regular_price
            },
            'thumbnail' => $newFilename, // Placeholder image
            'stock' => $this->faker->numberBetween(10, 100),
            'is_featured' => $this->faker->boolean,
            'status' => TravelProductStatus::Active, // Assuming you are using an enum for status
        ];
    }
}
