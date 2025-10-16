<?php

namespace App\Traits;

use App\helpers\imageCompressing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadTrait
{
 
    public function remove(string $file) : void
    {
        if ($this->exist($file)) {
        Storage::disk('public')->delete($file);
        }
    }

    public function exist(string $file) : bool
    {
        return Storage::disk('public')->exists($file);
    }

    public function upload(string $disk, UploadedFile $file, bool $originalName = false): string
    {
        if (!$this->exist($disk)) {
            Storage::makeDirectory($disk);
        }

        if ($originalName) {
            return $file->storageAs($disk, $this->originalName($file));
        }

        return Storage::put($disk, $file);
    }

    public function originalName(UploadedFile $file): string
    {
        return $file->getClientOriginalName();
    }

    public function originalExtension(UploadedFile $file): string
    {
        return $file->getClientOriginalExtension();
    }

    public function folderStorage(String $folderNameEnum, String $folderName)
    {
        $destinationPath = $folderName . '/' . $folderNameEnum;
        if(!file_exists(public_path('storage/' . $destinationPath))) {
            mkdir(public_path('storage/' . $destinationPath), 0777, true);
        }

        return $destinationPath;
    }

    public function compressImage($fileName, $imagePath,$storePath, array $options = []): mixed
    {
        $storedImage = ImageCompressing::process($fileName, $imagePath, $storePath, $options)->toArray();
        return $storedImage['files'];
    }
}