<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')
            ->where('role', 'worker')
            ->whereNotNull('barangay')
            ->get(['id', 'barangay']);

        foreach ($users as $user) {
            $coords = $this->pointFor($user->barangay, $user->id);
            DB::table('worker_profiles')
                ->where('user_id', $user->id)
                ->update([
                    'current_latitude'  => $coords[0],
                    'current_longitude' => $coords[1],
                ]);
        }
    }

    public function down(): void
    {
        // No-op — stale coords are overwritten, not restorable.
    }

    protected function pointFor(string $barangay, int $seed): array
    {
        $boxes = [
            'Acle'        => ['lat' => [13.9851, 14.0251], 'lng' => [120.7277, 120.7677]],
            'Bayudbud'    => ['lat' => [14.0355, 14.0755], 'lng' => [120.7163, 120.7563]],
            'Bolbok'      => ['lat' => [14.0015, 14.0415], 'lng' => [120.7382, 120.7782]],
            'Burgos'      => ['lat' => [14.0124, 14.0200], 'lng' => [120.7291, 120.7310]],
            'Dalima'      => ['lat' => [14.0214, 14.0463], 'lng' => [120.6843, 120.7056]],
            'Dao'         => ['lat' => [13.9796, 14.0196], 'lng' => [120.7347, 120.7747]],
            'Guinhawa'    => ['lat' => [13.9623, 14.0023], 'lng' => [120.7068, 120.7468]],
            'Lumbangan'   => ['lat' => [14.0045, 14.0445], 'lng' => [120.6950, 120.7350]],
            'Luna'        => ['lat' => [14.0110, 14.0273], 'lng' => [120.7303, 120.7402]],
            'Luntal'      => ['lat' => [14.0114, 14.0514], 'lng' => [120.6929, 120.7329]],
            'Magahis'     => ['lat' => [14.0229, 14.0629], 'lng' => [120.7332, 120.7732]],
            'Malibu'      => ['lat' => [13.9756, 14.0156], 'lng' => [120.6858, 120.7258]],
            'Mataywanac'  => ['lat' => [14.0194, 14.0594], 'lng' => [120.7193, 120.7593]],
            'Palincaro'   => ['lat' => [13.9896, 14.0296], 'lng' => [120.6845, 120.7245]],
            'Putol'       => ['lat' => [13.9730, 14.0130], 'lng' => [120.7081, 120.7481]],
            'Rillo'       => ['lat' => [14.0102, 14.0224], 'lng' => [120.7225, 120.7291]],
            'Rizal'       => ['lat' => [14.0124, 14.0250], 'lng' => [120.7272, 120.7305]],
            'Sabang'      => ['lat' => [14.0376, 14.0776], 'lng' => [120.6880, 120.7280]],
            'San Jose'    => ['lat' => [14.0036, 14.0436], 'lng' => [120.7620, 120.8020]],
            'Talon'       => ['lat' => [13.9979, 14.0379], 'lng' => [120.6786, 120.7186]],
            'Toong'       => ['lat' => [14.0491, 14.0492], 'lng' => [120.7908, 120.7909]],
            'Tuyon-Tuyon' => ['lat' => [13.9844, 14.0244], 'lng' => [120.7097, 120.7497]],
        ];

        if (!isset($boxes[$barangay])) {
            $barangay = 'Luna';
        }
        $box = $boxes[$barangay];
        mt_srand($seed);
        $lat = $box['lat'][0] + mt_rand() / mt_getrandmax() * ($box['lat'][1] - $box['lat'][0]);
        $lng = $box['lng'][0] + mt_rand() / mt_getrandmax() * ($box['lng'][1] - $box['lng'][0]);
        return [round($lat, 4), round($lng, 4)];
    }
};
