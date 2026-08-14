<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadService
{
    protected ImageManager $manager;

    /**
     * Default width thresholds for responsive sizes.
     */
    protected array $defaultSizes = [
        'small'  => 300,
        'medium' => 750,
        'large'  => 1200,
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Upload an image and convert it into 3 WebP sizes (small, medium, large).
     *
     * @param UploadedFile|string $file UploadedFile instance or absolute local file path.
     * @param string $folder Storage folder (relative to disk 'public').
     * @param array $sizes Custom width map [ 'small' => 300, 'medium' => 750, ... ]
     * @param int $quality WebP quality (1-100).
     * @return array Map of sizes to relative storage paths, e.g. ['small' => 'events/hash_small.webp', ...]
     */
    public function upload(
        UploadedFile|string $file,
        string $folder = 'uploads',
        array $sizes = [],
        int $quality = 80
    ): array {
        $sourcePath = $this->resolveSourcePath($file);
        if (!$sourcePath || !file_exists($sourcePath)) {
            throw new \InvalidArgumentException('Source file does not exist or is invalid.');
        }

        $image = $this->manager->read($sourcePath);
        $hash = Str::uuid()->toString();
        $targetFolder = trim($folder, '/');
        $sizesToProcess = !empty($sizes) ? $sizes : $this->defaultSizes;
        $results = [];

        foreach ($sizesToProcess as $sizeName => $maxWidth) {
            $filename = "{$hash}_{$sizeName}.webp";
            $relativePath = "{$targetFolder}/{$filename}";

            $resized = clone $image;
            if ($maxWidth && $resized->width() > $maxWidth) {
                $resized->scale(width: $maxWidth);
            }

            $encoded = $resized->toWebp(quality: $quality);
            Storage::disk('public')->put($relativePath, (string) $encoded);

            $results[$sizeName] = $relativePath;
        }

        return $results;
    }

    /**
     * Upload an image and convert it to a single WebP file.
     *
     * @param UploadedFile|string $file UploadedFile instance or absolute local file path.
     * @param string $folder Storage folder (relative to disk 'public').
     * @param int|null $maxWidth Max width to scale down (null to keep original width).
     * @param int $quality WebP quality (1-100).
     * @return string Relative storage path of saved WebP file.
     */
    public function uploadSingle(
        UploadedFile|string $file,
        string $folder = 'uploads',
        ?int $maxWidth = 1200,
        int $quality = 80
    ): string {
        $sourcePath = $this->resolveSourcePath($file);
        if (!$sourcePath || !file_exists($sourcePath)) {
            throw new \InvalidArgumentException('Source file does not exist or is invalid.');
        }

        $image = $this->manager->read($sourcePath);
        $hash = Str::uuid()->toString();
        $targetFolder = trim($folder, '/');
        $filename = "{$hash}.webp";
        $relativePath = "{$targetFolder}/{$filename}";

        if ($maxWidth && $image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $encoded = $image->toWebp(quality: $quality);
        Storage::disk('public')->put($relativePath, (string) $encoded);

        return $relativePath;
    }

    /**
     * Delete an image or an array of image paths from storage.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Delete multiple image paths from storage.
     */
    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path)) {
                $this->delete($path);
            }
        }
    }

    /**
     * Helper to get real path from UploadedFile or string path.
     */
    protected function resolveSourcePath(UploadedFile|string $file): ?string
    {
        if ($file instanceof UploadedFile) {
            return $file->getRealPath();
        }

        return $file;
    }
}
