<?php

namespace App\Support;

class WorkerSampleData
{
    public static function stats(): array
    {
        return [
            ['label' => 'Earnings This Week', 'value' => '₱2,450', 'icon' => 'fa-coins', 'accent' => true],
            ['label' => 'Active Jobs', 'value' => 2, 'icon' => 'fa-briefcase'],
            ['label' => 'Rating', 'value' => '4.8 ★', 'icon' => 'fa-star'],
            ['label' => 'Completed Jobs', 'value' => 47, 'icon' => 'fa-circle-check'],
        ];
    }

    public static function jobRequests(): array
    {
        return [
            [
                'id' => 'jr1',
                'client' => 'Maria Santos',
                'service' => 'Plumbing — Leak Fix',
                'date' => 'Jun 26, 2026 · 10:00 AM',
                'location' => 'Brgy. 3, Tuy, Batangas',
                'status' => 'Pending',
                'price' => 450,
            ],
            [
                'id' => 'jr2',
                'client' => 'John Villanueva',
                'service' => 'Electrical Inspection',
                'date' => 'Jun 28, 2026 · 2:00 PM',
                'location' => 'Brgy. 5, Tuy, Batangas',
                'status' => 'Pending',
                'price' => 600,
            ],
            [
                'id' => 'jr3',
                'client' => 'Ana Lopez',
                'service' => 'Deep Cleaning',
                'date' => 'Jun 25, 2026 · 9:00 AM',
                'location' => 'Brgy. 2, Tuy, Batangas',
                'status' => 'Accepted',
                'price' => 300,
            ],
            [
                'id' => 'jr4',
                'client' => 'Pedro Reyes',
                'service' => 'Painting — Room Repaint',
                'date' => 'Jun 22, 2026 · 1:00 PM',
                'location' => 'Brgy. 1, Tuy, Batangas',
                'status' => 'Completed',
                'price' => 400,
            ],
        ];
    }

    public static function schedule(): array
    {
        return [
            [
                'id' => 's1',
                'client' => 'Ana Lopez',
                'service' => 'Deep Cleaning',
                'date' => 'Jun 25, 2026',
                'time' => '9:00 AM — 12:00 PM',
                'location' => 'Brgy. 2, Tuy, Batangas',
                'status' => 'Confirmed',
            ],
            [
                'id' => 's2',
                'client' => 'Maria Santos',
                'service' => 'Plumbing — Leak Fix',
                'date' => 'Jun 26, 2026',
                'time' => '10:00 AM — 11:30 AM',
                'location' => 'Brgy. 3, Tuy, Batangas',
                'status' => 'Pending',
            ],
            [
                'id' => 's3',
                'client' => 'John Villanueva',
                'service' => 'Electrical Inspection',
                'date' => 'Jun 28, 2026',
                'time' => '2:00 PM — 4:00 PM',
                'location' => 'Brgy. 5, Tuy, Batangas',
                'status' => 'Confirmed',
            ],
        ];
    }

    public static function conversations(): array
    {
        return [
            [
                'id' => 'wc1',
                'name' => 'Ana Lopez',
                'initials' => 'AL',
                'preview' => 'See you tomorrow at 9 AM!',
                'time' => '1:45 PM',
                'active' => true,
                'messages' => [
                    ['from' => 'them', 'text' => 'Hi! I\'d like to confirm my booking for deep cleaning tomorrow.'],
                    ['from' => 'me', 'text' => 'Sure, I\'ll be there at 9 AM.'],
                    ['from' => 'them', 'text' => 'See you tomorrow at 9 AM!'],
                ],
            ],
            [
                'id' => 'wc2',
                'name' => 'Maria Santos',
                'initials' => 'MS',
                'preview' => 'Thanks! Please bring your tools.',
                'time' => '11:30 AM',
                'active' => false,
                'messages' => [],
            ],
            [
                'id' => 'wc3',
                'name' => 'John Villanueva',
                'initials' => 'JV',
                'preview' => 'Can you do it on Saturday instead?',
                'time' => 'Yesterday',
                'active' => false,
                'messages' => [],
            ],
        ];
    }

    public static function notifications(): array
    {
        return [
            [
                'type' => 'booking',
                'title' => 'New booking request',
                'desc' => 'Maria Santos sent you a plumbing request for Jun 26.',
                'time' => '1 hour ago',
                'unread' => true,
            ],
            [
                'type' => 'message',
                'title' => 'New message from Ana Lopez',
                'desc' => '"See you tomorrow at 9 AM!"',
                'time' => '2 hours ago',
                'unread' => true,
            ],
            [
                'type' => 'system',
                'title' => 'Profile verification update',
                'desc' => 'Your NBI Clearance is now under review. We\'ll notify you once verified.',
                'time' => '1 day ago',
                'unread' => false,
            ],
            [
                'type' => 'review',
                'title' => 'New review received',
                'desc' => 'Pedro Reyes left you a 5-star review. Great job!',
                'time' => '2 days ago',
                'unread' => false,
            ],
            [
                'type' => 'earnings',
                'title' => 'Payout completed',
                'desc' => '₱2,450 has been deposited to your linked account.',
                'time' => '3 days ago',
                'unread' => false,
            ],
        ];
    }

    public static function earnings(): array
    {
        return [
            'total' => 68500,
            'this_month' => 12450,
            'pending_payout' => 3200,
            'avg_per_job' => 580,
            'payouts' => [
                [
                    'date' => 'Jun 22, 2026',
                    'client' => 'Pedro Reyes',
                    'job' => 'Painting — Room Repaint',
                    'amount' => 400,
                    'status' => 'Completed',
                ],
                [
                    'date' => 'Jun 20, 2026',
                    'client' => 'Elena Santos',
                    'job' => 'Electrical Repair',
                    'amount' => 650,
                    'status' => 'Completed',
                ],
                [
                    'date' => 'Jun 18, 2026',
                    'client' => 'Carlos Mendez',
                    'job' => 'Cabinet Installation',
                    'amount' => 1200,
                    'status' => 'Completed',
                ],
                [
                    'date' => 'Jun 15, 2026',
                    'client' => 'Ana Lopez',
                    'job' => 'Deep Cleaning',
                    'amount' => 300,
                    'status' => 'Pending',
                ],
                [
                    'date' => 'Jun 12, 2026',
                    'client' => 'Maria Santos',
                    'job' => 'Pipe Replacement',
                    'amount' => 850,
                    'status' => 'Completed',
                ],
            ],
        ];
    }

    public static function documents(): array
    {
        return [
            [
                'id' => 'doc1',
                'name' => 'Government-Issued ID',
                'description' => 'Valid PH driver\'s license, UMID, passport, or national ID.',
                'icon' => 'fa-id-card',
                'status' => 'Verified',
                'file' => 'id_uploaded.jpg',
            ],
            [
                'id' => 'doc2',
                'name' => 'NBI Clearance',
                'description' => 'Recent NBI clearance (issued within the last 6 months).',
                'icon' => 'fa-shield-halved',
                'status' => 'Pending',
                'file' => 'nbi_clearance.pdf',
            ],
            [
                'id' => 'doc3',
                'name' => 'Barangay Clearance',
                'description' => 'Barangay clearance or certificate of residency.',
                'icon' => 'fa-building-columns',
                'status' => 'Pending',
                'file' => null,
            ],
            [
                'id' => 'doc4',
                'name' => 'Certificate of Training',
                'description' => 'TESDA certificate or any relevant skills training certification.',
                'icon' => 'fa-certificate',
                'status' => 'Not Submitted',
                'file' => null,
            ],
            [
                'id' => 'doc5',
                'name' => 'Proof of Address',
                'description' => 'Recent utility bill or bank statement with your current address.',
                'icon' => 'fa-house-chimney',
                'status' => 'Verified',
                'file' => 'billing_statement.pdf',
            ],
        ];
    }
}
