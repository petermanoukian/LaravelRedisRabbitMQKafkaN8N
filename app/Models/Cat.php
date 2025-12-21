<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    // Table name (optional if it matches plural of model)
    protected $table = 'cats';

    // Allow mass assignment for these fields
    protected $fillable = [
        'name',
        'des',
        'dess',
        'filer',
        'filename',
        'mime', 
        'sizer',
        'extension',
    ]; 


    protected const MIMES_WE_KNOW_OF = [
        'text/plain',
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/json',
        'text/csv',
        'application/csv',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/html',
        'application/xhtml+xml',
    ];

    protected const FRIENDLY_MIME_NAMES = [
        'text/plain' => 'Text File',
        'application/pdf' => 'PDF Document',
        'image/jpeg' => 'JPEG Image',
        'image/png' => 'PNG Image',
        'image/gif' => 'GIF Image',
        'image/webp' => 'WebP Image',
        'image/tiff' => 'TIFF Image',
        'application/msword' => 'Word Document (DOC)',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word Document (DOCX)',
        'application/vnd.ms-excel' => 'Excel Spreadsheet (XLS)',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel Spreadsheet (XLSX)',
        'application/json' => 'JSON File',
        'text/csv' => 'CSV File',
        'application/csv' => 'CSV File',
        'application/vnd.ms-powerpoint' => 'PowerPoint Presentation (PPT)',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint Presentation (PPTX)',
        'text/html' => 'HTML Document',
        'application/xhtml+xml' => 'XHTML Document',
    ];

    public function getMimeLabelAttribute(): string
    {
        $mime = strtolower(trim((string) $this->mime));

        // If the field itself is missing
        if ($mime === '') {
            return 'No MIME provided';
        }

        // If MIME isn’t one of the mimes we know of
        if (!in_array($mime, self::MIMES_WE_KNOW_OF, true)) {
            return 'Surprising MIME type';
        }

        // Known MIME → friendly label if available, else playful default
        return self::FRIENDLY_MIME_NAMES[$mime] ?? 'Surprising MIME type';
    }


    public function getSizeLabelAttribute(): string
    {
        $bytes = (int) $this->sizer;

        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $value = $bytes / pow(1024, $power);

        return round($value, 2) . ' ' . $units[$power];
    }



}
