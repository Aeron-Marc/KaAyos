<?php

namespace App\Support;

class TuyRoadNodes
{
    /**
     * Key municipal road junctions in Tuy, Batangas.
     */
    public const JUNCTIONS = [
        'poblacion_center' => [14.0245, 120.7300], // Municipal Hall / Town Plaza
        'palico_junction'  => [14.0538, 120.7180], // Palico - Nasugbu Highway junction
        'obispo_junction'  => [14.0320, 120.7215], // West road fork towards Obispo / Malibu
        'putol_fork'       => [14.0150, 120.7420], // East artery towards Putol / Sabang
        'sabang_bridge'    => [14.0080, 120.7550], // South-East bridge towards Sabang / Bayudbud
        'guinhawa_highway' => [14.0120, 120.7250], // South Highway towards Guinhawa / Balayan
    ];

    /**
     * Map each barangay to its primary access road junction.
     */
    public const BARANGAY_ACCESS = [
        'Acle'           => 'palico_junction',
        'Bayudbud'       => 'sabang_bridge',
        'Bolboc'         => 'palico_junction',
        'Burgos'         => 'poblacion_center',
        'Dalima'         => 'obispo_junction',
        'Dao'            => 'palico_junction',
        'Guinhawa'       => 'guinhawa_highway',
        'Lumbangan'      => 'poblacion_center',
        'Luntal'         => 'poblacion_center',
        'Magallanes'     => 'obispo_junction',
        'Malibu'         => 'obispo_junction',
        'Mataywanac'     => 'palico_junction',
        'Palico'         => 'palico_junction',
        'Ricarte'        => 'poblacion_center',
        'Rizal'          => 'poblacion_center',
        'Sabang'         => 'sabang_bridge',
        'San Jose'       => 'obispo_junction',
        'San Nicolas'    => 'obispo_junction',
        'Talon'          => 'putol_fork',
        'Toong'          => 'putol_fork',
        'Tuyon-tuyon'    => 'poblacion_center',
        'Luna'           => 'poblacion_center',
        'Putol'          => 'putol_fork',
    ];

    /**
     * Build offline road-following waypoints between two barangays or points.
     * Connects origin -> origin junction -> poblacion -> destination junction -> destination.
     *
     * @return array<array{float, float}>
     */
    public static function buildFallbackCorridor(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $points = [[$lat1, $lng1]];

        // Find nearest access junction for origin and destination
        $originJunction = self::findNearestJunction($lat1, $lng1);
        $destJunction = self::findNearestJunction($lat2, $lng2);

        if ($originJunction !== $destJunction) {
            $points[] = self::JUNCTIONS[$originJunction];

            // If crossing across town (neither is poblacion), route via Poblacion Center
            if ($originJunction !== 'poblacion_center' && $destJunction !== 'poblacion_center') {
                $points[] = self::JUNCTIONS['poblacion_center'];
            }

            $points[] = self::JUNCTIONS[$destJunction];
        }

        $points[] = [$lat2, $lng2];

        // Deduplicate consecutive identical points
        $unique = [];
        foreach ($points as $p) {
            if (empty($unique) || (abs($unique[count($unique) - 1][0] - $p[0]) > 0.0001 || abs($unique[count($unique) - 1][1] - $p[1]) > 0.0001)) {
                $unique[] = $p;
            }
        }

        return $unique;
    }

    protected static function findNearestJunction(float $lat, float $lng): string
    {
        $minDist = PHP_FLOAT_MAX;
        $bestKey = 'poblacion_center';

        foreach (self::JUNCTIONS as $key => [$jLat, $jLng]) {
            $dist = TuyBarangays::distanceKm($lat, $lng, $jLat, $jLng);
            if ($dist < $minDist) {
                $minDist = $dist;
                $bestKey = $key;
            }
        }

        return $bestKey;
    }
}

