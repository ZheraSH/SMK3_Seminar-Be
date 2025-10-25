<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageCompressing
{

    public static function process($fileName, UploadedFile $request, String $targetPath, array $options = [])
    {
        $number = 1;
        $fileName = $options['name'] ?? $fileName.$number++;
        $originalFileExt = $request->getClientOriginalExtension();

        $uploadImage = $request->storeAs("{$targetPath}", "{$fileName}.{$originalFileExt}", 'public');
        $compressTargetImage = "storage/{$uploadImage}";

        $options['targetPath'] = $targetPath;
        $options['name'] = $fileName;
        $options['extension'] = $originalFileExt;
        $options['original_uploaded_file'] = $compressTargetImage;

        $compressResult = self::processCompressImage($compressTargetImage, $options);

        return collect([
            ...$compressResult,
            ...$options,
        ]);   
    }

    public static function processCompressImage(string $imagePath, array $options = [])
    {
        $imageInfo = self::getFileInfo($imagePath);
        if ($imageInfo['mime'] == 'image/gif') {
            $imageLayer = imagecreatefromgif($imagePath);
        } elseif ($imageInfo['mime'] == 'image/jpeg') {
            $imageLayer = imagecreatefromjpeg($imagePath);
        } elseif ($imageInfo['mime'] == 'image/png') {
            $imageLayer = imagecreatefrompng($imagePath);
        }

        $filename = "{$options['targetPath']}/{$options['name']}.webp";
        $filePath = public_path("storage/{$filename}");
        imagewebp($imageLayer, $filePath, $options['quality'] ?? 50);

        if (!isset($options['duplicate']) || !$options['duplicate']) {
            unlink($options['original_uploaded_file']);
            $files = $filename;
        } else {
            $filenameOriginal = "{$options['targetPath']}/{$options['name']}.jpg";
            $filePathOriginal = public_path("storage/{$filenameOriginal}");
            imagejpeg($imageLayer, $filePathOriginal, $options['quality'] ?? 50);

            $files = [
                $filename,
                $filenameOriginal,
            ];
        }

        return [
            'files' => $files,
        ];
    }

    public static function getFileInfo(string $imageTarget)
    {
        return getimagesize($imageTarget);
    }
}