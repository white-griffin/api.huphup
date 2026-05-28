<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{

    public function upload(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public'
    ): string {
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($directory, $name, $disk);
    }

    public function delete(?string $path, string $disk = 'public'): bool
    {
        return $path && Storage::disk($disk)->delete($path);
    }

    public function url(?string $path, string $disk = 'public'): ?string
    {
        return $path ? Storage::disk($disk)->url($path) : null;
    }

    public function replace(
        ?string $oldPath,
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public'
    ): string {
        $this->delete($oldPath, $disk);

        return $this->upload($file, $directory, $disk);
    }

}
