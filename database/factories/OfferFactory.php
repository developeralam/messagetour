<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Enum\OfferStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Offer::class;

    public function definition()
    {
        // Get all image files from the public/banner-seeder directory
        $bannerImages = File::files(public_path('images/exclusive-offer-seeder'));

        // Check if there are any images in the directory
        if (empty($bannerImages)) {
            throw new \Exception('No images found in the banner-seeder directory.');
        }

        // Select a random image file
        $randomImage = $bannerImages[array_rand($bannerImages)];

        // Generate a new filename for the copied image
        $newFilename = 'offer/' . Str::random(10) . '-' . $randomImage->getFilename();

        // Copy the image to the public disk with the new filename
        Storage::disk('public')->put($newFilename, File::get($randomImage->getPathname()));

        return [
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement([1, 2, 3]), // Randomly selects an OfferType
            'slug' => Str::slug($this->faker->sentence(3)),
            'thumbnail' => $newFilename, // Placeholder image path
            'description' => $this->faker->paragraph,
            'status' => OfferStatus::Active, // Assuming Active as the default status
        ];
    }
}
