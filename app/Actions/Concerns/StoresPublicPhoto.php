<?php

namespace App\Actions\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

trait StoresPublicPhoto
{
    protected function storePhoto(?UploadedFile $photo, ?string $currentPath, string $directory): ?string
    {
        if (! $photo instanceof UploadedFile) {
            return $currentPath;
        }

        if (is_string($currentPath) && $currentPath !== '') {
            Storage::disk('public')->delete($currentPath);
        }

        $path = $photo->store($directory, 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The photo could not be stored.');
        }

        return $path;
    }
}
