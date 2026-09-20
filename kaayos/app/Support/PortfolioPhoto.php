<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PortfolioPhoto
{
    public static function url(?string $photoPath, int $id): string
    {
        if ($photoPath && Storage::exists($photoPath)) {
            return Storage::url($photoPath);
        }

        return '/images/stock/work-' . (($id % 6) + 1) . '.jpg';
    }
}