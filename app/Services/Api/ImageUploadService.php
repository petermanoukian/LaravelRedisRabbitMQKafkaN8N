<?php

namespace App\Services\Api;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;

class ImageUploadService
{
    /**
     * Upload and process image: resize large version, create thumbnail.
     * Assumes validation has been performed elsewhere (e.g., via FormRequest).
     *
     * @param Request $request
     * @param string $inputName The name of the file input field (e.g., 'image')
     * @param string $largeFolder Folder for the large resized image (e.g., 'uploads/large')
     * @param string $smallFolder Folder for the thumbnail (e.g., 'uploads/small')
     * @param int $maxWidth Maximum width for large image (default: 1500)
     * @param int $maxHeight Maximum height for large image (default: 100)
     * @return array ['large_path' => string, 'small_path' => string, 'original_name' => string] | null on failure
     */
    public function upload(
        Request $request,
        string $inputName,
        string $largeFolder,
        string $smallFolder,
        int $maxWidth = 1500,
        int $maxHeight = 1000
    ): ?array 
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }

        /** @var UploadedFile $file */
        $file = $request->file($inputName);
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Use original name as base, sanitize
        $baseFileName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseFileName = str_replace(' ', '-', $baseFileName);
        $randomSuffix = time() . '_' . uniqid();
        $fileName = $baseFileName . '-' . $randomSuffix . '.' . $extension;

        // Paths using public_path (relative from public/)
        $relativeLargePath = "{$largeFolder}/{$fileName}";
        $relativeSmallPath = "{$smallFolder}/{$fileName}";

        $largePath = public_path($relativeLargePath);
        $smallPath = public_path($relativeSmallPath);

        // Ensure folders exist
        $largeDir = dirname($largePath);
        if (!file_exists($largeDir)) {
            mkdir($largeDir, 0755, true);
        }

        $smallDir = dirname($smallPath);
        if (!file_exists($smallDir)) {
            mkdir($smallDir, 0755, true);
        }

        // Process with Intervention Image v3
        $manager = new ImageManager(new Driver());

        // Large version: resize if needed
        $image = $manager->read($file->getPathname());
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Don't upscale smaller images
            });
        }
        $image->save($largePath);

        // Thumbnail: e.g., cover to 200x200 (cropped square, adjust as needed)
        $thumbImage = $manager->read($file->getPathname());
        $thumbImage->cover(200, 200)->save($smallPath);

        return [
            'large_path' => $relativeLargePath,
            'small_path' => $relativeSmallPath,
            'original_name' => $originalName,
        ];
    }
}