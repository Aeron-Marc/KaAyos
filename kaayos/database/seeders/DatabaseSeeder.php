<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\BookingQuote;
use App\Models\BookingWorker;
use App\Models\ClientAddress;
use App\Models\Conversation;
use App\Models\Dispute;
use App\Models\Message;
use App\Models\ProviderService;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Models\WorkerProfile;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');
        $faker->seed(42);

        $start = Carbon::now()->subMonths(2);
        $end   = Carbon::now();
        $pass  = Hash::make('password');

        $barangays = [
            'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
            'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
            'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
            'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
        ];

        $randDate = fn () => Carbon::instance($faker->dateTimeBetween($start, $end));
        $logs     = [];
        $log      = function (string $table, int $count) use (&$logs) {
            $logs[$table] = $count;
        };

        // Clean dummy users and related data on re-run (keep admin)
        User::where('role', '!=', 'admin')->delete();
        Testimonial::query()->delete();

        // ═════════════════════════════════════════════════════════════
        //  1. SERVICE CATEGORIES
        // ═════════════════════════════════════════════════════════════

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
            ServiceCategory::firstOrCreate(['slug' => $data['slug']], $data);
        }
        $log('service_categories', ServiceCategory::count());

        // ═════════════════════════════════════════════════════════════
        //  2. SERVICES
        // ═════════════════════════════════════════════════════════════

        $servicesData = [
            'plumbing' => [
                ['name' => 'Leak Repair', 'slug' => 'leak-repair', 'base_price' => 350],
                ['name' => 'Pipe Installation', 'slug' => 'pipe-installation', 'base_price' => 500],
                ['name' => 'Water Heater Service', 'slug' => 'water-heater-service', 'base_price' => 450],
                ['name' => 'Drain Cleaning', 'slug' => 'drain-cleaning', 'base_price' => 300],
                ['name' => 'Toilet Repair & Installation', 'slug' => 'toilet-repair-installation', 'base_price' => 400],
                ['name' => 'Faucet Replacement', 'slug' => 'faucet-replacement', 'base_price' => 250],
            ],
            'electrical' => [
                ['name' => 'Electrical Inspection', 'slug' => 'electrical-inspection', 'base_price' => 400],
                ['name' => 'Wiring & Rewiring', 'slug' => 'wiring-rewiring', 'base_price' => 600],
                ['name' => 'Lighting Installation', 'slug' => 'lighting-installation', 'base_price' => 300],
                ['name' => 'Outlet & Switch Repair', 'slug' => 'outlet-switch-repair', 'base_price' => 250],
                ['name' => 'Ceiling Fan Installation', 'slug' => 'ceiling-fan-installation', 'base_price' => 400],
                ['name' => 'Panel Upgrade', 'slug' => 'panel-upgrade', 'base_price' => 1000],
            ],
            'cleaning' => [
                ['name' => 'Deep Cleaning', 'slug' => 'deep-cleaning', 'base_price' => 300],
                ['name' => 'Move-in/out Cleaning', 'slug' => 'move-in-out-cleaning', 'base_price' => 500],
                ['name' => 'Window Cleaning', 'slug' => 'window-cleaning', 'base_price' => 200],
                ['name' => 'Carpet Shampooing', 'slug' => 'carpet-shampooing', 'base_price' => 400],
                ['name' => 'Office Cleaning', 'slug' => 'office-cleaning', 'base_price' => 400],
            ],
            'carpentry' => [
                ['name' => 'Furniture Assembly', 'slug' => 'furniture-assembly', 'base_price' => 350],
                ['name' => 'Cabinet Installation', 'slug' => 'cabinet-installation', 'base_price' => 550],
                ['name' => 'Custom Shelving', 'slug' => 'custom-shelving', 'base_price' => 400],
                ['name' => 'Door Repair & Installation', 'slug' => 'door-repair-installation', 'base_price' => 450],
            ],
            'painting' => [
                ['name' => 'Interior Painting', 'slug' => 'interior-painting', 'base_price' => 450],
                ['name' => 'Exterior Painting', 'slug' => 'exterior-painting', 'base_price' => 600],
                ['name' => 'Cabinet Refinishing', 'slug' => 'cabinet-refinishing', 'base_price' => 350],
            ],
            'aircon' => [
                ['name' => 'AC Cleaning', 'slug' => 'ac-cleaning', 'base_price' => 350],
                ['name' => 'AC Repair', 'slug' => 'ac-repair', 'base_price' => 500],
                ['name' => 'AC Installation', 'slug' => 'ac-installation', 'base_price' => 800],
                ['name' => 'Gas Refill', 'slug' => 'gas-refill', 'base_price' => 500],
            ],
            'landscaping' => [
                ['name' => 'Lawn Mowing', 'slug' => 'lawn-mowing', 'base_price' => 250],
                ['name' => 'Tree Trimming', 'slug' => 'tree-trimming', 'base_price' => 500],
                ['name' => 'Hedge Trimming', 'slug' => 'hedge-trimming', 'base_price' => 300],
                ['name' => 'Yard Cleanup', 'slug' => 'yard-cleanup', 'base_price' => 400],
            ],
            'laundry' => [
                ['name' => 'Wash & Fold', 'slug' => 'wash-fold', 'base_price' => 150],
                ['name' => 'Dry Cleaning', 'slug' => 'dry-cleaning', 'base_price' => 200],
                ['name' => 'Ironing & Pressing', 'slug' => 'ironing-pressing', 'base_price' => 120],
            ],
            'pest-control' => [
                ['name' => 'General Fumigation', 'slug' => 'general-fumigation', 'base_price' => 500],
                ['name' => 'Rodent Control', 'slug' => 'rodent-control', 'base_price' => 400],
                ['name' => 'Termite Treatment', 'slug' => 'termite-treatment', 'base_price' => 700],
            ],
            'appliance-repair' => [
                ['name' => 'Refrigerator Repair', 'slug' => 'refrigerator-repair', 'base_price' => 500],
                ['name' => 'Washing Machine Repair', 'slug' => 'washing-machine-repair', 'base_price' => 450],
                ['name' => 'Oven & Stove Repair', 'slug' => 'oven-stove-repair', 'base_price' => 400],
            ],
            'masonry' => [
                ['name' => 'Tiling', 'slug' => 'tiling', 'base_price' => 500],
                ['name' => 'Concrete Repair', 'slug' => 'concrete-repair', 'base_price' => 600],
                ['name' => 'Brick & Block Work', 'slug' => 'brick-block-work', 'base_price' => 550],
            ],
            'personal-care' => [
                ['name' => 'Home Massage', 'slug' => 'home-massage', 'base_price' => 400],
                ['name' => 'Manicure', 'slug' => 'manicure', 'base_price' => 150],
                ['name' => 'Pedicure', 'slug' => 'pedicure', 'base_price' => 180],
            ],
            'moving-services' => [
                ['name' => 'Packing Assistance', 'slug' => 'packing-assistance', 'base_price' => 300],
                ['name' => 'Loading & Unloading', 'slug' => 'loading-unloading', 'base_price' => 350],
                ['name' => 'Local Transport', 'slug' => 'local-transport', 'base_price' => 500],
            ],
        ];

        foreach ($servicesData as $catSlug => $services) {
            $category = ServiceCategory::where('slug', $catSlug)->first();
            if (! $category) {
                continue;
            }
            foreach ($services as $data) {
                Service::firstOrCreate(
                    ['slug' => $data['slug']],
                    array_merge($data, ['category_id' => $category->id, 'is_active' => true])
                );
            }
        }
        $log('services', Service::count());

        // ═════════════════════════════════════════════════════════════
        //  3. ADMIN USER (not dummy, email stays @kaayos.tech)
        // ═════════════════════════════════════════════════════════════

        User::firstOrCreate(
            ['email' => 'admin@kaayos.tech'],
            [
                'first_name'        => 'Admin',
                'last_name'         => 'KaAyos',
                'name'              => 'Admin KaAyos',
                'password'          => $pass,
                'role'              => 'admin',
                'city'              => 'Tuy, Batangas',
                'language'          => 'English',
                'email_verified_at' => $randDate(),
                'created_at'        => $randDate(),
                'updated_at'        => $randDate(),
            ]
        );

        // ═════════════════════════════════════════════════════════════
        //  4. CLIENT USERS (dummy — all emails @gmail.com)
        // ═════════════════════════════════════════════════════════════

        $clientNames = [
            ['Juan', 'Dela Cruz'], ['Maria', 'Santos'], ['Mark Anthony', 'Reyes'],
            ['John Paul', 'Mendoza'], ['Maricel', 'Bautista'], ['Jose', 'Garcia'],
            ['Luzviminda', 'Flores'], ['Jericho', 'Cruz'], ['Jayson', 'Villanueva'],
            ['Niño', 'Ramos'], ['Joy', 'Gonzales'], ['Crisanto', 'Aquino'],
            ['Mayumi', 'Fernandez'], ['Diwata', 'Perez'], ['Francisco', 'Mercado'],
        ];

        $clientUsers = [];
        foreach ($clientNames as $i => [$first, $last]) {
            $email = strtolower(str_replace(' ', '.', "$first.$last")) . '@gmail.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $first,
                    'last_name'         => $last,
                    'name'              => "$first $last",
                    'password'          => $pass,
                    'role'              => 'client',
                    'city'              => 'Tuy, Batangas',
                    'barangay'          => $barangays[$i % count($barangays)],
                    'language'          => 'English',
                    'phone'             => $faker->phoneNumber,
                    'email_verified_at' => $randDate(),
                    'created_at'        => $randDate(),
                    'updated_at'        => $randDate(),
                ]
            );
            $clientUsers[] = $user;
        }
        $log('users (clients)', User::where('role', 'client')->count());

        // ═════════════════════════════════════════════════════════════
        //  5. WORKER USERS & PROFILES (dummy — all emails @gmail.com)
        // ═════════════════════════════════════════════════════════════

        $workersData = [
            ['Divina', 'Lopez', 'Plumbing', 'Licensed master plumber with over 10 years of experience.', ['Pipe Repair', 'Water Heater', 'Drain Unblocking'], ['Filipino', 'English'], 450, 10, 15, true, 4.8],
            ['Cherry Gil', 'Pascual', 'Cleaning', 'Professional cleaner with 8 years of experience.', ['Deep Cleaning', 'Move-in/Move-out', 'Window Cleaning'], ['Filipino', 'English'], 300, 8, 12, true, 4.7],
            ['Ferdinand', 'Ocampo', 'Electrical', 'Licensed professional electrician with 12 years in residential and commercial wiring.', ['Wiring', 'Panel Upgrades', 'Lighting Setup'], ['Filipino', 'English'], 600, 12, 20, true, 4.9],
            ['Roberto', 'Castro', 'Carpentry', 'Master carpenter with 15 years of experience.', ['Furniture Building', 'Cabinet Making', 'Wood Repair'], ['Filipino', 'English'], 500, 15, 18, true, 4.8],
            ['Nene', 'Aguilar', 'Painting', 'Creative painter with a passion for transforming homes.', ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'], ['Filipino'], 400, 9, 12, true, 4.5],
            ['Angelo', 'Tolentino', 'Aircon', 'Certified HVAC technician with 7 years of experience.', ['AC Cleaning', 'AC Repair', 'AC Installation'], ['Filipino', 'English'], 550, 7, 20, true, 4.6],
            ['Grace', 'Padilla', 'Landscaping', 'Experienced landscaper specializing in garden design.', ['Lawn Mowing', 'Garden Design', 'Yard Cleanup'], ['Filipino', 'English'], 350, 5, 15, false, 4.3],
            ['Christian', 'Alonzo', 'Laundry', 'Professional laundry service provider.', ['Wash & Fold', 'Dry Cleaning', 'Ironing'], ['Filipino', 'English'], 200, 4, 10, false, 4.2],
            ['Princess Joy', 'David', 'Pest Control', 'Licensed pest control specialist.', ['Fumigation', 'Rodent Control', 'Termite Treatment'], ['Filipino', 'English'], 400, 6, 18, false, 4.4],
            ['Reynaldo', 'Navarro', 'Appliance Repair', 'Skilled appliance repair technician.', ['Refrigerator Repair', 'Washing Machine Repair', 'Oven Repair'], ['Filipino'], 350, 8, 15, false, 4.1],
            ['Jasmine', 'Torres', 'Masonry', 'Experienced mason specializing in tiling, concrete work.', ['Tiling', 'Concrete Repair', 'Brick Work'], ['Filipino', 'English'], 450, 7, 15, false, 4.3],
            ['Vincent', 'Dizon', 'Personal Care', 'Licensed massage therapist and personal care professional.', ['Home Massage', 'Manicure', 'Pedicure'], ['Filipino', 'English'], 300, 5, 10, false, 4.5],
            ['Fatima', 'Sotto', 'Moving Services', 'Reliable moving service provider.', ['Packing', 'Loading & Unloading', 'Local Transport'], ['Filipino', 'English'], 350, 6, 20, false, 4.0],
            ['Edgar', 'Soriano', 'Plumbing', 'Skilled plumber offering fast and reliable service.', ['Leak Repair', 'Pipe Installation', 'Drain Cleaning'], ['Filipino', 'English'], 400, 6, 12, false, 4.2],
            ['Mary Grace', 'Valenzuela', 'Cleaning', 'Dedicated cleaning professional.', ['Deep Cleaning', 'Upholstery Cleaning', 'Post-Construction Cleaning'], ['Filipino', 'English'], 320, 5, 12, false, 4.1],
            ['Sofia', 'Gomez', 'Painting', 'Creative painter with a keen eye for detail.', ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'], ['Filipino', 'English'], 400, 6, 12, true, 4.7],
            ['Malaya', 'Dimaculangan', 'Plumbing', 'Reliable plumber with 5 years of experience.', ['Leak Repair', 'Pipe Installation', 'Faucet Replacement'], ['Filipino', 'English'], 400, 5, 15, false, 4.3],
            ['Alab', 'Macalintal', 'Electrical', 'Skilled electrician specializing in wiring.', ['Lighting Installation', 'Wiring & Rewiring', 'Outlet & Switch Repair'], ['Filipino', 'English'], 500, 6, 18, false, 4.2],
            ['Tala', 'Catacutan', 'Cleaning', 'Thorough cleaning professional with 4 years of experience.', ['Deep Cleaning', 'Window Cleaning', 'Carpet Shampooing'], ['Filipino', 'English'], 280, 4, 12, false, 4.1],
            ['Kidlat', 'Panganiban', 'Carpentry', 'Efficient carpenter skilled in furniture assembly.', ['Furniture Assembly', 'Cabinet Installation', 'Custom Shelving'], ['Filipino'], 450, 7, 15, false, 4.4],
            ['Mutya', 'Magbanua', 'Painting', 'Creative painter with an eye for color.', ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'], ['Filipino', 'English'], 380, 5, 12, false, 4.0],
            ['Lakan', 'Dalisay', 'Plumbing', 'Experienced plumber offering drain cleaning.', ['Drain Cleaning', 'Toilet Repair & Installation', 'Water Heater Service'], ['Filipino', 'English'], 420, 6, 15, false, 4.5],
            ['Dakila', 'Manalo', 'Electrical', 'Licensed electrician with expertise in rewiring.', ['Electrical Inspection', 'Wiring & Rewiring', 'Generator Repair'], ['Filipino'], 550, 7, 20, false, 4.6],
            ['Hiraya', 'Ilagan', 'Cleaning', 'Detail-oriented cleaner specializing in move-in/move-out.', ['Move-in/out Cleaning', 'Post-Construction Cleaning', 'Office Cleaning'], ['Filipino', 'English'], 320, 5, 15, false, 4.3],
            ['Bituin', 'Lualhati', 'Carpentry', 'Talented carpenter focused on custom furniture.', ['Custom Shelving', 'Trim & Molding', 'Drywall Repair'], ['Filipino', 'English'], 400, 4, 12, false, 4.0],
            ['Isagani', 'Bayani', 'Painting', 'Dependable painter skilled in exterior painting.', ['Exterior Painting', 'Fence Painting', 'Garage Floor Coating'], ['Filipino'], 350, 6, 15, false, 4.2],
            ['Makisig', 'Dimagiba', 'Aircon', 'HVAC technician with 5 years of experience.', ['AC Cleaning', 'AC Repair', 'Filter Replacement'], ['Filipino', 'English'], 480, 5, 18, false, 4.4],
            ['Amihan', 'Batumbakal', 'Landscaping', 'Passionate landscaper specializing in garden design.', ['Lawn Mowing', 'Tree Trimming', 'Hedge Trimming'], ['Filipino'], 300, 4, 15, false, 4.1],
            ['Mithi', 'Agbayani', 'Laundry', 'Laundry care specialist with 3 years of experience.', ['Wash & Fold', 'Dry Cleaning', 'Ironing & Pressing'], ['Filipino', 'English'], 180, 3, 10, false, 4.0],
            ['Sinag', 'Mabini', 'Pest Control', 'Licensed pest control technician.', ['General Fumigation', 'Rodent Control', 'Mosquito Control'], ['Filipino', 'English'], 380, 5, 15, false, 4.3],
            ['Alon', 'Silang', 'Appliance Repair', 'Skilled appliance repair technician.', ['Refrigerator Repair', 'Washing Machine Repair', 'Electric Fan Repair'], ['Filipino', 'English'], 350, 6, 15, false, 4.2],
            ['Bagwis', 'Balagtas', 'Masonry', 'Experienced mason offering tiling, concrete repair.', ['Tiling', 'Concrete Repair', 'Brick & Block Work'], ['Filipino', 'English'], 420, 7, 18, false, 4.4],
            ['Kislap', 'Tolosa', 'Personal Care', 'Certified massage therapist and beauty care specialist.', ['Home Massage', 'Manicure', 'Pedicure'], ['Filipino', 'English'], 280, 4, 10, false, 4.5],
            ['Marikit', 'Alcantara', 'Moving Services', 'Dependable mover with 4 years of experience.', ['Packing Assistance', 'Loading & Unloading', 'Local Transport'], ['Filipino', 'English'], 320, 4, 20, false, 4.1],
            ['Alaya', 'Gatchalian', 'Plumbing', 'Professional plumber handling leak repairs.', ['Leak Repair', 'Pipe Installation', 'Faucet Replacement'], ['Filipino', 'English'], 400, 5, 15, false, 4.3],
            ['Datu', 'Macaraig', 'Electrical', 'Experienced electrician skilled in panel upgrades.', ['Panel Upgrade', 'Generator Repair', 'Smart Home Setup'], ['Filipino', 'English'], 520, 6, 18, false, 4.3],
            ['Magiting', 'Magsaysay', 'Cleaning', 'Thorough cleaning expert specializing in deep cleaning.', ['Deep Cleaning', 'Carpet Shampooing', 'Upholstery Cleaning'], ['Filipino', 'English'], 300, 5, 12, false, 4.0],
            ['Tadhana', 'Pangilinan', 'Carpentry', 'Carpentry professional specializing in kitchen cabinet refacing.', ['Kitchen Cabinet Refacing', 'Deck Repair', 'Custom Shelving'], ['Filipino'], 480, 6, 15, false, 4.5],
            ['Luningning', 'Tupaz', 'Painting', 'Talented painter offering interior and exterior painting.', ['Interior Painting', 'Ceiling Painting', 'Wallpaper Removal'], ['Filipino', 'English'], 370, 4, 12, false, 4.1],
            ['Diwa', 'Velasco', 'Aircon', 'HVAC specialist with 5 years of experience.', ['AC Installation', 'Duct Cleaning', 'Compressor Repair'], ['Filipino', 'English'], 500, 5, 18, false, 4.4],
            ['Sampaguita', 'Yuzon', 'Landscaping', 'Creative landscaper specializing in planting.', ['Planting & Gardening', 'Garden Design', 'Lot Clearing'], ['Filipino', 'English'], 350, 6, 18, false, 4.3],
            ['Ligaya', 'Zapanta', 'Laundry', 'Laundry service provider with careful handling.', ['Wash & Fold', 'Dry Cleaning', 'Ironing & Pressing'], ['Filipino'], 170, 3, 10, false, 3.9],
            ['Biyaya', 'Halili', 'Pest Control', 'Pest control specialist handling termite treatment.', ['Termite Treatment', 'Ant & Roach Removal', 'General Fumigation'], ['Filipino', 'English'], 400, 5, 15, false, 4.2],
            ['Liwanag', 'Manansala', 'Appliance Repair', 'Dependable appliance repair technician.', ['Oven & Stove Repair', 'Dryer Repair', 'Water Dispenser Repair'], ['Filipino', 'English'], 380, 5, 15, false, 4.1],
            ['Dalisay', 'Polistico', 'Masonry', 'Skilled mason offering tiling, pavement work.', ['Tiling', 'Pavement & Walkway', 'Waterproofing'], ['Filipino', 'English'], 430, 6, 18, false, 4.3],
            ['Yumi', 'Sumulong', 'Personal Care', 'Licensed beauty care professional.', ['Home Massage', 'Haircut & Blow-dry', 'Waxing'], ['Filipino', 'English'], 300, 4, 12, false, 4.4],
        ];

        $workerUsers = [];
        $workerProfiles = [];
        foreach ($workersData as $i => [$first, $last, $category, $bio, $skills, $langs, $rate, $exp, $radius, $verified, $rating]) {
            $email = strtolower(str_replace(' ', '.', "$first.$last")) . '@gmail.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $first,
                    'last_name'         => $last,
                    'name'              => "$first $last",
                    'password'          => $pass,
                    'role'              => 'worker',
                    'service_category'  => $category,
                    'city'              => 'Tuy, Batangas',
                    'barangay'          => $barangays[$i % count($barangays)],
                    'language'          => 'English',
                    'phone'             => $faker->phoneNumber,
                    'email_verified_at' => $randDate(),
                    'created_at'        => $randDate(),
                    'updated_at'        => $randDate(),
                ]
            );

            $profile = WorkerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio'                     => $bio,
                    'skills'                  => $skills,
                    'spoken_languages'        => $langs,
                    'hourly_rate'             => $rate,
                    'available_days'          => 'Monday — Saturday',
                    'preferred_hours'         => 'Full Day (8 AM — 5 PM)',
                    'service_areas'           => $barangays,
                    'years_of_experience'     => $exp,
                    'service_radius_km'       => $radius,
                    'government_id_verified'  => $verified,
                    'average_rating'          => $rating,
                    'cover_photo'             => null,
                    'created_at'              => $randDate(),
                    'updated_at'              => $randDate(),
                ]
            );

            $workerUsers[] = $user;
            $workerProfiles[] = $profile;
        }
        $log('users (workers)', User::where('role', 'worker')->count());
        $log('worker_profiles', WorkerProfile::count());

        // ══════════════════════════════════════════════════════════════
        //  6. WORKER DOCUMENTS
        // ═════════════════════════════════════════════════════════════

        $docTypes = ['Government-Issued ID', 'National Police Clearance', 'Barangay Clearance', 'Proof of Competency'];
        foreach ($workerUsers as $i => $user) {
            foreach ($docTypes as $j => $type) {
                WorkerDocument::firstOrCreate(
                    ['user_id' => $user->id, 'document_type' => $type],
                    [
                        'file_path'     => null,
                        'status'        => $i < 7 ? 'verified' : 'pending',
                        'verified_at'   => $i < 7 ? $randDate() : null,
                        'created_at'    => $randDate(),
                        'updated_at'    => $randDate(),
                    ]
                );
            }
        }
        $log('worker_documents', WorkerDocument::count());

        // ══════════════════════════════════════════════════════════════
        //  7. PROVIDER SERVICES
        // ══════════════════════════════════════════════════════════════

        foreach ($workerUsers as $user) {
            $category = ServiceCategory::where('name', $user->service_category)->first();
            if (! $category) {
                continue;
            }
            $services = $category->services;
            foreach ($services as $service) {
                ProviderService::firstOrCreate(
                    ['user_id' => $user->id, 'service_id' => $service->id],
                    [
                        'custom_price' => null,
                        'is_available' => true,
                        'created_at'   => $randDate(),
                        'updated_at'   => $randDate(),
                    ]
                );
            }
        }
        $log('provider_services', ProviderService::count());

        // ══════════════════════════════════════════════════════════════
        //  8. CLIENT ADDRESSES
        // ══════════════════════════════════════════════════════════════

        foreach ($clientUsers as $user) {
            ClientAddress::firstOrCreate(
                ['user_id' => $user->id, 'house_no' => $faker->buildingNumber, 'barangay' => $barangays[array_rand($barangays)]],
                [
                    'label'        => 'Home',
                    'latitude'     => null,
                    'longitude'    => null,
                    'is_default'   => true,
                    'created_at'   => $randDate(),
                    'updated_at'   => $randDate(),
                ]
            );
        }
        $log('client_addresses', ClientAddress::count());

        // ══════════════════════════════════════════════════════════════
        //  9. CONVERSATIONS
        // ══════════════════════════════════════════════════════════════

        foreach ($clientUsers as $i => $client) {
            $worker = $workerUsers[$i % count($workerUsers)];
            Conversation::firstOrCreate(
                ['client_id' => $client->id, 'worker_id' => $worker->id],
                [
                    'last_message_at' => $randDate(),
                    'created_at'      => $randDate(),
                    'updated_at'      => $randDate(),
                ]
            );
        }
        $log('conversations', Conversation::count());

        // ══════════════════════════════════════════════════════════════
        //  10. BOOKINGS (cleared on re-run to avoid duplicates)
        // ══════════════════════════════════════════════════════════════

        // Clear transactional tables on re-run (reverse dependency order)
        BookingHistory::query()->delete();
        BookingWorker::query()->delete();
        Dispute::query()->delete();
        Message::query()->delete();
        Review::query()->delete();
        BookingQuote::query()->delete();
        Booking::query()->delete();

        $statuses = [Booking::STATUS_NEW, Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS, Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED, Booking::STATUS_DECLINED];
        $allBookings = [];

        foreach ($workerUsers as $wi => $workerUser) {
            $category = ServiceCategory::where('name', $workerUser->service_category)->first();
            $catServices = $category ? $category->services : collect();
            $numBookings = rand(2, 5);

            for ($b = 0; $b < $numBookings; $b++) {
                $client = $clientUsers[array_rand($clientUsers)];
                $service = $catServices->random();
                $scheduled = $randDate();
                $status = $statuses[array_rand($statuses)];
                $price = round($workerUser->workerProfile?->hourly_rate ?? 400 * rand(1, 4), 2);
                $completed = null;
                if ($status === Booking::STATUS_COMPLETED) {
                    $completed = $scheduled->copy()->addHours(rand(2, 5));
                }

                $exists = Booking::where('client_id', $client->id)
                    ->where('worker_id', $workerUser->id)
                    ->where('scheduled_at', $scheduled)
                    ->first();
                if ($exists) {
                    continue;
                }

                $booking = Booking::create([
                    'client_id'        => $client->id,
                    'worker_id'        => $workerUser->id,
                    'service_id'       => $service?->id,
                    'service_category' => $workerUser->service_category,
                    'scheduled_at'     => $scheduled,
                    'address'          => $barangays[array_rand($barangays)] . ', Tuy, Batangas',
                    'price'            => $price,
                    'status'           => $status,
                    'completed_at'     => $completed,
                    'created_at'       => $scheduled->copy()->subDays(rand(1, 7)),
                    'updated_at'       => $scheduled->copy()->subDays(rand(1, 7)),
                ]);
                $allBookings[] = $booking;
            }
        }
        $log('bookings', Booking::count());

        // ══════════════════════════════════════════════════════════════
        //  11. BOOKING QUOTES (earnings) — one per completed booking
        // ══════════════════════════════════════════════════════════════

        $completedBookings = Booking::where('status', Booking::STATUS_COMPLETED)->get();
        foreach ($completedBookings as $booking) {
            BookingQuote::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'worker_id'    => $booking->worker_id,
                    'total_amount' => $booking->price,
                    'tip_amount'   => 0.00,
                    'paid_at'      => $booking->completed_at,
                    'created_at'   => $booking->completed_at,
                    'updated_at'   => $booking->completed_at,
                ]
            );
        }
        $log('booking_quotes', BookingQuote::count());

        // ══════════════════════════════════════════════════════════════
        //  12. REVIEWS — one per completed booking
        // ══════════════════════════════════════════════════════════════

        $comments = [
            'Excellent service! Very professional and on time.',
            'Great work, highly recommended. Will book again.',
            'Very satisfied with the quality of work.',
            'Did an amazing job. Fair pricing too.',
            'Professional and efficient. Thank you!',
            'Good quality work. Would recommend to friends.',
            'Satisfied with the service. On time and polite.',
            'Magaling po gumawa. Satisfied ako sa resulta.',
            'Salamat sa mahusay na serbisyo!',
            'Maayos at mabilis ang trabaho. Sulit ang bayad.',
        ];

        foreach ($completedBookings as $booking) {
            Review::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'client_id'  => $booking->client_id,
                    'worker_id'  => $booking->worker_id,
                    'rating'     => rand(3, 5),
                    'comment'    => $comments[array_rand($comments)],
                    'photo_path' => null,
                    'created_at' => $booking->completed_at ?? $randDate(),
                    'updated_at' => $booking->completed_at ?? $randDate(),
                ]
            );
        }
        $log('reviews', Review::count());

        // ══════════════════════════════════════════════════════════════
        //  13. MESSAGES
        // ══════════════════════════════════════════════════════════════

        $messageTemplates = [
            'Hi! I saw your profile and I would like to book your service.',
            'Thanks for reaching out! I would be happy to help.',
            'Are you available this weekend?',
            'Yes, I am available. I have accepted the booking.',
            'What time should I expect you?',
            'I will be there at the scheduled time.',
            'How is it going?',
            'I have arrived and started working on it.',
            'All done! Thank you for the great service.',
            'You are welcome! Let me know if you need anything else.',
        ];

        foreach (Booking::limit(30)->get() as $booking) {
            $sender   = rand(0, 1) ? $booking->client_id : $booking->worker_id;
            $receiver = $sender === $booking->client_id ? $booking->worker_id : $booking->client_id;
            $sentAt   = $randDate();

            Message::firstOrCreate(
                ['booking_id' => $booking->id, 'sender_id' => $sender, 'message' => $messageTemplates[array_rand($messageTemplates)]],
                [
                    'receiver_id' => $receiver,
                    'read_at'     => null,
                    'created_at'  => $sentAt,
                    'updated_at'  => $sentAt,
                ]
            );
        }
        $log('messages', Message::count());

        // ══════════════════════════════════════════════════════════════
        //  14. BOOKING WORKERS
        // ══════════════════════════════════════════════════════════════

        foreach (Booking::inRandomOrder()->limit(15)->get() as $booking) {
            $helper = $workerUsers[array_rand($workerUsers)];
            if ($helper->id === $booking->worker_id) {
                continue;
            }
            BookingWorker::firstOrCreate(
                ['booking_id' => $booking->id, 'worker_id' => $helper->id],
                [
                    'role'            => 'assistant',
                    'payout_amount'   => 0.00,
                    'status'          => 'pending_client_approval',
                    'justification_note' => null,
                    'invited_at'      => $randDate(),
                    'responded_at'    => null,
                    'created_at'      => $randDate(),
                    'updated_at'      => $randDate(),
                ]
            );
        }
        $log('booking_workers', BookingWorker::count());

        // ══════════════════════════════════════════════════════════════
        //  15. DISPUTES
        // ══════════════════════════════════════════════════════════════

        $reasons = ['Service not completed', 'Overcharged', 'Worker did not show up', 'Quality of work was poor'];
        foreach (Booking::inRandomOrder()->limit(5)->get() as $booking) {
            $raisedBy = $booking->client_id;
            Dispute::firstOrCreate(
                ['booking_id' => $booking->id, 'raised_by' => $raisedBy],
                [
                    'reason'           => $reasons[array_rand($reasons)],
                    'status'           => 'open',
                    'resolution_notes' => null,
                    'resolved_by'      => null,
                    'resolved_at'      => null,
                    'created_at'       => $randDate(),
                    'updated_at'       => $randDate(),
                ]
            );
        }
        $log('disputes', Dispute::count());

        // ══════════════════════════════════════════════════════════════
        //  16. BOOKING HISTORIES
        // ══════════════════════════════════════════════════════════════

        $statusFlow = ['new' => 'accepted', 'accepted' => 'en_route', 'en_route' => 'in_progress', 'in_progress' => 'completed'];
        foreach ($statusFlow as $oldStatus => $newStatus) {
            $bookings = Booking::where('status', $newStatus)->inRandomOrder()->limit(3)->get();
            foreach ($bookings as $booking) {
                $historyDate = $randDate();
                BookingHistory::firstOrCreate(
                    ['booking_id' => $booking->id, 'new_status' => $newStatus],
                    [
                        'user_id'   => $booking->worker_id,
                        'old_status' => $oldStatus,
                        'notes'     => 'Status transitioned from ' . $oldStatus . ' to ' . $newStatus,
                        'created_at' => $historyDate,
                        'updated_at' => $historyDate,
                    ]
                );
            }
        }
        $log('booking_histories', BookingHistory::count());

        // ══════════════════════════════════════════════════════════════
        //  17. TESTIMONIALS
        // ══════════════════════════════════════════════════════════════

        $testimonials = [
            ['name' => 'Ana Reyes', 'role' => 'Homeowner, Tuy', 'content' => 'Na-book ko agad si Mang Jose para sa sirang gripo ko. Wala pang isang oras, nasa bahay na at naayos agad.', 'rating' => 5, 'avatar_initials' => 'AR', 'sort_order' => 1],
            ['name' => 'Mang Carlos', 'role' => 'Electrician, Tuy', 'content' => 'Dati referral lang ang kitaan. Ngayon, may regular akong booking galing sa KaAyos.', 'rating' => 5, 'avatar_initials' => 'MC', 'sort_order' => 2],
            ['name' => 'Dennis B.', 'role' => 'Homeowner, Nasugbu', 'content' => 'Yung AI matching nila, hindi biro. Inirecommend agad yung tamang worker para sa painting project namin.', 'rating' => 5, 'avatar_initials' => 'DB', 'sort_order' => 3],
            ['name' => 'Luzviminda Flores', 'role' => 'Homeowner, Lumbangan', 'content' => 'Mabilis at maayos ang serbisyo. Na-book ko si Aling Cherry para sa deep cleaning.', 'rating' => 5, 'avatar_initials' => 'LF', 'sort_order' => 4],
            ['name' => 'Jericho Cruz', 'role' => 'Homeowner, Dalima', 'content' => 'Maayos magtrabaho si Mang Roberto — yung mga sira naming cabinet inayos agad.', 'rating' => 4, 'avatar_initials' => 'JC', 'sort_order' => 5],
        ];

        foreach ($testimonials as $data) {
            Testimonial::firstOrCreate(
                ['name' => $data['name'], 'sort_order' => $data['sort_order']],
                array_merge($data, ['is_active' => true, 'created_at' => $randDate(), 'updated_at' => $randDate()])
            );
        }
        $log('testimonials', Testimonial::count());

        // ══════════════════════════════════════════════════════════════
        //  SUMMARY
        // ══════════════════════════════════════════════════════════════

        echo PHP_EOL . 'Database seeded successfully (fixed seed: 42, dates: 2 months ago to today).' . PHP_EOL;
        foreach ($logs as $table => $count) {
            echo "  {$table}: {$count} rows" . PHP_EOL;
        }
        echo 'Admin: admin@kaayos.tech / password' . PHP_EOL;
        echo 'All dummy user emails end in @gmail.com' . PHP_EOL;
        echo 'Photo fields (avatar, cover_photo, photo_path) left null.' . PHP_EOL;
    }
}
