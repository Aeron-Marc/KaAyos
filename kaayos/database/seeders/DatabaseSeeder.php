<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ProviderService;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

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

        // ── Client Users ────────────────────────────────────────────

        $clients = [
            ['first_name' => 'Maria',  'last_name' => 'Santos',     'email' => 'maria@example.com',     'city' => 'Tuy, Batangas'],
            ['first_name' => 'John',   'last_name' => 'Villanueva', 'email' => 'john@example.com',      'city' => 'Nasugbu, Batangas'],
            ['first_name' => 'Ana',    'last_name' => 'Lopez',      'email' => 'ana@example.com',       'city' => 'Balayan, Batangas'],
        ];

        $clientUsers = [];
        foreach ($clients as $data) {
            $clientUsers[] = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'name'       => $data['first_name'] . ' ' . $data['last_name'],
                'email'      => $data['email'],
                'password'   => $password,
                'role'       => 'client',
                'city'       => $data['city'],
                'language'   => 'English',
                'email_verified_at' => now(),
            ]);
        }

        // ── Worker Users ────────────────────────────────────────────

        $workersData = [
            [
                'first_name' => 'Juan',     'last_name' => 'Dela Cruz',  'email' => 'juan@example.com',   'category' => 'Plumbing',
                'bio' => 'Expert plumber with over 8 years of experience in residential and commercial plumbing. Licensed and fully insured.',
                'skills' => ['Pipe Fixing', 'Water Heater', 'Drain Unblocking'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 450, 'years_exp' => 8, 'radius' => 25,
                'areas' => ['Tuy', 'Nasugbu', 'Balayan'],
                'days' => 'Monday — Saturday', 'hours' => 'Full Day (8 AM — 5 PM)',
            ],
            [
                'first_name' => 'Elena',    'last_name' => 'Santos',    'email' => 'elena@example.com',   'category' => 'Cleaning',
                'bio' => 'Professional cleaner dedicated to making homes spotless. Trained in eco-friendly cleaning methods.',
                'skills' => ['Deep Cleaning', 'Move-in/Move-out', 'Window Washing'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 300, 'years_exp' => 5, 'radius' => 20,
                'areas' => ['Tuy', 'Balayan', 'Calaca'],
                'days' => 'Monday — Friday', 'hours' => 'Morning (8 AM — 12 PM)',
            ],
            [
                'first_name' => 'Marco',    'last_name' => 'Reyes',     'email' => 'marco@example.com',   'category' => 'Electrical',
                'bio' => 'Licensed electrician specializing in residential wiring, panel upgrades, and smart home setup.',
                'skills' => ['Wiring', 'Panel Upgrades', 'Lighting Setup'],
                'languages' => ['Filipino', 'English'],
                'hourly_rate' => 600, 'years_exp' => 10, 'radius' => 30,
                'areas' => ['Tuy', 'Nasugbu', 'Lian'],
                'days' => 'All Week', 'hours' => 'Full Day (8 AM — 5 PM)',
            ],
            [
                'first_name' => 'Sofia',    'last_name' => 'Gomez',     'email' => 'sofia@example.com',   'category' => 'Painting',
                'bio' => 'Creative painter with a keen eye for detail. Transforms homes with quality finishes.',
                'skills' => ['Interior Painting', 'Exterior Painting', 'Cabinet Refinishing'],
                'languages' => ['Filipino'],
                'hourly_rate' => 400, 'years_exp' => 6, 'radius' => 20,
                'areas' => ['Tuy', 'Balayan', 'Calatagan'],
                'days' => 'Monday — Saturday', 'hours' => 'Morning (8 AM — 12 PM)',
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

            WorkerProfile::create([
                'user_id'             => $user->id,
                'bio'                 => $data['bio'],
                'skills'              => $data['skills'],
                'spoken_languages'    => $data['languages'],
                'hourly_rate'         => $data['hourly_rate'],
                'available_days'      => $data['days'],
                'preferred_hours'     => $data['hours'],
                'service_areas'       => $data['areas'],
                'years_of_experience' => $data['years_exp'],
                'service_radius'      => $data['radius'],
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
                        'user_id'    => $user->id,
                        'service_id' => $service->id,
                        'is_available' => true,
                    ]);
                }
            }
        }

        // ── Bookings ────────────────────────────────────────────────

        $bookingsData = [
            // Past completed bookings
            [
                'client_id' => $clientUsers[0]->id, 'worker_id' => $workerUsers[0]->id,
                'service_category' => 'Plumbing', 'address' => 'Brgy. 3, Tuy, Batangas',
                'scheduled_at' => now()->subDays(5)->setHour(10), 'price' => 450,
                'status' => 'completed', 'completed_at' => now()->subDays(5)->setHour(12),
            ],
            [
                'client_id' => $clientUsers[1]->id, 'worker_id' => $workerUsers[1]->id,
                'service_category' => 'Cleaning', 'address' => 'Brgy. 5, Nasugbu, Batangas',
                'scheduled_at' => now()->subDays(3)->setHour(9), 'price' => 1200,
                'status' => 'completed', 'completed_at' => now()->subDays(3)->setHour(15),
            ],
            [
                'client_id' => $clientUsers[2]->id, 'worker_id' => $workerUsers[2]->id,
                'service_category' => 'Electrical', 'address' => 'Brgy. 2, Balayan, Batangas',
                'scheduled_at' => now()->subDays(7)->setHour(14), 'price' => 650,
                'status' => 'completed', 'completed_at' => now()->subDays(7)->setHour(16),
            ],
            // Active / in-progress bookigns
            [
                'client_id' => $clientUsers[0]->id, 'worker_id' => $workerUsers[0]->id,
                'service_category' => 'Plumbing — Leak Fix', 'address' => 'Brgy. 1, Tuy, Batangas',
                'scheduled_at' => now()->setHour(10), 'price' => 450,
                'status' => 'in_progress',
            ],
            [
                'client_id' => $clientUsers[2]->id, 'worker_id' => $workerUsers[1]->id,
                'service_category' => 'Deep Cleaning', 'address' => 'Brgy. 2, Tuy, Batangas',
                'scheduled_at' => now()->setHour(9), 'price' => 300,
                'status' => 'in_progress',
            ],
            // Upcoming confirmed bookings
            [
                'client_id' => $clientUsers[1]->id, 'worker_id' => $workerUsers[2]->id,
                'service_category' => 'Electrical Inspection', 'address' => 'Brgy. 5, Tuy, Batangas',
                'scheduled_at' => now()->addDays(2)->setHour(14), 'price' => 600,
                'status' => 'confirmed',
            ],
            [
                'client_id' => $clientUsers[0]->id, 'worker_id' => $workerUsers[3]->id,
                'service_category' => 'Interior Painting', 'address' => 'Brgy. 3, Tuy, Batangas',
                'scheduled_at' => now()->addDays(3)->setHour(9), 'price' => 400,
                'status' => 'confirmed',
            ],
            // Pending booking requests
            [
                'client_id' => $clientUsers[0]->id, 'worker_id' => $workerUsers[2]->id,
                'service_category' => 'Wiring Repair', 'address' => 'Brgy. 3, Tuy, Batangas',
                'scheduled_at' => now()->addDays(5)->setHour(10), 'price' => 600,
                'status' => 'pending',
            ],
            [
                'client_id' => $clientUsers[2]->id, 'worker_id' => $workerUsers[0]->id,
                'service_category' => 'Pipe Replacement', 'address' => 'Brgy. 1, Balayan, Batangas',
                'scheduled_at' => now()->addDays(7)->setHour(13), 'price' => 800,
                'status' => 'pending',
            ],
        ];

        foreach ($bookingsData as $data) {
            Booking::create($data);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@kaayos.com / password');
        $this->command->info('Clients: maria@example.com, john@example.com, ana@example.com / password');
        $this->command->info('Workers: juan@example.com, elena@example.com, marco@example.com, sofia@example.com / password');
    }
}
