<?php

namespace App\Services;

use App\Http\Requests\FileUploadRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class FileUploaderService
{
    public function upload(
        FileUploadRequest $request,   // validated request
        string $inputName,
        string $folder,
        string $baseFileName,
        string $randomSuffix
    ): ?array {
        if (!$request->hasFile($inputName)) {
            Log::warning('🚫 No file received under "' . $inputName . '"');
            return null;
        }

        /** @var UploadedFile $file */
        $file = $request->file($inputName);

        $extension = strtolower($file->getClientOriginalExtension());

        $fileSize = $file->getSize();
        $mineType = $file->getClientMimeType();
        $original = $file->getClientOriginalName();

        if (empty($baseFileName)) {
            $baseFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }
        $baseFileName = str_replace([' ', '/'], '-', $baseFileName);

        $fileName = $baseFileName . '_' . $randomSuffix . '.' . $extension;
        $relativePath = "{$folder}/{$fileName}";

        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        $file->move(public_path($folder), $fileName);

        return [
            'path'     => $relativePath,
            'original' => $original,
            'mime'     => $mineType,   
            'size'     => $fileSize,
            'extension'=> $extension,
            'filename' => $fileName,
        ];
    }
}
