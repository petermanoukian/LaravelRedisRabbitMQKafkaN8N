<?php

namespace App\Services;

use App\Http\Requests\ImageUploadRequest;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    public function upload(
        ImageUploadRequest $request,
        string $inputName,
        string $largeFolder,
        string $smallFolder,
        int $maxWidth = 1500,
        int $maxHeight = 1000,
        ?string $baseFileName = null
    ): ?array 
    {
        Log::info("ImageUploadService: starting upload for input [$inputName]");
        if (!$request->hasFile($inputName)) {
            return null;
        }

        /** @var UploadedFile $file */
        $file = $request->file($inputName);


        Log::info("ImageUploadService: file received", 
        [ 'originalName' => $file->getClientOriginalName(), 'extension' => $file->getClientOriginalExtension(), 
        'size' => $file->getSize(), 'mime' => $file->getMimeType(), ]);


        $originalName = $file->getClientOriginalName();
        $extension    = strtolower($file->getClientOriginalExtension());

        // Use original name as base, sanitize
        if (is_null($baseFileName)) {
            $baseFileName = pathinfo($originalName, PATHINFO_FILENAME);
        }
        $baseFileName = str_replace([' ', '/'], '-', $baseFileName);

        $randomSuffix = time() . '_' . uniqid();
        $fileName     = $baseFileName . '-' . $randomSuffix . '.' . $extension;

        // Paths relative to public/
        $relativeLargePath = "{$largeFolder}/{$fileName}";
        $relativeSmallPath = "{$smallFolder}/{$fileName}";

        $largePath = public_path($relativeLargePath);
        $smallPath = public_path($relativeSmallPath);


        Log::info("ImageUploadService: saving paths", [ 'largePath' => $largePath, 'smallPath' => $smallPath, ]);

        // Ensure folders exist
        if (!file_exists(dirname($largePath))) {
            mkdir(dirname($largePath), 0755, true);
        }
        if (!file_exists(dirname($smallPath))) {
            mkdir(dirname($smallPath), 0755, true);
        }

        // Process with Intervention Image v3
        $manager = new ImageManager(new Driver());

        // Large version: resize if needed
        $image = $manager->read($file->getPathname());
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Log::info("ImageUploadService: resized image");
        }
        $image->save($largePath);

        // Thumbnail: cropped square
        $thumbImage = $manager->read($file->getPathname());
        $thumbImage->cover(200, 200)->save($smallPath);

        return [
            'large'         => $relativeLargePath,
            'small'         => $relativeSmallPath,
            'original_name' => $originalName,
        ];
    }
}
