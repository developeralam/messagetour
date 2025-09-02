<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

trait InteractsWithImageUploads
{
    /**
     * Optimize and store the image using Intervention Image v3 (as WebP).
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $disk
     * @param  string  $dir
     * @param  int|null  $width
     * @param  int|null  $height
     * @param  int  $quality
     * @return string
     */
    public function optimizeAndStoreImage(
        $file,
        $disk = 'public',
        $dir = 'images',
        $width = null,
        $height = null,
        $quality = 75
    ) {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getRealPath());

        if ($width || $height) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Encode as WebP
        $encoded = $image->toWebp($quality);
        $filename = uniqid() . '.webp';
        $filePath = "{$dir}/{$filename}";

        Storage::disk($disk)->put($filePath, $encoded);

        return $filePath;
    }

    public function storeEvisaDocuments(
        UploadedFile $file,
        string $disk = 'public',
        string $dir = 'images'
    ): string {
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid()->toString() . '.' . $ext;
        $dir = trim($dir, '/'); // keep paths clean

        // Save the original bytes unchanged
        $path = $file->storeAs($dir, $filename, $disk);

        return $path;
    }

    /**
     * Delete image from a given disk/path.
     *
     * @param  string  $disk
     * @param  string  $path
     * @return bool
     */
    public function deleteImage($disk, $path)
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Update image: delete old image (if present) and store a new optimized one.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $oldImagePath
     * @param  string  $disk
     * @param  string  $dir
     * @param  int|null  $width
     * @param  int|null  $height
     * @param  int  $quality
     * @return string|null
     */
    public function optimizeAndUpdateImage(
        $file,
        $oldImagePath,
        $disk = 'public',
        $dir = 'images',
        $width = null,
        $height = null,
        $quality = 75
    ) {
        // 1) Delete old image if it exists
        if ($oldImagePath) {
            $this->deleteImage($disk, $oldImagePath);
        }

        // 2) Store and return the new path
        return $this->optimizeAndStoreImage(
            $file,
            $disk,
            $dir,
            $width,
            $height,
            $quality
        );
    }
}
