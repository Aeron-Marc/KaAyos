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
use App\Models\Review;
use App\Models\WorkPortfolio;
use App\Models\WorkerDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $platformFeePercent = config('kaayos.platform_fee_percent', 10);

        // ── Barangays of Tuy, Batangas ────────────────────────────

        $barangays = [
            'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
            'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
            'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
            'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
        ];

        // ═══════════════════════════════════════════════════════════
        //  1. SERVICE CATEGORIES (13)
        // ═══════════════════════════════════════════════════════════

        $categories = [
            ['name' => 'Plumbing',           'slug' => 'plumbing',           'description' => 'Pipe repairs, water heater installation, drainage solutions',                   'icon' => 'fa-wrench'],
            ['name' => 'Electrical',         'slug' => 'electrical',         'description' => 'Wiring, panel upgrades, lighting installation and repair',                     'icon' => 'fa-bolt'],
            ['name' => 'Cleaning',           'slug' => 'cleaning',           'description' => 'Deep cleaning, move-in/move-out, window and floor care',                        'icon' => 'fa-broom'],
            ['name' => 'Carpentry',          'slug' => 'carpentry',          'description' => 'Furniture assembly, cabinet installation, wood repairs',                        'icon' => 'fa-screwdriver-wrench'],
            ['name' => 'Painting',           'slug' => 'painting',           'description' => 'Interior and exterior painting, cabinet refinishing',                          'icon' => 'fa-paint-roller'],
            ['name' => 'Aircon',             'slug' => 'aircon',             'description' => 'AC cleaning, repair, and installation',                                        'icon' => 'fa-snowflake'],
            ['name' => 'Landscaping',        'slug' => 'landscaping',        'description' => 'Lawn care, tree trimming, gardening and yard maintenance',                     'icon' => 'fa-tree'],
            ['name' => 'Laundry',            'slug' => 'laundry',            'description' => 'Wash and fold, dry cleaning, ironing and fabric care',                         'icon' => 'fa-shirt'],
            ['name' => 'Pest Control',       'slug' => 'pest-control',       'description' => 'Fumigation, rodent control, termite and insect treatment',                     'icon' => 'fa-bug'],
            ['name' => 'Appliance Repair',   'slug' => 'appliance-repair',   'description' => 'Repair and maintenance of home appliances and electronics',                    'icon' => 'fa-tv'],
            ['name' => 'Masonry',            'slug' => 'masonry',            'description' => 'Tiling, concrete work, brick laying and waterproofing',                        'icon' => 'fa-hammer'],
            ['name' => 'Personal Care',      'slug' => 'personal-care',      'description' => 'Home massage, manicure, pedicure, haircut and beauty services',                 'icon' => 'fa-hand-sparkles'],
            ['name' => 'Moving Services',    'slug' => 'moving-services',    'description' => 'Packing, loading, transport and heavy item moving assistance',                  'icon' => 'fa-truck'],
        ];

        foreach ($categories as $data) {
            ServiceCategory::create($data);
        }

        // ═══════════════════════════════════════════════════════════
        //  2. SERVICES (all categories)
        // ═══════════════════════════════════════════════════════════

        $servicesData = [
            'plumbing' => [
                ['name' => 'Leak Repair',                'slug' => 'leak-repair',                  'base_price' => 350],
                ['name' => 'Pipe Installation',          'slug' => 'pipe-installation',            'base_price' => 500],
                ['name' => 'Water Heater Service',       'slug' => 'water-heater-service',         'base_price' => 450],
                ['name' => 'Drain Cleaning',             'slug' => 'drain-cleaning',               'base_price' => 300],
                ['name' => 'Toilet Repair & Installation','slug' => 'toilet-repair-installation',  'base_price' => 400],
                ['name' => 'Faucet Replacement',         'slug' => 'faucet-replacement',           'base_price' => 250],
                ['name' => 'Sewer Line Cleaning',        'slug' => 'sewer-line-cleaning',          'base_price' => 600],
                ['name' => 'Garbage Disposal Repair',    'slug' => 'garbage-disposal-repair',      'base_price' => 350],
            ],
            'electrical' => [
                ['name' => 'Electrical Inspection',      'slug' => 'electrical-inspection',        'base_price' => 400],
                ['name' => 'Wiring & Rewiring',          'slug' => 'wiring-rewiring',              'base_price' => 600],
                ['name' => 'Lighting Installation',      'slug' => 'lighting-installation',        'base_price' => 300],
                ['name' => 'Outlet & Switch Repair',     'slug' => 'outlet-switch-repair',         'base_price' => 250],
                ['name' => 'Ceiling Fan Installation',   'slug' => 'ceiling-fan-installation',     'base_price' => 400],
                ['name' => 'Panel Upgrade',              'slug' => 'panel-upgrade',                'base_price' => 1000],
                ['name' => 'Generator Repair',           'slug' => 'generator-repair',             'base_price' => 700],
                ['name' => 'Smart Home Setup',           'slug' => 'smart-home-setup',             'base_price' => 500],
            ],
            'cleaning' => [
                ['name' => 'Deep Cleaning',              'slug' => 'deep-cleaning',                'base_price' => 300],
                ['name' => 'Move-in/out Cleaning',       'slug' => 'move-in-out-cleaning',         'base_price' => 500],
                ['name' => 'Window Cleaning',            'slug' => 'window-cleaning',              'base_price' => 200],
                ['name' => 'Carpet Shampooing',          'slug' => 'carpet-shampooing',            'base_price' => 400],
                ['name' => 'Upholstery Cleaning',        'slug' => 'upholstery-cleaning',          'base_price' => 350],
                ['name' => 'Post-Construction Cleaning', 'slug' => 'post-construction-cleaning',   'base_price' => 600],
                ['name' => 'Office Cleaning',            'slug' => 'office-cleaning',              'base_price' => 400],
                ['name' => 'Pressure Washing',           'slug' => 'pressure-washing',             'base_price' => 500],
            ],
            'carpentry' => [
                ['name' => 'Furniture Assembly',         'slug' => 'furniture-assembly',           'base_price' => 350],
                ['name' => 'Cabinet Installation',       'slug' => 'cabinet-installation',         'base_price' => 550],
                ['name' => 'Custom Shelving',            'slug' => 'custom-shelving',              'base_price' => 400],
                ['name' => 'Door Repair & Installation', 'slug' => 'door-repair-installation',     'base_price' => 450],
                ['name' => 'Deck Repair',                'slug' => 'deck-repair',                  'base_price' => 600],
                ['name' => 'Trim & Molding',             'slug' => 'trim-molding',                 'base_price' => 350],
                ['name' => 'Drywall Repair',             'slug' => 'drywall-repair',               'base_price' => 300],
                ['name' => 'Kitchen Cabinet Refacing',   'slug' => 'kitchen-cabinet-refacing',      'base_price' => 700],
            ],
            'painting' => [
                ['name' => 'Interior Painting',          'slug' => 'interior-painting',            'base_price' => 450],
                ['name' => 'Exterior Painting',          'slug' => 'exterior-painting',            'base_price' => 600],
                ['name' => 'Cabinet Refinishing',        'slug' => 'cabinet-refinishing',          'base_price' => 350],
                ['name' => 'Fence Painting',             'slug' => 'fence-painting',               'base_price' => 400],
                ['name' => 'Ceiling Painting',           'slug' => 'ceiling-painting',             'base_price' => 400],
                ['name' => 'Wallpaper Removal',          'slug' => 'wallpaper-removal',            'base_price' => 350],
                ['name' => 'Garage Floor Coating',       'slug' => 'garage-floor-coating',         'base_price' => 500],
            ],
            'aircon' => [
                ['name' => 'AC Cleaning',                'slug' => 'ac-cleaning',                  'base_price' => 350],
                ['name' => 'AC Repair',                  'slug' => 'ac-repair',                    'base_price' => 500],
                ['name' => 'AC Installation',            'slug' => 'ac-installation',              'base_price' => 800],
                ['name' => 'Gas Refill',                 'slug' => 'gas-refill',                   'base_price' => 500],
                ['name' => 'Duct Cleaning',              'slug' => 'duct-cleaning',                'base_price' => 600],
                ['name' => 'Compressor Repair',          'slug' => 'compressor-repair',            'base_price' => 800],
                ['name' => 'Filter Replacement',         'slug' => 'filter-replacement',           'base_price' => 200],
            ],
            'landscaping' => [
                ['name' => 'Lawn Mowing',                'slug' => 'lawn-mowing',                  'base_price' => 250],
                ['name' => 'Tree Trimming',              'slug' => 'tree-trimming',                'base_price' => 500],
                ['name' => 'Hedge Trimming',             'slug' => 'hedge-trimming',               'base_price' => 300],
                ['name' => 'Planting & Gardening',       'slug' => 'planting-gardening',           'base_price' => 350],
                ['name' => 'Yard Cleanup',               'slug' => 'yard-cleanup',                 'base_price' => 400],
                ['name' => 'Garden Design',              'slug' => 'garden-design',                'base_price' => 600],
                ['name' => 'Lot Clearing',               'slug' => 'lot-clearing',                 'base_price' => 800],
            ],
            'laundry' => [
                ['name' => 'Wash & Fold',                'slug' => 'wash-fold',                    'base_price' => 150],
                ['name' => 'Dry Cleaning',               'slug' => 'dry-cleaning',                 'base_price' => 200],
                ['name' => 'Ironing & Pressing',         'slug' => 'ironing-pressing',             'base_price' => 120],
                ['name' => 'Pick-up & Delivery',         'slug' => 'pickup-delivery',              'base_price' => 100],
                ['name' => 'Curtain Cleaning',           'slug' => 'curtain-cleaning',             'base_price' => 300],
                ['name' => 'Blanket & Comforter Wash',   'slug' => 'blanket-comforter-wash',       'base_price' => 250],
            ],
            'pest-control' => [
                ['name' => 'General Fumigation',         'slug' => 'general-fumigation',           'base_price' => 500],
                ['name' => 'Rodent Control',             'slug' => 'rodent-control',               'base_price' => 400],
                ['name' => 'Termite Treatment',          'slug' => 'termite-treatment',            'base_price' => 700],
                ['name' => 'Mosquito Control',           'slug' => 'mosquito-control',             'base_price' => 350],
                ['name' => 'Ant & Roach Removal',        'slug' => 'ant-roach-removal',            'base_price' => 300],
                ['name' => 'Preventive Spraying',        'slug' => 'preventive-spraying',          'base_price' => 250],
            ],
            'appliance-repair' => [
                ['name' => 'Refrigerator Repair',        'slug' => 'refrigerator-repair',          'base_price' => 500],
                ['name' => 'Washing Machine Repair',     'slug' => 'washing-machine-repair',       'base_price' => 450],
                ['name' => 'Oven & Stove Repair',        'slug' => 'oven-stove-repair',            'base_price' => 400],
                ['name' => 'Water Dispenser Repair',     'slug' => 'water-dispenser-repair',       'base_price' => 300],
                ['name' => 'Electric Fan Repair',        'slug' => 'electric-fan-repair',          'base_price' => 200],
                ['name' => 'Microwave Repair',           'slug' => 'microwave-repair',             'base_price' => 350],
                ['name' => 'Dryer Repair',               'slug' => 'dryer-repair',                 'base_price' => 450],
            ],
            'masonry' => [
                ['name' => 'Tiling',                     'slug' => 'tiling',                       'base_price' => 500],
                ['name' => 'Concrete Repair',            'slug' => 'concrete-repair',              'base_price' => 600],
                ['name' => 'Brick & Block Work',         'slug' => 'brick-block-work',             'base_price' => 550],
                ['name' => 'Waterproofing',              'slug' => 'waterproofing',                'base_price' => 450],
                ['name' => 'Wall Repointing',            'slug' => 'wall-repointing',              'base_price' => 400],
                ['name' => 'Pavement & Walkway',         'slug' => 'pavement-walkway',             'base_price' => 700],
            ],
            'personal-care' => [
                ['name' => 'Home Massage',               'slug' => 'home-massage',                 'base_price' => 400],
                ['name' => 'Manicure',                   'slug' => 'manicure',                     'base_price' => 150],
                ['name' => 'Pedicure',                   'slug' => 'pedicure',                     'base_price' => 180],
                ['name' => 'Haircut & Blow-dry',         'slug' => 'haircut-blowdry',              'base_price' => 200],
                ['name' => 'Facial',                     'slug' => 'facial',                       'base_price' => 350],
                ['name' => 'Waxing',                     'slug' => 'waxing',                       'base_price' => 200],
            ],
            'moving-services' => [
                ['name' => 'Packing Assistance',          'slug' => 'packing-assistance',          'base_price' => 300],
                ['name' => 'Loading & Unloading',         'slug' => 'loading-unloading',           'base_price' => 350],
                ['name' => 'Local Transport',             'slug' => 'local-transport',             'base_price' => 500],
                ['name' => 'Heavy Item Moving',           'slug' => 'heavy-item-moving',           'base_price' => 600],
                ['name' => 'Storage Assistance',          'slug' => 'storage-assistance',          'base_price' => 400],
            ],
        ];

        foreach ($servicesData as $catSlug => $services) {
            $category = ServiceCategory::where('slug', $catSlug)->first();
            foreach ($services as $data) {
                $category->services()->create($data);
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  3. ADMIN USER
        // ═══════════════════════════════════════════════════════════

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

        // ═══════════════════════════════════════════════════════════
        //  4. CLIENTS (15)
        // ═══════════════════════════════════════════════════════════

        $clients = [
            ['first_name' => 'Juan',          'last_name' => 'Dela Cruz',     'email' => 'juan.cruz@example.com'],
            ['first_name' => 'Maria',         'last_name' => 'Santos',        'email' => 'maria.santos@example.com'],
            ['first_name' => 'Mark Anthony',  'last_name' => 'Reyes',         'email' => 'mark.reyes@example.com'],
            ['first_name' => 'John Paul',     'last_name' => 'Mendoza',       'email' => 'jp.mendoza@example.com'],
            ['first_name' => 'Maricel',       'last_name' => 'Bautista',      'email' => 'maricel.bautista@example.com'],
            ['first_name' => 'Jose',          'last_name' => 'Garcia',        'email' => 'jose.garcia@example.com'],
            ['first_name' => 'Luzviminda',    'last_name' => 'Flores',        'email' => 'luz.flores@example.com'],
            ['first_name' => 'Jericho',       'last_name' => 'Cruz',          'email' => 'jericho.cruz@example.com'],
            ['first_name' => 'Jayson',        'last_name' => 'Villanueva',    'email' => 'jayson.villanueva@example.com'],
            ['first_name' => 'Niño',          'last_name' => 'Ramos',         'email' => 'nino.ramos@example.com'],
            ['first_name' => 'Joy',           'last_name' => 'Gonzales',      'email' => 'joy.gonzales@example.com'],
            ['first_name' => 'Crisanto',      'last_name' => 'Aquino',        'email' => 'cris.aquino@example.com'],
            ['first_name' => 'Mayumi',        'last_name' => 'Fernandez',     'email' => 'mayumi.fernandez@example.com'],
            ['first_name' => 'Diwata',        'last_name' => 'Perez',         'email' => 'diwata.perez@example.com'],
            ['first_name' => 'Francisco',     'last_name' => 'Mercado',       'email' => 'francisco.mercado@example.com'],
        ];

        $clientUsers = [];
        foreach ($clients as $data) {
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

        // ═══════════════════════════════════════════════════════════
        //  5. WORKER USERS & PROFILES (46 — 7 full + 9 basic+ + 30 new)
        // ═══════════════════════════════════════════════════════════
        //
        //  Full providers: indices 0-5, 15 (one per core category)
        //    Divina Lopez       → Plumbing
        //    Cherry Gil Pascual → Cleaning
        //    Ferdinand Ocampo   → Electrical
        //    Roberto Castro     → Carpentry
        //    Nene Aguilar       → Painting
        //    Angelo Tolentino   → Aircon
        //    Sofia Gomez        → Painting
        //
        //  Basic+ providers: indices 6-14
        //    Grace Padilla      → Landscaping
        //    Christian Alonzo   → Laundry
        //    Princess Joy David → Pest Control
        //    Reynaldo Navarro   → Appliance Repair
        //    Jasmine Torres     → Masonry
        //    Vincent Dizon      → Personal Care
        //    Fatima Sotto       → Moving Services
        //    Edgar Soriano      → Plumbing  (extra)
        //    Mary Grace Valenzuela → Cleaning (extra)
        //
        //  New basic+ providers: indices 16-45
        //    16 Malaya Dimaculangan  → Plumbing
        //    17 Alab Macalintal      → Electrical
        //    18 Tala Catacutan       → Cleaning
        //    19 Kidlat Panganiban    → Carpentry
        //    20 Mutya Magbanua       → Painting
        //    21 Lakan Dalisay        → Plumbing
        //    22 Dakila Manalo        → Electrical
        //    23 Hiraya Ilagan        → Cleaning
        //    24 Bituin Lualhati      → Carpentry
        //    25 Isagani Bayani       → Painting
        //    26 Makisig Dimagiba     → Aircon
        //    27 Amihan Batumbakal    → Landscaping
        //    28 Mithi Agbayani       → Laundry
        //    29 Sinag Mabini         → Pest Control
        //    30 Alon Silang          → Appliance Repair
        //    31 Bagwis Balagtas      → Masonry
        //    32 Kislap Tolosa        → Personal Care
        //    33 Marikit Alcantara    → Moving Services
        //    34 Alaya Gatchalian     → Plumbing
        //    35 Datu Macaraig        → Electrical
        //    36 Magiting Magsaysay   → Cleaning
        //    37 Tadhana Pangilinan   → Carpentry
        //    38 Luningning Tupaz     → Painting
        //    39 Diwa Velasco         → Aircon
        //    40 Sampaguita Yuzon     → Landscaping
        //    41 Ligaya Zapanta       → Laundry
        //    42 Biyaya Halili        → Pest Control
        //    43 Liwanag Manansala    → Appliance Repair
        //    44 Dalisay Polistico    → Masonry
        //    45 Yumi Sumulong        → Personal Care
        //
        //  All providers get what the AI suggestion engine needs:
        //    - profile with skills, hourly_rate, languages
        //    - government_id_verified = true
        //    - average_rating > 0
        //    - completed bookings with reviews
        //    - non-completed bookings (various statuses)

        $workersData = [
            // ── Full Provider 0: Divina Lopez — Plumbing ──────────
            [
                'first_name' => 'Divina', 'last_name' => 'Lopez', 'email' => 'divina.lopez@example.com', 'category' => 'Plumbing',
                'bio' => 'Licensed master plumber with over 10 years of experience. Expert in pipe repairs, water heater installation, and drainage solutions. Fully insured and committed to quality service.',
                'skills' => ['Pipe Repair', 'Water Heater', 'Drain Unblocking', 'Pipe Installation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 450, 'years_exp' => 10, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9581, 'lng' => 120.7278, 'verified' => true, 'rating' => 4.8, 'full' => true,
            ],
            // ── Full Provider 1: Cherry Gil Pascual — Cleaning ────
            [
                'first_name' => 'Cherry Gil', 'last_name' => 'Pascual', 'email' => 'cherry.pascual@example.com', 'category' => 'Cleaning',
                'bio' => 'Professional cleaner with 8 years of experience. Specializes in deep cleaning, move-in/move-out services, and eco-friendly cleaning methods. Trusted by over 100 happy homeowners.',
                'skills' => ['Deep Cleaning', 'Move-in/Move-out', 'Window Cleaning', 'Carpet Shampooing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 8, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9550, 'lng' => 120.7300, 'verified' => true, 'rating' => 4.7, 'full' => true,
            ],
            // ── Full Provider 2: Ferdinand Ocampo — Electrical ────
            [
                'first_name' => 'Ferdinand', 'last_name' => 'Ocampo', 'email' => 'ferdinand.ocampo@example.com', 'category' => 'Electrical',
                'bio' => 'Licensed professional electrician with 12 years in residential and commercial wiring. Expert in panel upgrades, smart home automation, and troubleshooting complex electrical issues.',
                'skills' => ['Wiring', 'Panel Upgrades', 'Lighting Setup', 'Home Automation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 600, 'years_exp' => 12, 'radius_km' => 20,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9600, 'lng' => 120.7250, 'verified' => true, 'rating' => 4.9, 'full' => true,
            ],
            // ── Full Provider 3: Roberto Castro — Carpentry ───────
            [
                'first_name' => 'Roberto', 'last_name' => 'Castro', 'email' => 'roberto.castro@example.com', 'category' => 'Carpentry',
                'bio' => 'Master carpenter with 15 years of experience. Specializes in custom furniture, cabinet making, and home renovations. Known for precision and attention to detail.',
                'skills' => ['Furniture Building', 'Cabinet Making', 'Wood Repair', 'Custom Shelving'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 500, 'years_exp' => 15, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9560, 'lng' => 120.7350, 'verified' => true, 'rating' => 4.8, 'full' => true,
            ],
            // ── Full Provider 4: Nene Aguilar — Painting ──────────
            [
                'first_name' => 'Nene', 'last_name' => 'Aguilar', 'email' => 'nene.aguilar@example.com', 'category' => 'Painting',
                'bio' => 'Creative painter with a passion for transforming homes. Skilled in interior and exterior painting, cabinet refinishing, and decorative finishes. 9 years of experience.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing', 'Wallpaper Removal'],
                'languages' => ['Filipino'],
                'hourly_rate' => 400, 'years_exp' => 9, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9520, 'lng' => 120.7200, 'verified' => true, 'rating' => 4.5, 'full' => true,
            ],
            // ── Full Provider 5: Angelo Tolentino — Aircon ────────
            [
                'first_name' => 'Angelo', 'last_name' => 'Tolentino', 'email' => 'angelo.tolentino@example.com', 'category' => 'Aircon',
                'bio' => 'Certified HVAC technician with 7 years of experience. AC cleaning, repair, gas refill, and installation for all brands. Fast and reliable service.',
                'skills' => ['AC Cleaning', 'AC Repair', 'AC Installation', 'Gas Refill'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 550, 'years_exp' => 7, 'radius_km' => 20,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 6 PM)',
                'lat' => 13.9620, 'lng' => 120.7300, 'verified' => true, 'rating' => 4.6, 'full' => true,
            ],
            // ── Basic+ Provider 6: Grace Padilla — Landscaping ────
            [
                'first_name' => 'Grace', 'last_name' => 'Padilla', 'email' => 'grace.padilla@example.com', 'category' => 'Landscaping',
                'bio' => 'Experienced landscaper specializing in garden design, lawn care, and yard maintenance. Creates beautiful outdoor spaces for homes in Tuy.',
                'skills' => ['Lawn Mowing', 'Garden Design', 'Yard Cleanup', 'Planting'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 350, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9500, 'lng' => 120.7220, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 7: Christian Alonzo — Laundry ─────
            [
                'first_name' => 'Christian', 'last_name' => 'Alonzo', 'email' => 'christian.alonzo@example.com', 'category' => 'Laundry',
                'bio' => 'Professional laundry service provider. Offers wash & fold, dry cleaning, and ironing with pick-up and delivery. Reliable and careful with garments.',
                'skills' => ['Wash & Fold', 'Dry Cleaning', 'Ironing', 'Pick-up & Delivery'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 200, 'years_exp' => 4, 'radius_km' => 10,
                'days' => 'Monday — Friday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9530, 'lng' => 120.7180, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 8: Princess Joy David — Pest Control ──
            [
                'first_name' => 'Princess Joy', 'last_name' => 'David', 'email' => 'princess.david@example.com', 'category' => 'Pest Control',
                'bio' => 'Licensed pest control specialist. Handles fumigation, rodent control, termite treatment, and general pest prevention for homes and businesses.',
                'skills' => ['Fumigation', 'Rodent Control', 'Termite Treatment', 'Mosquito Control'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 6, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9480, 'lng' => 120.7280, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
            // ── Basic+ Provider 9: Reynaldo Navarro — Appliance Repair ──
            [
                'first_name' => 'Reynaldo', 'last_name' => 'Navarro', 'email' => 'reynaldo.navarro@example.com', 'category' => 'Appliance Repair',
                'bio' => 'Skilled appliance repair technician. Fixes refrigerators, washing machines, ovens, and other home appliances. Over 8 years of hands-on experience.',
                'skills' => ['Refrigerator Repair', 'Washing Machine Repair', 'Oven Repair', 'Electric Fan Repair'],
                'languages' => ['Filipino'],
                'hourly_rate' => 350, 'years_exp' => 8, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9580, 'lng' => 120.7320, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 10: Jasmine Torres — Masonry ──────
            [
                'first_name' => 'Jasmine', 'last_name' => 'Torres', 'email' => 'jasmine.torres@example.com', 'category' => 'Masonry',
                'bio' => 'Experienced mason specializing in tiling, concrete work, brick laying, and waterproofing. Quality craftsmanship for residential projects.',
                'skills' => ['Tiling', 'Concrete Repair', 'Brick Work', 'Waterproofing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 450, 'years_exp' => 7, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9540, 'lng' => 120.7260, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 11: Vincent Dizon — Personal Care ──
            [
                'first_name' => 'Vincent', 'last_name' => 'Dizon', 'email' => 'vincent.dizon@example.com', 'category' => 'Personal Care',
                'bio' => 'Licensed massage therapist and personal care professional. Offers home massage, manicure, pedicure, and grooming services. Relaxing and professional.',
                'skills' => ['Home Massage', 'Manicure', 'Pedicure', 'Facial'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 5, 'radius_km' => 10,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9510, 'lng' => 120.7240, 'verified' => true, 'rating' => 4.5, 'full' => false,
            ],
            // ── Basic+ Provider 12: Fatima Sotto — Moving Services ──
            [
                'first_name' => 'Fatima', 'last_name' => 'Sotto', 'email' => 'fatima.sotto@example.com', 'category' => 'Moving Services',
                'bio' => 'Reliable moving service provider. Offers packing, loading, local transport, and heavy item moving. Careful handling of your belongings guaranteed.',
                'skills' => ['Packing', 'Loading & Unloading', 'Local Transport', 'Heavy Item Moving'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 350, 'years_exp' => 6, 'radius_km' => 20,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 6 PM)',
                'lat' => 13.9570, 'lng' => 120.7290, 'verified' => true, 'rating' => 4.0, 'full' => false,
            ],
            // ── Basic+ Provider 13: Edgar Soriano — Plumbing ──────
            [
                'first_name' => 'Edgar', 'last_name' => 'Soriano', 'email' => 'edgar.soriano@example.com', 'category' => 'Plumbing',
                'bio' => 'Skilled plumber offering fast and reliable service. Handles leak repairs, pipe installations, and emergency plumbing. 6 years of experience.',
                'skills' => ['Leak Repair', 'Pipe Installation', 'Drain Cleaning', 'Faucet Replacement'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 6, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9590, 'lng' => 120.7310, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 14: Mary Grace Valenzuela — Cleaning ──
            [
                'first_name' => 'Mary Grace', 'last_name' => 'Valenzuela', 'email' => 'mg.valenzuela@example.com', 'category' => 'Cleaning',
                'bio' => 'Dedicated cleaning professional specializing in eco-friendly home cleaning, upholstery care, and post-construction cleanup. Meticulous and trustworthy.',
                'skills' => ['Deep Cleaning', 'Upholstery Cleaning', 'Post-Construction Cleaning', 'Pressure Washing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 320, 'years_exp' => 5, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9490, 'lng' => 120.7210, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Full Provider 15: Sofia Gomez — Painting ───────────
            [
                'first_name' => 'Sofia', 'last_name' => 'Gomez', 'email' => 'sofia.gomez@example.com', 'category' => 'Painting',
                'bio' => 'Creative painter with a keen eye for detail. Transforms homes with quality finishes. Specializes in interior and exterior painting, cabinet refinishing, and decorative finishes.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing', 'Ceiling Painting'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 6, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9520, 'lng' => 120.7200, 'verified' => true, 'rating' => 4.7, 'full' => true,
            ],
            // ── Basic+ Provider 16: Malaya Dimaculangan — Plumbing ──
            [
                'first_name' => 'Malaya', 'last_name' => 'Dimaculangan', 'email' => 'malaya.dimaculangan@example.com', 'category' => 'Plumbing',
                'bio' => 'Reliable plumber with 5 years of experience. Handles leak repairs, pipe installations, and emergency plumbing with fast response times.',
                'skills' => ['Leak Repair', 'Pipe Installation', 'Faucet Replacement', 'Drain Cleaning'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9561, 'lng' => 120.7281, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 17: Alab Macalintal — Electrical ──
            [
                'first_name' => 'Alab', 'last_name' => 'Macalintal', 'email' => 'alab.macalintal@example.com', 'category' => 'Electrical',
                'bio' => 'Skilled electrician specializing in wiring, lighting installation, and panel upgrades. Fast and thorough troubleshooting.',
                'skills' => ['Lighting Installation', 'Wiring & Rewiring', 'Outlet & Switch Repair', 'Ceiling Fan Installation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 500, 'years_exp' => 6, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9582, 'lng' => 120.7262, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 18: Tala Catacutan — Cleaning ──
            [
                'first_name' => 'Tala', 'last_name' => 'Catacutan', 'email' => 'tala.catacutan@example.com', 'category' => 'Cleaning',
                'bio' => 'Thorough cleaning professional with 4 years of experience. Deep cleaning, window washing, and upholstery care with eco-friendly products.',
                'skills' => ['Deep Cleaning', 'Window Cleaning', 'Carpet Shampooing', 'Upholstery Cleaning'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 280, 'years_exp' => 4, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9533, 'lng' => 120.7293, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 19: Kidlat Panganiban — Carpentry ──
            [
                'first_name' => 'Kidlat', 'last_name' => 'Panganiban', 'email' => 'kidlat.panganiban@example.com', 'category' => 'Carpentry',
                'bio' => 'Efficient carpenter skilled in furniture assembly, cabinet installation, and custom shelving. Known for precision and quality finishes.',
                'skills' => ['Furniture Assembly', 'Cabinet Installation', 'Custom Shelving', 'Door Repair & Installation'],
                'languages' => ['Filipino'],
                'hourly_rate' => 450, 'years_exp' => 7, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9554, 'lng' => 120.7314, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
            // ── Basic+ Provider 20: Mutya Magbanua — Painting ──
            [
                'first_name' => 'Mutya', 'last_name' => 'Magbanua', 'email' => 'mutya.magbanua@example.com', 'category' => 'Painting',
                'bio' => 'Creative painter with an eye for color. Offers interior and exterior painting, cabinet refinishing, and decorative finishes.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing', 'Ceiling Painting'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 380, 'years_exp' => 5, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9515, 'lng' => 120.7225, 'verified' => true, 'rating' => 4.0, 'full' => false,
            ],
            // ── Basic+ Provider 21: Lakan Dalisay — Plumbing ──
            [
                'first_name' => 'Lakan', 'last_name' => 'Dalisay', 'email' => 'lakan.dalisay@example.com', 'category' => 'Plumbing',
                'bio' => 'Experienced plumber offering drain cleaning, toilet installation, and water heater services. Committed to quality workmanship.',
                'skills' => ['Drain Cleaning', 'Toilet Repair & Installation', 'Water Heater Service', 'Pipe Installation'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 420, 'years_exp' => 6, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9576, 'lng' => 120.7276, 'verified' => true, 'rating' => 4.5, 'full' => false,
            ],
            // ── Basic+ Provider 22: Dakila Manalo — Electrical ──
            [
                'first_name' => 'Dakila', 'last_name' => 'Manalo', 'email' => 'dakila.manalo@example.com', 'category' => 'Electrical',
                'bio' => 'Licensed electrician with expertise in rewiring, generator repair, and smart home setup. Safety-focused and reliable.',
                'skills' => ['Electrical Inspection', 'Wiring & Rewiring', 'Generator Repair', 'Smart Home Setup'],
                'languages' => ['Filipino'],
                'hourly_rate' => 550, 'years_exp' => 7, 'radius_km' => 20,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9597, 'lng' => 120.7247, 'verified' => true, 'rating' => 4.6, 'full' => false,
            ],
            // ── Basic+ Provider 23: Hiraya Ilagan — Cleaning ──
            [
                'first_name' => 'Hiraya', 'last_name' => 'Ilagan', 'email' => 'hiraya.ilagan@example.com', 'category' => 'Cleaning',
                'bio' => 'Detail-oriented cleaner specializing in move-in/move-out and post-construction cleaning. Trusted by real estate agents.',
                'skills' => ['Move-in/out Cleaning', 'Post-Construction Cleaning', 'Office Cleaning', 'Pressure Washing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 320, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9548, 'lng' => 120.7308, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 24: Bituin Lualhati — Carpentry ──
            [
                'first_name' => 'Bituin', 'last_name' => 'Lualhati', 'email' => 'bituin.lualhati@example.com', 'category' => 'Carpentry',
                'bio' => 'Talented carpenter focused on custom furniture, trim work, and drywall repair. Brings creative solutions to every project.',
                'skills' => ['Custom Shelving', 'Trim & Molding', 'Drywall Repair', 'Furniture Assembly'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 4, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9529, 'lng' => 120.7339, 'verified' => true, 'rating' => 4.0, 'full' => false,
            ],
            // ── Basic+ Provider 25: Isagani Bayani — Painting ──
            [
                'first_name' => 'Isagani', 'last_name' => 'Bayani', 'email' => 'isagani.bayani@example.com', 'category' => 'Painting',
                'bio' => 'Dependable painter skilled in exterior painting, fence painting, and garage floor coating. High-quality finishes every time.',
                'skills' => ['Exterior Painting', 'Fence Painting', 'Garage Floor Coating', 'Wallpaper Removal'],
                'languages' => ['Filipino'],
                'hourly_rate' => 350, 'years_exp' => 6, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9530, 'lng' => 120.7210, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 26: Makisig Dimagiba — Aircon ──
            [
                'first_name' => 'Makisig', 'last_name' => 'Dimagiba', 'email' => 'makisig.dimagiba@example.com', 'category' => 'Aircon',
                'bio' => 'HVAC technician with 5 years of experience. AC cleaning, repair, installation, and gas refill for all major brands.',
                'skills' => ['AC Cleaning', 'AC Repair', 'Filter Replacement', 'Gas Refill'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 480, 'years_exp' => 5, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9611, 'lng' => 120.7311, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
            // ── Basic+ Provider 27: Amihan Batumbakal — Landscaping ──
            [
                'first_name' => 'Amihan', 'last_name' => 'Batumbakal', 'email' => 'amihan.batumbakal@example.com', 'category' => 'Landscaping',
                'bio' => 'Passionate landscaper specializing in garden design, tree trimming, and yard cleanup. Creates beautiful outdoor living spaces.',
                'skills' => ['Lawn Mowing', 'Tree Trimming', 'Hedge Trimming', 'Yard Cleanup'],
                'languages' => ['Filipino'],
                'hourly_rate' => 300, 'years_exp' => 4, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9492, 'lng' => 120.7232, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 28: Mithi Agbayani — Laundry ──
            [
                'first_name' => 'Mithi', 'last_name' => 'Agbayani', 'email' => 'mithi.agbayani@example.com', 'category' => 'Laundry',
                'bio' => 'Laundry care specialist with 3 years of experience. Wash & fold, dry cleaning, ironing, and blanket washing with pick-up and delivery.',
                'skills' => ['Wash & Fold', 'Dry Cleaning', 'Ironing & Pressing', 'Blanket & Comforter Wash'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 180, 'years_exp' => 3, 'radius_km' => 10,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9523, 'lng' => 120.7193, 'verified' => true, 'rating' => 4.0, 'full' => false,
            ],
            // ── Basic+ Provider 29: Sinag Mabini — Pest Control ──
            [
                'first_name' => 'Sinag', 'last_name' => 'Mabini', 'email' => 'sinag.mabini@example.com', 'category' => 'Pest Control',
                'bio' => 'Licensed pest control technician offering fumigation, rodent control, mosquito control, and preventive spraying for homes.',
                'skills' => ['General Fumigation', 'Rodent Control', 'Mosquito Control', 'Preventive Spraying'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 380, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9474, 'lng' => 120.7274, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 30: Alon Silang — Appliance Repair ──
            [
                'first_name' => 'Alon', 'last_name' => 'Silang', 'email' => 'alon.silang@example.com', 'category' => 'Appliance Repair',
                'bio' => 'Skilled appliance repair technician. Fixes refrigerators, washing machines, electric fans, and microwaves. Fast turnaround.',
                'skills' => ['Refrigerator Repair', 'Washing Machine Repair', 'Electric Fan Repair', 'Microwave Repair'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 350, 'years_exp' => 6, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9585, 'lng' => 120.7335, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 31: Bagwis Balagtas — Masonry ──
            [
                'first_name' => 'Bagwis', 'last_name' => 'Balagtas', 'email' => 'bagwis.balagtas@example.com', 'category' => 'Masonry',
                'bio' => 'Experienced mason offering tiling, concrete repair, brick work, and waterproofing. Quality results for residential projects.',
                'skills' => ['Tiling', 'Concrete Repair', 'Brick & Block Work', 'Wall Repointing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 420, 'years_exp' => 7, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9546, 'lng' => 120.7256, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
            // ── Basic+ Provider 32: Kislap Tolosa — Personal Care ──
            [
                'first_name' => 'Kislap', 'last_name' => 'Tolosa', 'email' => 'kislap.tolosa@example.com', 'category' => 'Personal Care',
                'bio' => 'Certified massage therapist and beauty care specialist. Offers home massage, manicure, pedicure, and facial services.',
                'skills' => ['Home Massage', 'Manicure', 'Pedicure', 'Facial'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 280, 'years_exp' => 4, 'radius_km' => 10,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9507, 'lng' => 120.7237, 'verified' => true, 'rating' => 4.5, 'full' => false,
            ],
            // ── Basic+ Provider 33: Marikit Alcantara — Moving Services ──
            [
                'first_name' => 'Marikit', 'last_name' => 'Alcantara', 'email' => 'marikit.alcantara@example.com', 'category' => 'Moving Services',
                'bio' => 'Dependable mover with 4 years of experience. Packing, loading, local transport, and heavy item moving. Careful and efficient.',
                'skills' => ['Packing Assistance', 'Loading & Unloading', 'Local Transport', 'Heavy Item Moving'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 320, 'years_exp' => 4, 'radius_km' => 20,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 6 PM)',
                'lat' => 13.9568, 'lng' => 120.7288, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 34: Alaya Gatchalian — Plumbing ──
            [
                'first_name' => 'Alaya', 'last_name' => 'Gatchalian', 'email' => 'alaya.gatchalian@example.com', 'category' => 'Plumbing',
                'bio' => 'Professional plumber with experience in sewer line cleaning, garbage disposal repair, and complete bathroom renovations.',
                'skills' => ['Sewer Line Cleaning', 'Garbage Disposal Repair', 'Toilet Repair & Installation', 'Leak Repair'],
                'languages' => ['Filipino'],
                'hourly_rate' => 380, 'years_exp' => 5, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9579, 'lng' => 120.7269, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 35: Datu Macaraig — Electrical ──
            [
                'first_name' => 'Datu', 'last_name' => 'Macaraig', 'email' => 'datu.macaraig@example.com', 'category' => 'Electrical',
                'bio' => 'Experienced electrician skilled in panel upgrades, generator repair, and smart home automation. Committed to safe installations.',
                'skills' => ['Panel Upgrade', 'Generator Repair', 'Smart Home Setup', 'Electrical Inspection'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 520, 'years_exp' => 6, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9603, 'lng' => 120.7253, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 36: Magiting Magsaysay — Cleaning ──
            [
                'first_name' => 'Magiting', 'last_name' => 'Magsaysay', 'email' => 'magiting.magsaysay@example.com', 'category' => 'Cleaning',
                'bio' => 'Thorough cleaning expert specializing in deep cleaning, carpet shampooing, and upholstery care. Eco-friendly products used.',
                'skills' => ['Deep Cleaning', 'Carpet Shampooing', 'Upholstery Cleaning', 'Window Cleaning'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 5, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9537, 'lng' => 120.7307, 'verified' => true, 'rating' => 4.0, 'full' => false,
            ],
            // ── Basic+ Provider 37: Tadhana Pangilinan — Carpentry ──
            [
                'first_name' => 'Tadhana', 'last_name' => 'Pangilinan', 'email' => 'tadhana.pangilinan@example.com', 'category' => 'Carpentry',
                'bio' => 'Carpentry professional specializing in kitchen cabinet refacing, deck repair, and custom shelving. Over 6 years of experience.',
                'skills' => ['Kitchen Cabinet Refacing', 'Deck Repair', 'Custom Shelving', 'Cabinet Installation'],
                'languages' => ['Filipino'],
                'hourly_rate' => 480, 'years_exp' => 6, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9563, 'lng' => 120.7343, 'verified' => true, 'rating' => 4.5, 'full' => false,
            ],
            // ── Basic+ Provider 38: Luningning Tupaz — Painting ──
            [
                'first_name' => 'Luningning', 'last_name' => 'Tupaz', 'email' => 'luningning.tupaz@example.com', 'category' => 'Painting',
                'bio' => 'Talented painter offering interior and exterior painting, ceiling painting, and wallpaper removal. Attention to detail guaranteed.',
                'skills' => ['Interior Painting', 'Ceiling Painting', 'Wallpaper Removal', 'Exterior Painting'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 370, 'years_exp' => 4, 'radius_km' => 12,
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9524, 'lng' => 120.7214, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 39: Diwa Velasco — Aircon ──
            [
                'first_name' => 'Diwa', 'last_name' => 'Velasco', 'email' => 'diwa.velasco@example.com', 'category' => 'Aircon',
                'bio' => 'HVAC specialist with 5 years of experience in AC installation, duct cleaning, and compressor repair. Certified and insured.',
                'skills' => ['AC Installation', 'Duct Cleaning', 'Compressor Repair', 'AC Cleaning'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 500, 'years_exp' => 5, 'radius_km' => 18,
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 6 PM)',
                'lat' => 13.9615, 'lng' => 120.7315, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
            // ── Basic+ Provider 40: Sampaguita Yuzon — Landscaping ──
            [
                'first_name' => 'Sampaguita', 'last_name' => 'Yuzon', 'email' => 'sampaguita.yuzon@example.com', 'category' => 'Landscaping',
                'bio' => 'Creative landscaper specializing in planting, garden design, and lot clearing. Transforms outdoor spaces into beautiful gardens.',
                'skills' => ['Planting & Gardening', 'Garden Design', 'Lot Clearing', 'Yard Cleanup'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 350, 'years_exp' => 6, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9506, 'lng' => 120.7226, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 41: Ligaya Zapanta — Laundry ──
            [
                'first_name' => 'Ligaya', 'last_name' => 'Zapanta', 'email' => 'ligaya.zapanta@example.com', 'category' => 'Laundry',
                'bio' => 'Laundry service provider with careful handling of garments. Wash & fold, dry cleaning, ironing, and curtain cleaning.',
                'skills' => ['Wash & Fold', 'Dry Cleaning', 'Ironing & Pressing', 'Curtain Cleaning'],
                'languages' => ['Filipino'],
                'hourly_rate' => 170, 'years_exp' => 3, 'radius_km' => 10,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9527, 'lng' => 120.7197, 'verified' => true, 'rating' => 3.9, 'full' => false,
            ],
            // ── Basic+ Provider 42: Biyaya Halili — Pest Control ──
            [
                'first_name' => 'Biyaya', 'last_name' => 'Halili', 'email' => 'biyaya.halili@example.com', 'category' => 'Pest Control',
                'bio' => 'Pest control specialist handling termite treatment, ant & roach removal, and general fumigation. Safe and effective methods.',
                'skills' => ['Termite Treatment', 'Ant & Roach Removal', 'General Fumigation', 'Preventive Spraying'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 400, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9478, 'lng' => 120.7278, 'verified' => true, 'rating' => 4.2, 'full' => false,
            ],
            // ── Basic+ Provider 43: Liwanag Manansala — Appliance Repair ──
            [
                'first_name' => 'Liwanag', 'last_name' => 'Manansala', 'email' => 'liwanag.manansala@example.com', 'category' => 'Appliance Repair',
                'bio' => 'Dependable appliance repair technician. Fixes ovens, dryers, water dispensers, and refrigerators. Prompt and professional.',
                'skills' => ['Oven & Stove Repair', 'Dryer Repair', 'Water Dispenser Repair', 'Refrigerator Repair'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 380, 'years_exp' => 5, 'radius_km' => 15,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9589, 'lng' => 120.7339, 'verified' => true, 'rating' => 4.1, 'full' => false,
            ],
            // ── Basic+ Provider 44: Dalisay Polistico — Masonry ──
            [
                'first_name' => 'Dalisay', 'last_name' => 'Polistico', 'email' => 'dalisay.polistico@example.com', 'category' => 'Masonry',
                'bio' => 'Skilled mason offering tiling, pavement work, and waterproofing. Quality craftsmanship for both indoor and outdoor projects.',
                'skills' => ['Tiling', 'Pavement & Walkway', 'Waterproofing', 'Concrete Repair'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 430, 'years_exp' => 6, 'radius_km' => 18,
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
                'lat' => 13.9549, 'lng' => 120.7259, 'verified' => true, 'rating' => 4.3, 'full' => false,
            ],
            // ── Basic+ Provider 45: Yumi Sumulong — Personal Care ──
            [
                'first_name' => 'Yumi', 'last_name' => 'Sumulong', 'email' => 'yumi.sumulong@example.com', 'category' => 'Personal Care',
                'bio' => 'Licensed beauty care professional. Offers home massage, haircut & blow-dry, waxing, and facial treatments. Relaxing and hygienic service.',
                'skills' => ['Home Massage', 'Haircut & Blow-dry', 'Waxing', 'Facial'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 4, 'radius_km' => 12,
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
                'lat' => 13.9511, 'lng' => 120.7241, 'verified' => true, 'rating' => 4.4, 'full' => false,
            ],
        ];

        $workerUsers = [];
        $workerProfiles = [];
        foreach ($workersData as $i => $data) {
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
                'user_id'                => $user->id,
                'bio'                    => $data['bio'],
                'skills'                 => $data['skills'],
                'spoken_languages'       => $data['languages'],
                'hourly_rate'            => $data['hourly_rate'],
                'available_days'         => $data['days'],
                'preferred_hours'        => $data['hours'],
                'service_areas'          => $barangays,
                'years_of_experience'    => $data['years_exp'],
                'service_radius_km'      => $data['radius_km'],
                'government_id_verified' => $data['verified'],
                'average_rating'         => $data['rating'],
                'current_latitude'       => $data['lat'],
                'current_longitude'      => $data['lng'],
            ]);

            $workerUsers[] = $user;
            $workerProfiles[] = $profile;
        }

        // ═══════════════════════════════════════════════════════════
        //  6. PROVIDER SERVICES
        // ═══════════════════════════════════════════════════════════

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

        // ═══════════════════════════════════════════════════════════
        //  7. BOOKINGS (generated per provider)
        // ═══════════════════════════════════════════════════════════
        //
        //  Config: [provider_index => [status => count]]
        //  Full providers (0-5,15):  6 completed + 1 each other
        //  Basic+ providers (6-14,16-45): 2-3 completed + 0-1 other

        $bookingDefs = [
            0  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            1  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            2  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            3  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            4  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            5  => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            6  => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            7  => ['completed' => 3, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            8  => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            9  => ['completed' => 2, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 0],
            10 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            11 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 1],
            12 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 0],
            13 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            14 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            15 => ['completed' => 6, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            // ── New workers (16-45) ─────────────────────────────
            16 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            17 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 1, 'new' => 1],
            18 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            19 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            20 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            21 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 1, 'new' => 0],
            22 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            23 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            24 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 1, 'new' => 0],
            25 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            26 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 0],
            27 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 1],
            28 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 0],
            29 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            30 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            31 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            32 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 0, 'new' => 0],
            33 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 0, 'new' => 1],
            34 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 0],
            35 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 1],
            36 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 0],
            37 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            38 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            39 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            40 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            41 => ['completed' => 2, 'in_progress' => 1, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            42 => ['completed' => 3, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 1],
            43 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 1, 'accepted' => 0, 'new' => 0],
            44 => ['completed' => 3, 'in_progress' => 1, 'en_route' => 0, 'accepted' => 0, 'new' => 1],
            45 => ['completed' => 2, 'in_progress' => 0, 'en_route' => 0, 'accepted' => 1, 'new' => 0],
        ];

        $statusMap = [
            'completed'   => Booking::STATUS_COMPLETED,
            'in_progress' => Booking::STATUS_IN_PROGRESS,
            'en_route'    => Booking::STATUS_EN_ROUTE,
            'accepted'    => Booking::STATUS_ACCEPTED,
            'new'         => Booking::STATUS_NEW,
        ];

        $serviceNamesByCategory = [];
        foreach ($servicesData as $catSlug => $svcs) {
            $cat = ServiceCategory::where('slug', $catSlug)->first();
            if ($cat) {
                $serviceNamesByCategory[$cat->name] = array_column($svcs, 'name');
            }
        }

        $completedBookings = [];

        foreach ($bookingDefs as $wi => $counts) {
            $workerUser = $workerUsers[$wi];
            $workerData = $workersData[$wi];
            $categoryName = $workerData['category'];
            $serviceNames = $serviceNamesByCategory[$categoryName] ?? ['General Service'];

            foreach ($counts as $statusKey => $count) {
                for ($b = 0; $b < $count; $b++) {
                    $isCompleted = $statusKey === 'completed';
                    $clientId = $clientUsers[array_rand($clientUsers)]->id;
                    $clientIdx = array_rand($clientUsers);
                    $barangay = $barangays[array_rand($barangays)];
                    $serviceName = $serviceNames[array_rand($serviceNames)];
                    $daysAgo = $isCompleted ? rand(3, 45) : 0;
                    $hoursFromNow = $isCompleted ? 0 : rand(1, 72);

                    $scheduled = $isCompleted
                        ? now()->subDays($daysAgo)->setHour(rand(8, 16))
                        : now()->addHours($hoursFromNow);

                    $completed = $isCompleted
                        ? $scheduled->copy()->addHours(rand(2, 5))
                        : null;

                    $price = $isCompleted
                        ? round(($workerData['hourly_rate'] * rand(2, 6)) + rand(0, 99), 2)
                        : round($workerData['hourly_rate'] * rand(1, 4), 2);

                    $booking = Booking::create([
                        'client_id'        => $clientId,
                        'worker_id'        => $workerUser->id,
                        'service_category' => $serviceName,
                        'address'          => $barangay . ', Tuy, Batangas',
                        'scheduled_at'     => $scheduled,
                        'price'            => $price,
                        'status'           => $statusMap[$statusKey],
                        'completed_at'     => $completed,
                    ]);

                    if ($isCompleted) {
                        $completedBookings[] = $booking;
                    }
                }
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  7b. DEDICATED BOOKINGS — Maria Santos (client index 1)
        // ═══════════════════════════════════════════════════════════

        $maria = $clientUsers[1];
        $mariaBookings = [
            // Completed bookings spread across different workers
            ['worker' => 0,  'service' => 'Bathroom Pipe Repair',    'addr' => 2,  'price' => 850,  'days_ago' => 8],
            ['worker' => 1,  'service' => 'Full Home Deep Cleaning', 'addr' => 7,  'price' => 1500, 'days_ago' => 5],
            ['worker' => 4,  'service' => 'Interior Room Painting',  'addr' => 11, 'price' => 1200, 'days_ago' => 12],
            ['worker' => 5,  'service' => 'AC Cleaning & Check-up',  'addr' => 3,  'price' => 600,  'days_ago' => 10],
            ['worker' => 15, 'service' => 'Living Room Repaint',     'addr' => 15, 'price' => 2000, 'days_ago' => 6],
            // In-progress
            ['worker' => 2,  'service' => 'Electrical Rewiring',     'addr' => 9,  'price' => 3500, 'days_ago' => 0],
            // En-route
            ['worker' => 3,  'service' => 'Cabinet Installation',    'addr' => 18, 'price' => 1800, 'days_ago' => 0],
            // Accepted
            ['worker' => 8,  'service' => 'Pest Control Treatment',  'addr' => 5,  'price' => 900,  'days_ago' => 0],
            // New
            ['worker' => 7,  'service' => 'Laundry Service Weekly',  'addr' => 20, 'price' => 600,  'days_ago' => 0],
        ];

        $mariaStatuses = [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_EN_ROUTE,
            Booking::STATUS_ACCEPTED,
            Booking::STATUS_NEW,
        ];

        foreach ($mariaBookings as $i => $data) {
            $isComplete = $mariaStatuses[$i] === Booking::STATUS_COMPLETED;
            $scheduled = $isComplete
                ? now()->subDays($data['days_ago'])->setHour(rand(8, 16))
                : now()->addHours(rand(2, 48));
            $completed = $isComplete
                ? $scheduled->copy()->addHours(rand(2, 5))
                : null;

            $booking = Booking::create([
                'client_id'        => $maria->id,
                'worker_id'        => $workerUsers[$data['worker']]->id,
                'service_category' => $data['service'],
                'address'          => $barangays[$data['addr']] . ', Tuy, Batangas',
                'scheduled_at'     => $scheduled,
                'price'            => $data['price'],
                'status'           => $mariaStatuses[$i],
                'completed_at'     => $completed,
            ]);

            if ($isComplete) {
                $completedBookings[] = $booking;
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  8. REVIEWS for all completed bookings
        // ═══════════════════════════════════════════════════════════

        $reviewComments = [
            'Excellent service! Very professional and on time.',
            'Great work, highly recommended. Will book again.',
            'Very satisfied with the quality of work.',
            'Did an amazing job. Fair pricing too.',
            'Professional and efficient. Thank you!',
            'Good quality work. Would recommend to friends.',
            'Satisfied with the service. On time and polite.',
            'Decent work for the price. No complaints.',
            'Very helpful and skilled. Great experience overall.',
            'Quick response and excellent craftsmanship.',
        ];

        $reviewCommentsFilipino = [
            'Magaling po gumawa. Satisfied ako sa resulta.',
            'Salamat sa mahusay na serbisyo!',
            'Maayos at mabilis ang trabaho. Sulit ang bayad.',
            'Propesyonal at magalang. Babalik-balikan ko ito.',
            'Napakaganda ng pagkakagawa. Siguradong recommend ko sa iba.',
        ];

        foreach ($completedBookings as $booking) {
            $rating = rand(3, 5);
            $comment = $rating >= 4
                ? ($reviewComments[array_rand($reviewComments)])
                : ($reviewCommentsFilipino[array_rand($reviewCommentsFilipino)]);

            Review::create([
                'booking_id' => $booking->id,
                'client_id'  => $booking->client_id,
                'worker_id'  => $booking->worker_id,
                'rating'     => $rating,
                'comment'    => $comment,
                'created_at' => $booking->completed_at ?? $booking->scheduled_at->copy()->addHours(rand(2, 5)),
                'updated_at' => $booking->completed_at ?? $booking->scheduled_at->copy()->addHours(rand(2, 5)),
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        //  9. EARNINGS for all completed bookings
        // ═══════════════════════════════════════════════════════════

        foreach ($completedBookings as $booking) {
            $gross = $booking->price;
            $fee = round($gross * ($platformFeePercent / 100), 2);
            $net = $gross - $fee;

            Earning::create([
                'worker_id'    => $booking->worker_id,
                'booking_id'   => $booking->id,
                'gross_amount' => $gross,
                'platform_fee' => $fee,
                'net_amount'   => $net,
                'paid_at'      => $booking->completed_at,
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        //  10. PORTFOLIO ITEMS (7 full providers)
        // ═══════════════════════════════════════════════════════════

        $portfolioCaptions = [
            'Plumbing' => [
                'Complete bathroom pipe installation and fixture setup',
                'Water heater replacement for residential home',
                'Kitchen sink drainage system overhaul',
                'Main water line repair and pressure adjustment',
                'Full bathroom plumbing renovation project',
            ],
            'Cleaning' => [
                'Deep cleaning of three-bedroom home after renovation',
                'Move-in cleaning service for new homeowners',
                'Complete upholstery and carpet deep cleaning',
                'Post-construction debris removal and cleaning',
                'Office space sanitation and disinfection service',
            ],
            'Electrical' => [
                'Complete house rewiring and panel upgrade',
                'Smart home lighting and automation installation',
                'Ceiling fan and light fixture installation project',
                'Generator backup system wiring and setup',
                'Commercial building electrical inspection and repair',
            ],
            'Carpentry' => [
                'Custom kitchen cabinets — from design to installation',
                'Built-in wall shelving and entertainment center',
                'Solid wood dining table and chair set',
                'Door and window frame replacement project',
                'Deck repair and wood restoration',
            ],
            'Painting' => [
                'Living room interior — warm neutral color scheme',
                'Exterior facade repaint — two-storey home',
                'Cabinet refinishing from dark wood to modern white',
                'Bedroom accent wall with textured finish',
                'Full interior repaint before move-in',
            ],
            'Aircon' => [
                'Central AC installation for residential home',
                'Split-type AC cleaning and gas refill service',
                'Window-type AC repair and compressor replacement',
                'Duct cleaning and air quality improvement',
                'Multi-split AC system installation for office',
            ],
        ];

        $fullProviderIndexes = [0, 1, 2, 3, 4, 5, 15];
        $fullProviderCategories = ['Plumbing', 'Cleaning', 'Electrical', 'Carpentry', 'Painting', 'Aircon', 'Painting'];

        foreach ($fullProviderIndexes as $i => $fpIdx) {
            $profile = $workerProfiles[$fpIdx];
            $cat = $fullProviderCategories[$i];
            $captions = $portfolioCaptions[$cat];

            foreach ($captions as $caption) {
                $profile->portfolios()->create([
                    'photo_path' => 'portfolios/worker-' . ($fpIdx + 1) . '-sample-' . rand(100, 999) . '.jpg',
                    'caption'    => $caption,
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  11. VERIFIED DOCUMENTS (7 full providers)
        // ═══════════════════════════════════════════════════════════

        $docConfigs = [
            ['type' => 'Government-Issued ID',       'status' => 'verified'],
            ['type' => 'NBI Clearance',               'status' => 'verified'],
            ['type' => 'Barangay Clearance',          'status' => 'verified'],
            ['type' => 'Proof of Competency',         'status' => 'verified'],
            ['type' => 'Professional License',        'status' => 'verified'],
        ];

        foreach ($fullProviderIndexes as $fpIdx) {
            $workerUser = $workerUsers[$fpIdx];
            foreach ($docConfigs as $doc) {
                $workerUser->workerDocuments()->create([
                    'document_type' => $doc['type'],
                    'file_path'     => 'documents/worker-' . ($fpIdx + 1) . '-' . str_replace(' ', '-', strtolower($doc['type'])) . '.pdf',
                    'status'        => $doc['status'],
                    'verified_at'   => now()->subDays(rand(10, 60)),
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════
        //  12. MESSAGES for all bookings
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

        // ═══════════════════════════════════════════════════════════
        //  SUMMARY
        // ═══════════════════════════════════════════════════════════

        $this->command->info('Database seeded successfully!');
        $this->command->info('Categories: ' . ServiceCategory::count() . ' | Services: ' . Service::count());
        $this->command->info('Admin:  admin@kaayos.com / password');
        $this->command->info('Clients: ' . User::where('role', 'client')->count() . ' users | Workers: ' . User::where('role', 'worker')->count() . ' users');
        $this->command->info('Bookings: ' . Booking::count() . ' | Reviews: ' . Review::count() . ' | Earnings: ' . Earning::count());
    }
}
