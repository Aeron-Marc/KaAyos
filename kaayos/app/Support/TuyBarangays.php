<?php

namespace App\Support;

class TuyBarangays
{
    private const CENTERS = [
        'Acle'        => [14.0051, 120.7477],
        'Bayudbud'    => [14.0555, 120.7363],
        'Bolbok'      => [14.0215, 120.7582],
        'Burgos'      => [14.0162, 120.7301],
        'Dalima'      => [14.0339, 120.6950],
        'Dao'         => [13.9996, 120.7547],
        'Guinhawa'    => [13.9823, 120.7268],
        'Lumbangan'   => [14.0245, 120.7150],
        'Luna'        => [14.0192, 120.7353],
        'Luntal'      => [14.0314, 120.7129],
        'Magahis'     => [14.0429, 120.7532],
        'Malibu'      => [13.9956, 120.7058],
        'Mataywanac'  => [14.0394, 120.7393],
        'Palincaro'   => [14.0096, 120.7045],
        'Putol'       => [13.9930, 120.7281],
        'Rillo'       => [14.0163, 120.7258],
        'Rizal'       => [14.0187, 120.7289],
        'Sabang'      => [14.0576, 120.7080],
        'San Jose'    => [14.0236, 120.7820],
        'Talon'       => [14.0179, 120.6986],
        'Toong'       => [14.0492, 120.7909],
        'Tuyon-Tuyon' => [14.0044, 120.7297],
    ];

    private const JITTER_RADIUS = 0.0005;

    public static function residenceFor(int $userId): string
    {
        $names = array_keys(self::CENTERS);
        return $names[crc32((string) $userId) % count($names)];
    }

    public static function pointFor(string $barangay, int $seed): array
    {
        if (!isset(self::CENTERS[$barangay])) {
            $barangay = 'Luna';
        }

        [$lat, $lng] = self::CENTERS[$barangay];

        mt_srand($seed);
        $latJitter = (mt_rand() / mt_getrandmax() * 2 - 1) * self::JITTER_RADIUS;
        $lngJitter = (mt_rand() / mt_getrandmax() * 2 - 1) * self::JITTER_RADIUS;

        return [
            round($lat + $latJitter, 4),
            round($lng + $lngJitter, 4),
        ];
    }

    public static function pointForStatic(string $barangay): array
    {
        if (!isset(self::CENTERS[$barangay])) {
            $barangay = 'Luna';
        }

        [$lat, $lng] = self::CENTERS[$barangay];

        return [
            round($lat, 6),
            round($lng, 6),
        ];
    }

    public static function allBarangays(): array
    {
        return array_keys(self::CENTERS);
    }

    public static function isValidBarangay(?string $barangay): bool
    {
        return $barangay !== null && isset(self::CENTERS[$barangay]);
    }

    public static function barangayFor(float $lat, float $lng): string
    {
        $best = null;
        $bestDist = PHP_FLOAT_MAX;

        foreach (self::CENTERS as $name => $center) {
            $dist = self::haversine($lat, $lng, $center[0], $center[1]);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $name;
            }
        }

        return $best ?? 'Luna';
    }

    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
