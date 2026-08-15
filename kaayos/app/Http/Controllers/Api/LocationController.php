<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\TuyBarangays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private const REGION  = 'Calabarzon';
    private const PROVINCE = 'Batangas';
    private const MUNICIPALITY = 'Tuy';

    public function reverseGeocode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $data['latitude'];
        $lng = (float) $data['longitude'];

        $barangay = TuyBarangays::barangayFor($lat, $lng);

        return response()->json([
            'barangay'          => $barangay,
            'region'            => self::REGION,
            'province'          => self::PROVINCE,
            'city_municipality' => self::MUNICIPALITY,
            'latitude'          => $lat,
            'longitude'         => $lng,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'barangay'          => ['required', 'string', 'max:255'],
            'street_address'    => ['nullable', 'string', 'max:255'],
            'region'            => ['nullable', 'string', 'max:120'],
            'province'          => ['nullable', 'string', 'max:120'],
            'city_municipality' => ['nullable', 'string', 'max:120'],
            'location_source'   => ['required', 'string', 'in:gps,manual'],
        ]);

        if (!TuyBarangays::isValidBarangay($data['barangay'])) {
            return response()->json([
                'message' => 'The selected barangay is not valid for this area.',
                'errors'  => ['barangay' => ['The selected barangay is not valid for this area.']],
            ], 422);
        }

        $user = $request->user();

        $latitude  = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if ($latitude === null || $longitude === null) {
            [$latitude, $longitude] = TuyBarangays::pointFor($data['barangay'], (int) $user->id);
        }

        $user->update([
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'barangay'          => $data['barangay'],
            'street_address'    => $data['street_address'] ?? null,
            'region'            => $data['region'] ?? self::REGION,
            'province'          => $data['province'] ?? self::PROVINCE,
            'city_municipality' => $data['city_municipality'] ?? self::MUNICIPALITY,
            'city'              => $data['city_municipality'] ?? self::MUNICIPALITY,
            'location_source'   => $data['location_source'],
        ]);

        if ($user->isWorker()) {
            $profile = $user->workerProfile;
            if ($profile) {
                $profile->update([
                    'current_latitude'        => $latitude,
                    'current_longitude'       => $longitude,
                    'location_is_approximate' => false,
                ]);
            }
        }

        return response()->json([
            'message'           => 'Location saved.',
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'barangay'          => $data['barangay'],
            'region'            => $data['region'] ?? self::REGION,
            'province'          => $data['province'] ?? self::PROVINCE,
            'city_municipality' => $data['city_municipality'] ?? self::MUNICIPALITY,
            'street_address'    => $data['street_address'] ?? null,
            'location_source'   => $data['location_source'],
            'residence'         => $user->residence,
        ]);
    }
}
