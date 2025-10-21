<?php

namespace App\Helper;

use getID3;
use Illuminate\Support\Str;

class Helper
{
    public static function uploadImage($file, $folder)
    {
        if (! $file->isValid()) {
            return null;
        }

        $uniqueId  = uniqid();
        $extension = $file->getClientOriginalExtension();
        $imageName = Str::slug(time() . '-' . $uniqueId) . '.' . $extension;
        $path      = public_path('uploads/' . $folder);

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $imageName);

        return 'uploads/' . $folder . '/' . $imageName;
    }

    public static function deleteImage($imageUrl)
    {
        if (! $imageUrl) {
            return false;
        }
        $filePath = public_path($imageUrl);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public static function deleteAvatar($filePath)
    {
        if (! $filePath) {
            return false;
        }

        $relativePath = str_replace(asset('/'), '', $filePath);
        $fullPath     = public_path($relativePath);

        if (file_exists($fullPath)) {
            unlink($fullPath);
            return true;
        }

        return false;
    }



    public static function getVideoDurationFormatted($relativePath)
    {
        $absolutePath = storage_path('app/public/' . $relativePath);

        $getID3 = new getID3();
        $info = $getID3->analyze($absolutePath);

        if (!isset($info['playtime_seconds'])) {
            return null;
        }

        return gmdate("H:i:s", (int)$info['playtime_seconds']);
    }

    public static function formatNumberShort($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        } else {
            return (string) $number;
        }
    }

    
}
