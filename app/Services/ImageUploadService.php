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
        array $allowedMimeTypes = [
            'image/jpeg',
            'image/gif',
            'image/webp',
            'image/png',
            'image/tiff',
        ],
        int $maxFileSize = 9920,
        ?string $baseFileName = null
    ): ?array 
    {
        // Merge dynamic allowedMimes and maxFileSize into request for validation
        $request->merge([
            'allowedMimeTypes' => $allowedMimeTypes,
            'maxFileSize'      => $maxFileSize,
        ]);
        // ✅ enforce validation before moving the file
        

        try {
            $request->validate($request->rules());
            /*
            Log::info('✅ Image validation passed for input "' . $inputName . '"', [
                'rules' => $request->rules(),
                'mime'  => $request->file($inputName)?->getMimeType(),
            ]);
            */
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Image validation failed for input "' . $inputName . '"', [
                'errors' => $e->errors(),
                'mime'   => $request->file($inputName)?->getMimeType(),
            ]);
            throw $e; // stop execution, don’t move the file
        }

        if (!$request->hasFile($inputName)) {
            return null;
        }

        /** @var UploadedFile $file */
        $file = $request->file($inputName);
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        // Use original name as base, sanitize
        if (is_null($baseFileName)) {
            $baseFileName = pathinfo($originalName, PATHINFO_FILENAME);
        }
        //$baseFileName = str_replace(' ', '-', $baseFileName);
        $baseFileName = str_replace([' ', '/'], '-', $baseFileName);
        $randomSuffix = time() . '_' . uniqid();
        $fileName = $baseFileName . '-' . $randomSuffix . '.' . $extension;

        // Paths using public_path (relative from public/)
        $relativeLargePath = "{$largeFolder}/{$fileName}";
        $relativeSmallPath = "{$smallFolder}/{$fileName}";

        $largePath = public_path($relativeLargePath);
        $smallPath = public_path($relativeSmallPath);


        /*

        Log::info('📂 ImageUploadService paths', [
            'largeFolder'       => $largeFolder,
            'smallFolder'       => $smallFolder,
            'relativeLargePath' => $relativeLargePath,
            'relativeSmallPath' => $relativeSmallPath,
            'largePath'         => $largePath,
            'smallPath'         => $smallPath
        ]);
        */
        
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
            'large' => $relativeLargePath,
            'small' => $relativeSmallPath,
            'original_name' => $originalName,
        ];
    }
}