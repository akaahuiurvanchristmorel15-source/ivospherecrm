<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Miniatures des photos d'articles (320 px) pour les listes : afficher les photos d'origine
 * (souvent 4000 px) dans le stock ou la caisse saturait la mémoire des téléphones.
 */
class ProductThumbnailService
{
    public const MAX_DIMENSION = 320;

    public const DIRECTORY = 'products/thumbs';

    public static function thumbnailPathFor(string $imagePath): string
    {
        return self::DIRECTORY.'/'.pathinfo($imagePath, PATHINFO_FILENAME).'.jpg';
    }

    /**
     * Crée (ou recrée) la miniature d'une image du disque public. Renvoie son chemin, ou null si
     * l'image est illisible ou si l'extension GD n'est pas disponible.
     */
    public static function generate(string $imagePath): ?string
    {
        $disk = Storage::disk('public');

        if (! function_exists('imagecreatefromstring') || ! $disk->exists($imagePath)) {
            return null;
        }

        try {
            $source = @imagecreatefromstring($disk->get($imagePath));
            if ($source === false) {
                return null;
            }

            $source = self::applyExifOrientation($source, $disk->path($imagePath));

            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min(1, self::MAX_DIMENSION / max($width, $height));
            $thumbWidth = max(1, (int) round($width * $scale));
            $thumbHeight = max(1, (int) round($height * $scale));

            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagefill($thumbnail, 0, 0, imagecolorallocate($thumbnail, 255, 255, 255));
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
            imagedestroy($source);

            ob_start();
            imagejpeg($thumbnail, null, 80);
            $contents = ob_get_clean();
            imagedestroy($thumbnail);

            $thumbnailPath = self::thumbnailPathFor($imagePath);
            $disk->put($thumbnailPath, $contents);

            return $thumbnailPath;
        } catch (Throwable $exception) {
            Log::warning("Miniature impossible pour {$imagePath} : {$exception->getMessage()}");

            return null;
        }
    }

    public static function delete(string $imagePath): void
    {
        Storage::disk('public')->delete(self::thumbnailPathFor($imagePath));
    }

    /**
     * Les photos de téléphone sont souvent stockées couchées avec une orientation EXIF.
     */
    private static function applyExifOrientation(\GdImage $image, string $absolutePath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($absolutePath)['Orientation'] ?? 1);
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }
        imagedestroy($image);

        return $rotated;
    }
}
