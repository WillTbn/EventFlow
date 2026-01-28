<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreImageVariants
{
    /**
     * Store the original image and generate medium and thumb variants.
     *
     * @return array{original: string, medium: string, thumb: string}
     */
    public function handle(UploadedFile $file, string $directory, string $baseName): array
    {
        $disk = Storage::disk('public');
        $safeBaseName = Str::slug($baseName) . '-' . Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $originalPath = $disk->putFileAs($directory, $file, $safeBaseName . '.' . $extension);

        $imageResource = $this->createImageResource($file->getPathname());

        $mediumPath = $this->storeResizedVariant(
            $disk,
            $imageResource,
            1200,
            1200,
            $directory,
            $safeBaseName . '-medium'
        );

        $thumbPath = $this->storeResizedVariant(
            $disk,
            $imageResource,
            400,
            400,
            $directory,
            $safeBaseName . '-thumb'
        );

        imagedestroy($imageResource);

        return [
            'original' => $originalPath,
            'medium' => $mediumPath,
            'thumb' => $thumbPath,
        ];
    }

    private function createImageResource(string $path): \GdImage
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read uploaded image contents.');
        }

        $imageResource = imagecreatefromstring($contents);

        if ($imageResource === false) {
            throw new RuntimeException('Unable to create image from uploaded contents.');
        }

        return $imageResource;
    }

    private function storeResizedVariant(
        \Illuminate\Filesystem\FilesystemAdapter $disk,
        \GdImage $imageResource,
        int $maxWidth,
        int $maxHeight,
        string $directory,
        string $fileName
    ): string {
        $originalWidth = imagesx($imageResource);
        $originalHeight = imagesy($imageResource);
        $widthScale = $maxWidth / $originalWidth;
        $heightScale = $maxHeight / $originalHeight;
        $scale = min($widthScale, $heightScale, 1);
        $targetWidth = (int) round($originalWidth * $scale);
        $targetHeight = (int) round($originalHeight * $scale);

        $resizedResource = imagecreatetruecolor($targetWidth, $targetHeight);
        $backgroundColor = imagecolorallocate($resizedResource, 255, 255, 255);
        imagefill($resizedResource, 0, 0, $backgroundColor);

        imagecopyresampled(
            $resizedResource,
            $imageResource,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $originalWidth,
            $originalHeight
        );

        ob_start();
        imagejpeg($resizedResource, null, 90);
        $imageBinary = ob_get_clean();
        imagedestroy($resizedResource);

        if ($imageBinary === false) {
            throw new RuntimeException('Unable to generate resized image.');
        }

        $path = $directory . '/' . $fileName . '.jpg';
        $disk->put($path, $imageBinary);

        return $path;
    }
}
