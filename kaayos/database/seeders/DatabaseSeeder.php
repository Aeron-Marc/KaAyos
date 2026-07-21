<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ProviderService;
use App\Models\Booking;

use App\Models\Earning;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $platformFeePercent = config('kaayos.platform_fee_percent', 10);

        // ── Service Categories ──────────────────────────────────────

        $categories = [
            ['name' => 'Plumbing',  'slug' => 'plumbing',  'description' => 'Pipe repairs, water heater installation, drainage solutions', 'icon' => 'fa-wrench'],
            ['name' => 'Electrical','slug' => 'electrical','description' => 'Wiring, panel upgrades, lighting installation and repair',       'icon' => 'fa-bolt'],
            ['name' => 'Cleaning',  'slug' => 'cleaning',  'description' => 'Deep cleaning, move-in/move-out, window and floor care',       'icon' => 'fa-broom'],
            ['name' => 'Carpentry', 'slug' => 'carpentry', 'description' => 'Furniture assembly, cabinet installation, wood repairs',       'icon' => 'fa-screwdriver-wrench'],
            ['name' => 'Painting',  'slug' => 'painting',  'description' => 'Interior and exterior painting, cabinet refinishing',          'icon' => 'fa-paint-roller'],
            ['name' => 'Aircon',    'slug' => 'aircon',    'description' => 'AC cleaning, repair, and installation',                       'icon' => 'fa-snowflake'],
        ];

        foreach ($categories as $data) {
            ServiceCategory::create($data);
        }

        // ── Services ─────────────────────────────────────────────────

        $servicesData = [
            'plumbing' => [
                ['name' => 'Leak Repair',           'slug' => 'leak-repair',           'base_price' => 350],
                ['name' => 'Pipe Installation',     'slug' => 'pipe-installation',     'base_price' => 500],
                ['name' => 'Water Heater Service',  'slug' => 'water-heater-service',  'base_price' => 450],
            ],
            'electrical' => [
                ['name' => 'Electrical Inspection', 'slug' => 'electrical-inspection', 'base_price' => 400],
                ['name' => 'Wiring & Rewiring',     'slug' => 'wiring-rewiring',       'base_price' => 600],
                ['name' => 'Lighting Installation', 'slug' => 'lighting-installation', 'base_price' => 300],
            ],
            'cleaning' => [
                ['name' => 'Deep Cleaning',         'slug' => 'deep-cleaning',         'base_price' => 300],
                ['name' => 'Move-in/out Cleaning',  'slug' => 'move-in-out-cleaning',  'base_price' => 500],
                ['name' => 'Window Cleaning',       'slug' => 'window-cleaning',       'base_price' => 200],
            ],
            'carpentry' => [
                ['name' => 'Furniture Assembly',    'slug' => 'furniture-assembly',    'base_price' => 350],
                ['name' => 'Cabinet Installation',  'slug' => 'cabinet-installation',  'base_price' => 550],
                ['name' => 'Custom Shelving',       'slug' => 'custom-shelving',       'base_price' => 400],
            ],
            'painting' => [
                ['name' => 'Interior Painting',     'slug' => 'interior-painting',     'base_price' => 450],
                ['name' => 'Exterior Painting',     'slug' => 'exterior-painting',     'base_price' => 600],
                ['name' => 'Cabinet Refinishing',   'slug' => 'cabinet-refinishing',   'base_price' => 350],
            ],
            'aircon' => [
                ['name' => 'AC Cleaning',           'slug' => 'ac-cleaning',           'base_price' => 350],
                ['name' => 'AC Repair',             'slug' => 'ac-repair',             'base_price' => 500],
                ['name' => 'AC Installation',       'slug' => 'ac-installation',       'base_price' => 800],
            ],
        ];

        foreach ($servicesData as $catSlug => $services) {
            $category = ServiceCategory::where('slug', $catSlug)->first();
            foreach ($services as $data) {
                $category->services()->create($data);
            }
        }

        // ── Admin User ──────────────────────────────────────────────

        User::create([
            'first_name' => 'Admin',
            'last_name'  => 'KaAyos',
            'name'       => 'Admin KaAyos',
            'email'      => 'admin@kaayos.com',
            'password'   => $password,
            'role'       => 'admin',
            'city'       => 'Tuy, Batangas',
            'language'   => 'English',
            'email_verified_at' => now(),
        ]);

        // ── Client Users (all from Tuy) ─────────────────────────────

        $barangays = [
            'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
            'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
            'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
            'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
        ];

        $clients = [
            ['first_name' => 'Maria',  'last_name' => 'Santos',     'email' => 'maria@example.com'],
            ['first_name' => 'John',   'last_name' => 'Villanueva', 'email' => 'john@example.com'],
            ['first_name' => 'Ana',    'last_name' => 'Lopez',      'email' => 'ana@example.com'],
            ['first_name' => 'Carlos', 'last_name' => 'Mendoza',    'email' => 'carlos@example.com'],
            ['first_name' => 'Rosa',   'last_name' => 'Fernandez',  'email' => 'rosa@example.com'],
        ];

        $clientUsers = [];
        foreach ($clients as $i => $data) {
            $clientUsers[] = User::create([
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'name'              => $data['first_name'] . ' ' . $data['last_name'],
                'email'             => $data['email'],
                'password'          => $password,
                'role'              => 'client',
                'city'              => 'Tuy, Batangas',
                'language'          => 'English',
                'email_verified_at' => now(),
            ]);
        }

        // ── Worker Users & Profiles (all from Tuy, Batangas) ───────
        // Tuy centre: ~13.9581° N, 120.7278° E

        $workersData = [
            [
                'first_name' => 'Juan',     'last_name' => 'Dela Cruz',  'email' => 'juan@example.com',   'category' => 'Plumbing',
                'bio' => 'Expert plumber with over 8 years of experience in residential and commercial plumbing. Licensed and fully insured.',
                'skills' => ['Pipe Fixing', 'Water Heater', 'Drain Unblocking'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 450, 'years_exp' => 8, 'radius_km' => 15,
                'areas' => $barangays,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9581, 'lng' => 120.7278, 'verified' => true,  'rating' => 4.7,
            ],
            [
                'first_name' => 'Elena',    'last_name' => 'Santos',    'email' => 'elena@example.com',   'category' => 'Cleaning',
                'bio' => 'Professional cleaner dedicated to making homes spotless. Trained in eco-friendly cleaning methods.',
                'skills' => ['Deep Cleaning', 'Move-in/Move-out', 'Window Washing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 5, 'radius_km' => 10,
                'areas' => $barangays,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9550, 'lng' => 120.7300, 'verified' => true,  'rating' => 4.5,
            ],
            [
                'first_name' => 'Marco',    'last_name' => 'Reyes',     'email' => 'marco@example.com',   'category' => 'Electrical',
                'bio' => 'Licensed electrician specializing in residential wiring, panel upgrades, and smart home setup.',
                'skills' => ['Wiring', 'Panel Upgrades', 'Lighting Setup'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 600, 'years_exp' => 10, 'radius_km' => 20,
                'areas' => $barangays,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9600, 'lng' => 120.7250, 'verified' => true,  'rating' => 4.9,
            ],
            [
                'first_name' => 'Sofia',    'last_name' => 'Gomez',     'email' => 'sofia@example.com',   'category' => 'Painting',
                'bio' => 'Creative painter with a keen eye for detail. Transforms homes with quality finishes.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'],
                'languages' => ['Filipino'],
                'hourly_rate' => 400, 'years_exp' => 6, 'radius_km' => 12,
                'areas' => $barangays,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9520, 'lng' => 120.7200, 'verified' => true,  'rating' => 4.3,
            ],
            [
                'first_name' => 'Pedro',    'last_name' => 'Marcos',    'email' => 'pedro@example.com',    'category' => 'Carpentry',
                'bio' => 'Master carpenter with 12 years of experience. Specialises in custom furniture and cabinetry.',
                'skills' => ['Furniture Building', 'Cabinet Making', 'Wood Repair'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 500, 'years_exp' => 12, 'radius_km' => 18,
                'areas' => $barangays,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9560, 'lng' => 120.7350, 'verified' => true,  'rating' => 4.8,
            ],
            [
                'first_name' => 'Liza',     'last_name' => 'Cruz',      'email' => 'liza@example.com',     'category' => 'Cleaning',
                'bio' => 'Eco-friendly cleaning specialist. Uses non-toxic products for a safe, sparkling home.',
                'skills' => ['Eco Cleaning', 'Deep Cleaning', 'Organising'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 350, 'years_exp' => 4, 'radius_km' => 8,
                'areas' => $barangays,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9530, 'lng' => 120.7220, 'verified' => false, 'rating' => 4.1,
            ],
            [
                'first_name' => 'Ramon',    'last_name' => 'Villanueva', 'email' => 'ramon@example.com',   'category' => 'Aircon',
                'bio' => 'Certified HVAC technician. AC cleaning, repair, and installation for all brands.',
                'skills' => ['AC Cleaning', 'AC Repair', 'AC Installation'],
                'languages' => ['Filipino'],
                'hourly_rate' => 550, 'years_exp' => 7, 'radius_km' => 20,
                'areas' => $barangays,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 6 PM)',
                'lat' => 13.9620, 'lng' => 120.7300, 'verified' => true,  'rating' => 4.6,
            ],
            [
                'first_name' => 'Bella',    'last_name' => 'Torres',     'email' => 'bella@example.com',    'category' => 'Plumbing',
                'bio' => 'Skilled plumber offering fast and reliable service. Handles emergencies 24/7.',
                'skills' => ['Leak Repair', 'Pipe Installation', 'Water Heater'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 5, 'radius_km' => 12,
                'areas' => $barangays,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9500, 'lng' => 120.7280, 'verified' => true,  'rating' => 4.4,
            ],
            [
                'first_name' => 'Dante',    'last_name' => 'Alcantara',  'email' => 'dante@example.com',    'category' => 'Electrical',
                'bio' => 'Licensed master electrician. Handles complex wiring, generator setup, and automation.',
                'skills' => ['Wiring', 'Generator Setup', 'Home Automation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 650, 'years_exp' => 15, 'radius_km' => 25,
                'areas' => $barangays,
                'days' => 'All Week', 'hours' => 'Full Day (7 AM — 6 PM)',
                'lat' => 13.9650, 'lng' => 120.7400, 'verified' => true,  'rating' => 5.0,
            ],
            [
                'first_name' => 'Carmen',   'last_name' => 'Rivera',     'email' => 'carmen@example.com',   'category' => 'Painting',
                'bio' => 'Professional painter with a passion for colour. Interior and exterior specialist.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Wallpaper Installation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 380, 'years_exp' => 9, 'radius_km' => 10,
                'areas' => $barangays,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9480, 'lng' => 120.7150, 'verified' => false, 'rating' => 4.2,
            ],
        ];

        $workerUsers = [];
        foreach ($workersData as $data) {
            $user = User::create([
                'first_name'       => $data['first_name'],
                'last_name'        => $data['last_name'],
                'name'             => $data['first_name'] . ' ' . $data['last_name'],
                'email'            => $data['email'],
                'password'         => $password,
                'role'             => 'worker',
                'service_category' => $data['category'],
                'city'             => 'Tuy, Batangas',
                'language'         => 'English',
                'email_verified_at' => now(),
            ]);

            $profile = WorkerProfile::create([
                'user_id'               => $user->id,
                'bio'                   => $data['bio'],
                'skills'                => $data['skills'],
                'spoken_languages'      => $data['languages'],
                'hourly_rate'           => $data['hourly_rate'],
                'available_days'        => $data['days'],
                'preferred_hours'       => $data['hours'],
                'service_areas'         => $data['areas'],
                'years_of_experience'   => $data['years_exp'],
                'service_radius_km'     => $data['radius_km'],
                'government_id_verified' => $data['verified'],
                'average_rating'        => $data['rating'],
                'current_latitude'      => $data['lat'],
                'current_longitude'     => $data['lng'],
            ]);

            $workerUsers[] = $user;
        }

        // ── Provider Services ───────────────────────────────────────

        foreach ($workerUsers as $user) {
            $category = ServiceCategory::where('name', $user->service_category)->first();
            if ($category) {
                $services = $category->services;
                foreach ($services as $service) {
                    ProviderService::create([
                        'user_id'      => $user->id,
                        'service_id'   => $service->id,
                        'is_available' => true,
                    ]);
                }
            }
        }

        // ── Bookings (all within Tuy, Batangas barangays) ──────────

        $addresses = [
            'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
            'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
            'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
            'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
        ];

        $bookingsData = [
            // Completed (3)
            ['client' => 0, 'worker' => 0, 'service' => 'Leak Repair',          'addr' => 0,  'price' => 450,  'status' => Booking::STATUS_COMPLETED, 'days_ago' => 5],
            ['client' => 1, 'worker' => 1, 'service' => 'Deep Cleaning',         'addr' => 4,  'price' => 1200, 'status' => Booking::STATUS_COMPLETED, 'days_ago' => 3],
            ['client' => 2, 'worker' => 2, 'service' => 'Wiring Inspection',     'addr' => 8,  'price' => 650,  'status' => Booking::STATUS_COMPLETED, 'days_ago' => 7],
            // In-progress (2)
            ['client' => 0, 'worker' => 0, 'service' => 'Pipe Replacement',      'addr' => 1,  'price' => 450,  'status' => Booking::STATUS_IN_PROGRESS, 'days_ago' => 0],
            ['client' => 4, 'worker' => 1, 'service' => 'Move-in Cleaning',      'addr' => 12, 'price' => 300,  'status' => Booking::STATUS_IN_PROGRESS, 'days_ago' => 0],
            // En-route (2)
            ['client' => 3, 'worker' => 4, 'service' => 'Furniture Assembly',    'addr' => 16, 'price' => 350,  'status' => Booking::STATUS_EN_ROUTE, 'days_ago' => 0],
            ['client' => 2, 'worker' => 6, 'service' => 'AC Cleaning',           'addr' => 20, 'price' => 350,  'status' => Booking::STATUS_EN_ROUTE, 'days_ago' => 0],
            // Accepted (2)
            ['client' => 1, 'worker' => 2, 'service' => 'Electrical Inspection', 'addr' => 3,  'price' => 600,  'status' => Booking::STATUS_ACCEPTED, 'days_ago' => 0],
            ['client' => 0, 'worker' => 3, 'service' => 'Interior Painting',     'addr' => 6,  'price' => 400,  'status' => Booking::STATUS_ACCEPTED, 'days_ago' => 0],
            // New (3)
            ['client' => 4, 'worker' => 8, 'service' => 'Wiring Repair',         'addr' => 18, 'price' => 600,  'status' => Booking::STATUS_NEW, 'days_ago' => 0],
            ['client' => 3, 'worker' => 7, 'service' => 'Pipe Installation',     'addr' => 21, 'price' => 800,  'status' => Booking::STATUS_NEW, 'days_ago' => 0],
            ['client' => 1, 'worker' => 9, 'service' => 'Exterior Painting',     'addr' => 10, 'price' => 1200, 'status' => Booking::STATUS_NEW, 'days_ago' => 0],
        ];

        $completedBookings = [];
        foreach ($bookingsData as $data) {
            $scheduled = $data['days_ago'] > 0
                ? now()->subDays($data['days_ago'])->setHour(9)
                : now()->addHours(array_rand(array_flip([1, 2, 3, 4])));

            $completed = ($data['status'] === Booking::STATUS_COMPLETED)
                ? $scheduled->copy()->addHours(3)
                : null;

            $booking = Booking::create([
                'client_id'        => $clientUsers[$data['client']]->id,
                'worker_id'        => $workerUsers[$data['worker']]->id,
                'service_category' => $data['service'],
                'address'          => $addresses[$data['addr']] . ', Tuy, Batangas',
                'scheduled_at'     => $scheduled,
                'price'            => $data['price'],
                'status'           => $data['status'],
                'completed_at'     => $completed,
            ]);

            if ($data['status'] === Booking::STATUS_COMPLETED) {
                $completedBookings[] = $booking;
            }
        }

        // ── Earnings (from completed bookings + additional ledger) ──

        foreach ($completedBookings as $b) {
            $gross = $b->price;
            $fee = round($gross * ($platformFeePercent / 100), 2);
            $net = $gross - $fee;

            Earning::create([
                'worker_id'    => $b->worker_id,
                'booking_id'   => $b->id,
                'gross_amount' => $gross,
                'platform_fee' => $fee,
                'net_amount'   => $net,
                'paid_at'      => $b->completed_at,
            ]);
        }

        $additionalEarnings = [
            ['worker' => 0, 'gross' => 500,  'days_ago' => 12],
            ['worker' => 0, 'gross' => 350,  'days_ago' => 18],
            ['worker' => 1, 'gross' => 800,  'days_ago' => 10],
            ['worker' => 1, 'gross' => 600,  'days_ago' => 20],
            ['worker' => 2, 'gross' => 1200, 'days_ago' => 8],
            ['worker' => 2, 'gross' => 900,  'days_ago' => 15],
            ['worker' => 3, 'gross' => 750,  'days_ago' => 6],
            ['worker' => 4, 'gross' => 1100, 'days_ago' => 14],
        ];

        foreach ($additionalEarnings as $data) {
            $paidAt = now()->subDays($data['days_ago'])->setHour(17);
            $fee = round($data['gross'] * ($platformFeePercent / 100), 2);
            $net = $data['gross'] - $fee;

            $dummyBooking = Booking::create([
                'client_id'        => $clientUsers[array_rand($clientUsers)]->id,
                'worker_id'        => $workerUsers[$data['worker']]->id,
                'service_category' => 'Completed Job',
                'address'          => $addresses[array_rand($addresses)] . ', Tuy, Batangas',
                'scheduled_at'     => $paidAt->copy()->subHours(3),
                'price'            => $data['gross'],
                'status'           => Booking::STATUS_COMPLETED,
                'completed_at'     => $paidAt,
            ]);

            Earning::create([
                'worker_id'    => $workerUsers[$data['worker']]->id,
                'booking_id'   => $dummyBooking->id,
                'gross_amount' => $data['gross'],
                'platform_fee' => $fee,
                'net_amount'   => $net,
                'paid_at'      => $paidAt,
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ── Sofia Gomez — Dedicated Data ─────────────────────────
        // ═══════════════════════════════════════════════════════════

        $sofia = $workerUsers[3];
        $sofiaProfile = $sofia->workerProfile;

        // ── Additional Bookings for Sofia ────────────────────────

        $sofiaBookings = [
            // Completed
            ['client' => 2, 'service' => 'Cabinet Refinishing', 'addr' => 15, 'price' => 750,  'days_ago' => 14],
            ['client' => 4, 'service' => 'Interior Painting',    'addr' => 21, 'price' => 1200, 'days_ago' => 10],
            // In-progress
            ['client' => 3, 'service' => 'Exterior Painting',    'addr' => 5,  'price' => 900,  'days_ago' => 0],
            // En-route
            ['client' => 1, 'service' => 'Cabinet Refinishing',  'addr' => 18, 'price' => 650,  'days_ago' => 0],
            // New
            ['client' => 0, 'service' => 'Wall Painting',        'addr' => 11, 'price' => 500,  'days_ago' => 0],
            ['client' => 2, 'service' => 'Ceiling Painting',     'addr' => 17, 'price' => 850,  'days_ago' => 0],
        ];

        $sofiaStatuses = [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_EN_ROUTE,
            Booking::STATUS_NEW,
            Booking::STATUS_NEW,
        ];

        foreach ($sofiaBookings as $i => $data) {
            $isComplete = $sofiaStatuses[$i] === Booking::STATUS_COMPLETED;
            $scheduled = $isComplete
                ? now()->subDays($data['days_ago'])->setHour(8)
                : now()->addDays(rand(1, 6))->setHour(rand(8, 16));

            $completed = $isComplete
                ? $scheduled->copy()->addHours(rand(3, 5))
                : null;

            $booking = Booking::create([
                'client_id'        => $clientUsers[$data['client']]->id,
                'worker_id'        => $sofia->id,
                'service_category' => $data['service'],
                'address'          => $addresses[$data['addr']] . ', Tuy, Batangas',
                'scheduled_at'     => $scheduled,
                'price'            => $data['price'],
                'status'           => $sofiaStatuses[$i],
                'completed_at'     => $completed,
            ]);

            if ($isComplete) {
                $gross = $booking->price;
                $fee = round($gross * ($platformFeePercent / 100), 2);
                Earning::create([
                    'worker_id'    => $sofia->id,
                    'booking_id'   => $booking->id,
                    'gross_amount' => $gross,
                    'platform_fee' => $fee,
                    'net_amount'   => $gross - $fee,
                    'paid_at'      => $completed,
                ]);
            }
        }

        // ── Additional Earnings for Sofia ────────────────────────

        $sofiaExtraEarnings = [
            ['gross' => 950,  'days_ago' => 22],
            ['gross' => 1100, 'days_ago' => 28],
            ['gross' => 600,  'days_ago' => 35],
        ];

        foreach ($sofiaExtraEarnings as $data) {
            $paidAt = now()->subDays($data['days_ago'])->setHour(17);
            $fee = round($data['gross'] * ($platformFeePercent / 100), 2);

            $dummy = Booking::create([
                'client_id'        => $clientUsers[array_rand($clientUsers)]->id,
                'worker_id'        => $sofia->id,
                'service_category' => 'Completed Painting Job',
                'address'          => $addresses[array_rand($addresses)] . ', Tuy, Batangas',
                'scheduled_at'     => $paidAt->copy()->subHours(3),
                'price'            => $data['gross'],
                'status'           => Booking::STATUS_COMPLETED,
                'completed_at'     => $paidAt,
            ]);

            Earning::create([
                'worker_id'    => $sofia->id,
                'booking_id'   => $dummy->id,
                'gross_amount' => $data['gross'],
                'platform_fee' => $fee,
                'net_amount'   => $data['gross'] - $fee,
                'paid_at'      => $paidAt,
            ]);
        }

        // ── Work Portfolio for Sofia ─────────────────────────────

        $portfolioItems = [
            ['caption' => 'Living room interior — complete repaint with warm neutral tones'],
            ['caption' => 'Cabinet refinishing — from dark wood to modern white gloss'],
            ['caption' => 'Exterior facade painting — two-storey residential home'],
            ['caption' => 'Bedroom accent wall — deep navy feature wall'],
            ['caption' => 'Kitchen cabinet makeover — full refinishing project'],
        ];

        foreach ($portfolioItems as $item) {
            $sofiaProfile->portfolios()->create([
                'photo_path' => 'portfolios/sofia-sample-' . rand(1, 999) . '.jpg',
                'caption'    => $item['caption'],
            ]);
        }

        // ── Documents for Sofia ──────────────────────────────────

        $docTypes = ['Government-Issued ID', 'NBI Clearance', 'Barangay Clearance', 'Proof of Competency'];
        $docStatuses = ['verified', 'verified', 'verified', 'pending'];

        foreach ($docTypes as $i => $type) {
            $sofia->workerDocuments()->create([
                'document_type' => $type,
                'file_path'     => 'documents/sofia-' . str_replace(' ', '-', strtolower($type)) . '.pdf',
                'status'        => $docStatuses[$i],
                'verified_at'   => $docStatuses[$i] === 'verified' ? now()->subDays(rand(10, 60)) : null,
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ── Juan Dela Cruz — Extra Schedule Data ─────────────────
        // ═══════════════════════════════════════════════════════════

        $juan = $workerUsers[0];

        $juanExtra = [
            ['client' => 4, 'service' => 'Water Heater Repair',  'addr' => 8,  'price' => 550, 'status' => Booking::STATUS_ACCEPTED, 'days_from_now' => 2],
            ['client' => 2, 'service' => 'Drain Cleaning',        'addr' => 13, 'price' => 380, 'status' => Booking::STATUS_NEW,      'days_from_now' => 4],
            ['client' => 1, 'service' => 'Pipe Inspection',       'addr' => 2,  'price' => 300, 'status' => Booking::STATUS_ACCEPTED, 'days_from_now' => 1],
        ];

        foreach ($juanExtra as $data) {
            Booking::create([
                'client_id'        => $clientUsers[$data['client']]->id,
                'worker_id'        => $juan->id,
                'service_category' => $data['service'],
                'address'          => $addresses[$data['addr']] . ', Tuy, Batangas',
                'scheduled_at'     => now()->addDays($data['days_from_now'])->setHour(rand(9, 15)),
                'price'            => $data['price'],
                'status'           => $data['status'],
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ── Extra bookings for remaining workers ─────────────────
        // ═══════════════════════════════════════════════════════════

        $extraWorkerBookings = [
            // Elena (Cleaning, index 1)
            [1, 0, 'Kitchen Cleaning',     7,  350, Booking::STATUS_NEW,      2],
            [1, 2, 'Bathroom Scrubbing',   14, 280, Booking::STATUS_ACCEPTED, 3],
            [1, 3, 'Window Washing',       6,  200, Booking::STATUS_EN_ROUTE, 1],
            [1, 4, 'Deep Cleaning',        19, 800, Booking::STATUS_NEW,      5],
            // Marco (Electrical, index 2)
            [2, 1, 'Light Fixture Install', 9, 400, Booking::STATUS_ACCEPTED,  2],
            [2, 3, 'Outlet Repair',         11, 250, Booking::STATUS_EN_ROUTE, 1],
            [2, 0, 'Ceiling Fan Wiring',    16, 500, Booking::STATUS_NEW,      3],
            // Pedro (Carpentry, index 4)
            [4, 2, 'Cabinet Repair',        5,  700, Booking::STATUS_ACCEPTED,  2],
            [4, 1, 'Bookshelf Assembly',    20, 350, Booking::STATUS_NEW,       4],
            [4, 3, 'Door Fixing',           10, 450, Booking::STATUS_IN_PROGRESS, 0],
            [4, 0, 'Custom Shelving',       15, 1200, Booking::STATUS_NEW,      6],
            // Liza (Cleaning, index 5)
            [5, 3, 'Eco Home Cleaning',     12, 400, Booking::STATUS_NEW,      1],
            [5, 0, 'Move-in Cleaning',      4,  600, Booking::STATUS_ACCEPTED, 3],
            [5, 2, 'Organising Service',    8,  250, Booking::STATUS_EN_ROUTE, 2],
            // Ramon (Aircon, index 6)
            [6, 1, 'AC General Cleaning',   17, 500, Booking::STATUS_NEW,       2],
            [6, 4, 'AC Repair',             2,  800, Booking::STATUS_ACCEPTED,  0],
            [6, 0, 'AC Installation',       9,  1500, Booking::STATUS_NEW,      5],
            [6, 3, 'AC Chemical Cleaning',  21, 600, Booking::STATUS_IN_PROGRESS, 0],
            // Bella (Plumbing, index 7)
            [7, 2, 'Faucet Replacement',    3,  300, Booking::STATUS_NEW,       1],
            [7, 0, 'Pipe Leak Fix',         18, 400, Booking::STATUS_ACCEPTED,  2],
            [7, 4, 'Water Heater Check',    1,  500, Booking::STATUS_EN_ROUTE,  0],
            [7, 1, 'Toilet Repair',         13, 350, Booking::STATUS_NEW,       4],
            // Dante (Electrical, index 8)
            [8, 3, 'Generator Wiring',      0,  1000, Booking::STATUS_NEW,      3],
            [8, 1, 'Smart Switch Setup',    6,  450, Booking::STATUS_ACCEPTED, 1],
            [8, 2, 'Electrical Inspection', 14, 600, Booking::STATUS_NEW,      5],
            // Carmen (Painting, index 9)
            [9, 0, 'Room Repaint',          8,  700, Booking::STATUS_ACCEPTED,  2],
            [9, 2, 'Wallpaper Installation', 20, 500, Booking::STATUS_NEW,      4],
            [9, 4, 'Ceiling Painting',      11, 650, Booking::STATUS_EN_ROUTE,  1],
            [9, 3, 'Trim Painting',         16, 350, Booking::STATUS_NEW,       6],
        ];

        foreach ($extraWorkerBookings as $d) {
            Booking::create([
                'client_id'        => $clientUsers[$d[1]]->id,
                'worker_id'        => $workerUsers[$d[0]]->id,
                'service_category' => $d[2],
                'address'          => $addresses[$d[3]] . ', Tuy, Batangas',
                'scheduled_at'     => now()->addDays($d[6])->setHour(rand(8, 16)),
                'price'            => $d[4],
                'status'           => $d[5],
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // ── Messages (real DB records for every booking) ─────────
        // ═══════════════════════════════════════════════════════════

        $allBookings = Booking::whereIn('status', Booking::STATUSES)->get();

        $messageTemplates = [
            Booking::STATUS_NEW => [
                ['from_worker' => false, 'message' => 'Hi! I saw your profile and I\'d like to book your service.'],
                ['from_worker' => true,  'message' => 'Thanks for reaching out! I\'d be happy to help.'],
            ],
            Booking::STATUS_ACCEPTED => [
                ['from_worker' => false, 'message' => 'Hi! I need help with this job. Are you available?'],
                ['from_worker' => true,  'message' => 'Yes, I\'m available. I\'ve accepted the booking.'],
                ['from_worker' => false, 'message' => 'Great! What time should I expect you?'],
                ['from_worker' => true,  'message' => 'I\'ll be there at the scheduled time. I\'ll message you before I head out.'],
            ],
            Booking::STATUS_EN_ROUTE => [
                ['from_worker' => false, 'message' => 'Are you on your way?'],
                ['from_worker' => true,  'message' => 'Yes, I\'m on my way now! ETA about 15 minutes.'],
                ['from_worker' => false, 'message' => 'See you soon!'],
            ],
            Booking::STATUS_IN_PROGRESS => [
                ['from_worker' => false, 'message' => 'How\'s it going?'],
                ['from_worker' => true,  'message' => 'I\'ve arrived and started working on it.'],
                ['from_worker' => false, 'message' => 'Let me know if you need anything.'],
                ['from_worker' => true,  'message' => 'Will do! Everything is going smoothly.'],
            ],
            Booking::STATUS_COMPLETED => [
                ['from_worker' => false, 'message' => 'All done! Thank you for the great service.'],
                ['from_worker' => true,  'message' => 'You\'re welcome! Let me know if you need anything else.'],
                ['from_worker' => false, 'message' => 'I\'ll definitely book you again!'],
            ],
        ];

        foreach ($allBookings as $booking) {
            $templates = $messageTemplates[$booking->status] ?? $messageTemplates[Booking::STATUS_NEW];
            $hoursAgo = 0;

            foreach ($templates as $tmpl) {
                $senderId = $tmpl['from_worker'] ? $booking->worker_id : $booking->client_id;
                $receiverId = $tmpl['from_worker'] ? $booking->client_id : $booking->worker_id;

                Message::create([
                    'booking_id'  => $booking->id,
                    'sender_id'   => $senderId,
                    'receiver_id' => $receiverId,
                    'message'     => $tmpl['message'],
                    'read_at'     => $hoursAgo > 0 ? now()->subMinutes(rand(5, 120)) : null,
                    'created_at'  => $booking->scheduled_at->copy()->subHours(2)->addMinutes($hoursAgo),
                    'updated_at'  => $booking->scheduled_at->copy()->subHours(2)->addMinutes($hoursAgo),
                ]);

                $hoursAgo += rand(10, 60);
            }
        }

        // ── Summary ─────────────────────────────────────────────────

        $this->command->info('Database seeded successfully!');
        $this->command->info('All data is scoped to Tuy, Batangas across ' . count($barangays) . ' official barangays.');
        $this->command->info('Admin:  admin@kaayos.com / password');
        $this->command->info('Clients: maria@example.com, john@example.com, ana@example.com, carlos@example.com, rosa@example.com / password');
        $this->command->info('Workers: juan@example.com, elena@example.com, marco@example.com, sofia@example.com, pedro@example.com, liza@example.com, ramon@example.com, bella@example.com, dante@example.com, carmen@example.com / password');
    }
}
