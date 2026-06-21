<?php

namespace App\Support;

class ClientSampleData
{
    public static function categories(): array
    {
        return [
            ['id' => 'plumbing', 'name' => 'Plumbing', 'icon' => 'fa-wrench', 'color' => 'ic-b'],
            ['id' => 'electrical', 'name' => 'Electrical', 'icon' => 'fa-bolt', 'color' => 'ic-y'],
            ['id' => 'cleaning', 'name' => 'Cleaning', 'icon' => 'fa-broom', 'color' => 'ic-g'],
            ['id' => 'carpentry', 'name' => 'Carpentry', 'icon' => 'fa-screwdriver-wrench', 'color' => 'ic-o'],
            ['id' => 'painting', 'name' => 'Painting', 'icon' => 'fa-paint-roller', 'color' => 'ic-p'],
            ['id' => 'aircon', 'name' => 'Aircon', 'icon' => 'fa-snowflake', 'color' => 'ic-t'],
        ];
    }

    public static function workers(): array
    {
        return [
            [
                'id' => 'w1',
                'name' => 'Juan Dela Cruz',
                'category' => 'Plumbing',
                'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80',
                'initials' => 'JD',
                'rating' => 4.9,
                'reviews' => 124,
                'distance' => '1.2 km',
                'price' => 450,
                'verified' => true,
                'skills' => ['Pipe Fixing', 'Water Heater', 'Drain Unblocking'],
            ],
            [
                'id' => 'w2',
                'name' => 'Elena Santos',
                'category' => 'Cleaning',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80',
                'initials' => 'ES',
                'rating' => 4.8,
                'reviews' => 89,
                'distance' => '3.5 km',
                'price' => 300,
                'verified' => true,
                'skills' => ['Deep Cleaning', 'Move-in/Move-out', 'Window Washing'],
            ],
            [
                'id' => 'w3',
                'name' => 'Marco Reyes',
                'category' => 'Electrical',
                'avatar' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=400&q=80',
                'initials' => 'MR',
                'rating' => 5.0,
                'reviews' => 210,
                'distance' => '0.8 km',
                'price' => 600,
                'verified' => true,
                'skills' => ['Wiring', 'Panel Upgrades', 'Lighting Setup'],
            ],
            [
                'id' => 'w4',
                'name' => 'Sofia Gomez',
                'category' => 'Painting',
                'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&q=80',
                'initials' => 'SG',
                'rating' => 4.7,
                'reviews' => 56,
                'distance' => '5.1 km',
                'price' => 400,
                'verified' => false,
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'],
            ],
        ];
    }

    public static function bookings(): array
    {
        return [
            [
                'id' => 'b1',
                'worker' => 'Juan Dela Cruz',
                'service' => 'Plumbing — Leak Fix',
                'date' => 'Jun 24, 2026 · 10:00 AM',
                'status' => 'Active',
                'price' => 450,
            ],
            [
                'id' => 'b2',
                'worker' => 'Elena Santos',
                'service' => 'Deep Cleaning',
                'date' => 'Jun 20, 2026 · 1:00 PM',
                'status' => 'Done',
                'price' => 1200,
            ],
            [
                'id' => 'b3',
                'worker' => 'Marco Reyes',
                'service' => 'Electrical Inspection',
                'date' => 'Jun 28, 2026 · 9:00 AM',
                'status' => 'Pending',
                'price' => 600,
            ],
        ];
    }

    public static function notifications(): array
    {
        return [
            [
                'type' => 'booking',
                'title' => 'Booking confirmed',
                'desc' => 'Juan Dela Cruz accepted your plumbing request for Jun 24.',
                'time' => '2 hours ago',
                'unread' => true,
            ],
            [
                'type' => 'message',
                'title' => 'New message from Marco Reyes',
                'desc' => '"I can come by tomorrow morning if that works for you."',
                'time' => '5 hours ago',
                'unread' => true,
            ],
            [
                'type' => 'system',
                'title' => 'Welcome to KaAyos!',
                'desc' => 'Your account is set up. Start by searching for a verified trabahador near you.',
                'time' => '1 day ago',
                'unread' => false,
            ],
            [
                'type' => 'review',
                'title' => 'Rate your recent job',
                'desc' => 'How was your deep cleaning with Elena Santos? Leave a review to help others.',
                'time' => '2 days ago',
                'unread' => false,
            ],
            [
                'type' => 'booking',
                'title' => 'Booking reminder',
                'desc' => 'Your electrical inspection with Marco Reyes is scheduled for Jun 28.',
                'time' => '3 days ago',
                'unread' => false,
            ],
        ];
    }

    public static function conversations(): array
    {
        return [
            [
                'id' => 'c1',
                'name' => 'Juan Dela Cruz',
                'initials' => 'JD',
                'preview' => 'Sure, I can bring my tools tomorrow.',
                'time' => '2:14 PM',
                'active' => true,
                'messages' => [
                    ['from' => 'them', 'text' => 'Hi! I saw your booking request for the leaking pipe.'],
                    ['from' => 'me', 'text' => 'Yes, it\'s under the kitchen sink. Can you come tomorrow?'],
                    ['from' => 'them', 'text' => 'Sure, I can bring my tools tomorrow.'],
                ],
            ],
            [
                'id' => 'c2',
                'name' => 'Marco Reyes',
                'initials' => 'MR',
                'preview' => 'I can come by tomorrow morning.',
                'time' => '11:30 AM',
                'active' => false,
                'messages' => [],
            ],
            [
                'id' => 'c3',
                'name' => 'Elena Santos',
                'initials' => 'ES',
                'preview' => 'Thank you for the review!',
                'time' => 'Yesterday',
                'active' => false,
                'messages' => [],
            ],
        ];
    }

    public static function reviews(): array
    {
        return [
            'pending' => [
                [
                    'worker' => 'Elena Santos',
                    'service' => 'Deep Cleaning',
                    'date' => 'Jun 20, 2026',
                ],
            ],
            'past' => [
                [
                    'worker' => 'Carlos Mendez',
                    'service' => 'Cabinet Repair',
                    'date' => 'May 15, 2026',
                    'rating' => 5,
                    'comment' => 'Excellent work, very professional and on time.',
                ],
            ],
        ];
    }

    public static function stats(): array
    {
        return [
            ['label' => 'Active Bookings', 'value' => 1, 'icon' => 'fa-calendar-check', 'accent' => true],
            ['label' => 'Completed Jobs', 'value' => 4, 'icon' => 'fa-circle-check'],
            ['label' => 'Unread Messages', 'value' => 2, 'icon' => 'fa-comment-dots'],
            ['label' => 'Pending Reviews', 'value' => 1, 'icon' => 'fa-star'],
        ];
    }
}
