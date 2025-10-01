<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileHelper
{
    /**
     * Upload file ke storage
     */
    public static function upload(UploadedFile $file, $path = 'uploads')
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs($path, $filename, 'public');
    }

    /**
     * Hapus file dari storage
     */
    public static function delete($filepath)
    {
        if ($filepath && Storage::disk('public')->exists($filepath)) {
            Storage::disk('public')->delete($filepath);
            return true;
        }
        return false;
    }

    /**
     * Ganti file lama dengan file baru
     */
    public static function replace($oldFile, UploadedFile $newFile, $path = 'uploads')
    {
        self::delete($oldFile);
        return self::upload($newFile, $path);
    }
}
