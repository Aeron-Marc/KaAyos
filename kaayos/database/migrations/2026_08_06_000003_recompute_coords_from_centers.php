<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
    }

    private function pointFor(string $barangay, int $seed): array
    {
        $centers = [
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

        if (!isset($centers[$barangay])) {
            $barangay = 'Luna';
        }

        [$lat, $lng] = $centers[$barangay];

        mt_srand($seed);
        $jitter = 0.0005;
        $latJitter = (mt_rand() / mt_getrandmax() * 2 - 1) * $jitter;
        $lngJitter = (mt_rand() / mt_getrandmax() * 2 - 1) * $jitter;

        return [round($lat + $latJitter, 4), round($lng + $lngJitter, 4)];
    }
};
