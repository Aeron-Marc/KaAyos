<?php

namespace App\Support;

class WorkerDocuments
{
    public static function types(): array
    {
        return [
            [
                'name'        => 'Government-Issued ID',
                'description' => 'Valid Philippine National ID (PhilID/ePhilID), UMID, Passport, or Driver\'s License. Used to verify your legal name, face, and age.',
                'icon'        => 'fa-id-card',
            ],
            [
                'name'        => 'National Police or NBI Clearance',
                'description' => 'Recent National Police Clearance or NBI Clearance issued within the last 6 months. Helps prove you have no criminal records before entering private homes.',
                'icon'        => 'fa-shield-halved',
            ],
            [
                'name'        => 'Barangay Clearance',
                'description' => 'Recent Barangay Clearance or Certificate of Residency from your local barangay. Serves as official proof of address.',
                'icon'        => 'fa-building-columns',
            ],
            [
                'name'        => 'Proof of Competency',
                'description' => 'For certified workers: TESDA National Certificate (NC I/NC II) or Certificate of Competency (COC). For uncertified workers: Project portfolio (3-5 before/after photos) or a signed Character Reference Voucher.',
                'icon'        => 'fa-certificate',
            ],
        ];
    }
}
