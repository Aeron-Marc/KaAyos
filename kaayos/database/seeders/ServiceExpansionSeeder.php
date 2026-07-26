<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ProviderService;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceExpansionSeeder extends Seeder
{
    public function run(): void
    {
        // ── New Categories (7) ────────────────────────────────────

        $newCategories = [
            ['name' => 'Landscaping',     'slug' => 'landscaping',     'description' => 'Lawn care, tree trimming, gardening and yard maintenance',   'icon' => 'fa-tree'],
            ['name' => 'Laundry',         'slug' => 'laundry',         'description' => 'Wash and fold, dry cleaning, ironing and fabric care',        'icon' => 'fa-shirt'],
            ['name' => 'Pest Control',    'slug' => 'pest-control',    'description' => 'Fumigation, rodent control, termite and insect treatment',     'icon' => 'fa-bug'],
            ['name' => 'Appliance Repair','slug' => 'appliance-repair','description' => 'Repair and maintenance of home appliances and electronics',   'icon' => 'fa-tv'],
            ['name' => 'Masonry',         'slug' => 'masonry',         'description' => 'Tiling, concrete work, brick laying and waterproofing',       'icon' => 'fa-hammer'],
            ['name' => 'Personal Care',   'slug' => 'personal-care',   'description' => 'Home massage, manicure, pedicure, haircut and beauty services','icon' => 'fa-hand-sparkles'],
            ['name' => 'Moving Services', 'slug' => 'moving-services', 'description' => 'Packing, loading, transport and heavy item moving assistance', 'icon' => 'fa-truck'],
        ];

        foreach ($newCategories as $data) {
            ServiceCategory::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // ── New Services for New Categories ───────────────────────

        $newServices = [
            'landscaping' => [
                ['name' => 'Lawn Mowing',               'slug' => 'lawn-mowing',                'base_price' => 250,  'description' => 'Grass cutting and lawn tidying for residential yards'],
                ['name' => 'Tree Trimming',             'slug' => 'tree-trimming',              'base_price' => 500,  'description' => 'Branch trimming, tree shaping and safe limb removal'],
                ['name' => 'Hedge Trimming',            'slug' => 'hedge-trimming',             'base_price' => 300,  'description' => 'Shaping and trimming of hedge rows and bushes'],
                ['name' => 'Planting & Gardening',      'slug' => 'planting-gardening',         'base_price' => 350,  'description' => 'Flower and plant planting, garden bed preparation and soil work'],
                ['name' => 'Yard Cleanup',              'slug' => 'yard-cleanup',               'base_price' => 400,  'description' => 'Leaf raking, debris removal and general yard clearing'],
                ['name' => 'Garden Design',             'slug' => 'garden-design',              'base_price' => 600,  'description' => 'Landscape planning, plant selection and garden layout design'],
                ['name' => 'Lot Clearing',              'slug' => 'lot-clearing',               'base_price' => 800,  'description' => 'Heavy clearing of overgrown lots including tall grass and weeds'],
            ],
            'laundry' => [
                ['name' => 'Wash & Fold',               'slug' => 'wash-fold',                  'base_price' => 150,  'description' => 'Machine washing, drying and folding of everyday clothing'],
                ['name' => 'Dry Cleaning',              'slug' => 'dry-cleaning',               'base_price' => 200,  'description' => 'Professional dry cleaning for delicate and formal garments'],
                ['name' => 'Ironing & Pressing',        'slug' => 'ironing-pressing',           'base_price' => 120,  'description' => 'Hand ironing and steam pressing of shirts, pants and dresses'],
                ['name' => 'Pick-up & Delivery',        'slug' => 'pickup-delivery',            'base_price' => 100,  'description' => 'Door-to-door laundry collection and return delivery service'],
                ['name' => 'Curtain Cleaning',          'slug' => 'curtain-cleaning',           'base_price' => 300,  'description' => 'Wash, dry and re-hang of curtains and drapes'],
                ['name' => 'Blanket & Comforter Wash',  'slug' => 'blanket-comforter-wash',      'base_price' => 250,  'description' => 'Deep washing of large blankets, comforters and bed sheets'],
            ],
            'pest-control' => [
                ['name' => 'General Fumigation',        'slug' => 'general-fumigation',         'base_price' => 500,  'description' => 'Whole-room or whole-house fumigation treatment'],
                ['name' => 'Rodent Control',            'slug' => 'rodent-control',             'base_price' => 400,  'description' => 'Rat and mouse trapping, exclusion and prevention'],
                ['name' => 'Termite Treatment',         'slug' => 'termite-treatment',          'base_price' => 700,  'description' => 'Termite inspection, baiting and chemical treatment'],
                ['name' => 'Mosquito Control',          'slug' => 'mosquito-control',           'base_price' => 350,  'description' => 'Mosquito fogging and larvicide treatment for property'],
                ['name' => 'Ant & Roach Removal',       'slug' => 'ant-roach-removal',          'base_price' => 300,  'description' => 'Cockroach and ant baiting, spraying and nest removal'],
                ['name' => 'Preventive Spraying',       'slug' => 'preventive-spraying',        'base_price' => 250,  'description' => 'Routine perimeter spraying to prevent pest infestations'],
            ],
            'appliance-repair' => [
                ['name' => 'Refrigerator Repair',       'slug' => 'refrigerator-repair',        'base_price' => 500,  'description' => 'Diagnosis and repair of cooling issues, leaks and compressor problems'],
                ['name' => 'Washing Machine Repair',    'slug' => 'washing-machine-repair',     'base_price' => 450,  'description' => 'Fix for draining, spinning, leaking and belt issues'],
                ['name' => 'Oven & Stove Repair',       'slug' => 'oven-stove-repair',          'base_price' => 400,  'description' => 'Repair of gas and electric ovens, burners and thermostats'],
                ['name' => 'Water Dispenser Repair',    'slug' => 'water-dispenser-repair',     'base_price' => 300,  'description' => 'Fix for heating, cooling and leaking water dispensers'],
                ['name' => 'Electric Fan Repair',       'slug' => 'electric-fan-repair',        'base_price' => 200,  'description' => 'Motor repair, blade balancing and switch replacement for fans'],
                ['name' => 'Microwave Repair',          'slug' => 'microwave-repair',           'base_price' => 350,  'description' => 'Repair for heating issues, turntable and control panel problems'],
                ['name' => 'Dryer Repair',              'slug' => 'dryer-repair',               'base_price' => 450,  'description' => 'Fix for drying issues, heating element and drum problems'],
            ],
            'masonry' => [
                ['name' => 'Tiling',                    'slug' => 'tiling',                     'base_price' => 500,  'description' => 'Floor and wall tile installation, repair and grouting'],
                ['name' => 'Concrete Repair',           'slug' => 'concrete-repair',            'base_price' => 600,  'description' => 'Crack filling, resurfacing and patching of concrete surfaces'],
                ['name' => 'Brick & Block Work',        'slug' => 'brick-block-work',           'base_price' => 550,  'description' => 'Brick laying, wall construction and block installation'],
                ['name' => 'Waterproofing',             'slug' => 'waterproofing',              'base_price' => 450,  'description' => 'Waterproof coating application for walls, roofs and basements'],
                ['name' => 'Wall Repointing',           'slug' => 'wall-repointing',            'base_price' => 400,  'description' => 'Mortar joint repair and restoration for brick and stone walls'],
                ['name' => 'Pavement & Walkway',        'slug' => 'pavement-walkway',           'base_price' => 700,  'description' => 'Concrete paving, walkway installation and pathway repair'],
            ],
            'personal-care' => [
                ['name' => 'Home Massage',              'slug' => 'home-massage',               'base_price' => 400,  'description' => 'Swedish, deep tissue or relaxation massage at your home'],
                ['name' => 'Manicure',                  'slug' => 'manicure',                   'base_price' => 150,  'description' => 'Nail shaping, cuticle care and polish application'],
                ['name' => 'Pedicure',                  'slug' => 'pedicure',                   'base_price' => 180,  'description' => 'Foot soak, nail care, callus removal and polish'],
                ['name' => 'Haircut & Blow-dry',        'slug' => 'haircut-blowdry',            'base_price' => 200,  'description' => 'Hair trimming, styling and blow-dry at home'],
                ['name' => 'Facial',                    'slug' => 'facial',                     'base_price' => 350,  'description' => 'Deep cleansing facial with steam, extraction and mask'],
                ['name' => 'Waxing',                    'slug' => 'waxing',                     'base_price' => 200,  'description' => 'Hair removal waxing for arms, legs, underarms and brows'],
            ],
            'moving-services' => [
                ['name' => 'Packing Assistance',         'slug' => 'packing-assistance',        'base_price' => 300,  'description' => 'Careful packing of belongings into boxes and containers'],
                ['name' => 'Loading & Unloading',        'slug' => 'loading-unloading',         'base_price' => 350,  'description' => 'Heavy lifting and loading of items onto trucks and vans'],
                ['name' => 'Local Transport',            'slug' => 'local-transport',           'base_price' => 500,  'description' => 'Transport of household items within Tuy and nearby areas'],
                ['name' => 'Heavy Item Moving',          'slug' => 'heavy-item-moving',         'base_price' => 600,  'description' => 'Moving of heavy furniture, appliances and equipment'],
                ['name' => 'Storage Assistance',         'slug' => 'storage-assistance',        'base_price' => 400,  'description' => 'Help with organizing and storing items in storage areas'],
            ],
        ];

        foreach ($newServices as $catSlug => $services) {
            $category = ServiceCategory::where('slug', $catSlug)->first();
            if ($category) {
                foreach ($services as $data) {
                    Service::firstOrCreate(
                        ['slug' => $data['slug']],
                        $data + ['category_id' => $category->id]
                    );
                }
            }
        }

        // ── Additional Services for Existing Categories (5 each) ──

        $existingExtensions = [
            'plumbing' => [
                ['name' => 'Drain Cleaning',             'slug' => 'drain-cleaning',            'base_price' => 300,  'description' => 'Unclogging and cleaning of kitchen, bathroom and floor drains'],
                ['name' => 'Toilet Repair & Installation','slug' => 'toilet-repair-installation','base_price' => 400,  'description' => 'Toilet leak repair, flush mechanism fix and new toilet installation'],
                ['name' => 'Faucet Replacement',         'slug' => 'faucet-replacement',         'base_price' => 250,  'description' => 'Replacement of kitchen and bathroom faucets and fixtures'],
                ['name' => 'Sewer Line Cleaning',        'slug' => 'sewer-line-cleaning',       'base_price' => 600,  'description' => 'Main sewer line inspection and cleaning using professional equipment'],
                ['name' => 'Garbage Disposal Repair',    'slug' => 'garbage-disposal-repair',   'base_price' => 350,  'description' => 'Fixing jammed or broken garbage disposal units'],
            ],
            'electrical' => [
                ['name' => 'Outlet & Switch Repair',     'slug' => 'outlet-switch-repair',      'base_price' => 250,  'description' => 'Repair or replacement of power outlets, switches and covers'],
                ['name' => 'Ceiling Fan Installation',   'slug' => 'ceiling-fan-installation',  'base_price' => 400,  'description' => 'Mounting and wiring of ceiling fans including remote setup'],
                ['name' => 'Panel Upgrade',              'slug' => 'panel-upgrade',             'base_price' => 1000, 'description' => 'Electrical panel upgrade and breaker replacement'],
                ['name' => 'Generator Repair',           'slug' => 'generator-repair',          'base_price' => 700,  'description' => 'Troubleshooting and repair of home generators'],
                ['name' => 'Smart Home Setup',           'slug' => 'smart-home-setup',          'base_price' => 500,  'description' => 'Installation of smart switches, lights and home automation devices'],
            ],
            'cleaning' => [
                ['name' => 'Carpet Shampooing',          'slug' => 'carpet-shampooing',         'base_price' => 400,  'description' => 'Deep shampoo cleaning of carpets and area rugs'],
                ['name' => 'Upholstery Cleaning',        'slug' => 'upholstery-cleaning',       'base_price' => 350,  'description' => 'Fabric sofa, chair and cushion steam cleaning'],
                ['name' => 'Post-Construction Cleaning', 'slug' => 'post-construction-cleaning', 'base_price' => 600,  'description' => 'Construction dust removal, debris cleanup and final polishing'],
                ['name' => 'Office Cleaning',            'slug' => 'office-cleaning',           'base_price' => 400,  'description' => 'Commercial office cleaning including desks, floors and restrooms'],
                ['name' => 'Pressure Washing',           'slug' => 'pressure-washing',          'base_price' => 500,  'description' => 'High-pressure washing of driveways, patios, walls and fences'],
            ],
            'carpentry' => [
                ['name' => 'Door Repair & Installation', 'slug' => 'door-repair-installation',   'base_price' => 450,  'description' => 'Fixing sticking doors, replacing hinges and installing new doors'],
                ['name' => 'Deck Repair',                'slug' => 'deck-repair',               'base_price' => 600,  'description' => 'Wood deck repair, board replacement and refinishing'],
                ['name' => 'Trim & Molding',             'slug' => 'trim-molding',              'base_price' => 350,  'description' => 'Installation of baseboards, crown molding and window trims'],
                ['name' => 'Drywall Repair',             'slug' => 'drywall-repair',            'base_price' => 300,  'description' => 'Patching of holes, crack repair and drywall replacement'],
                ['name' => 'Kitchen Cabinet Refacing',   'slug' => 'kitchen-cabinet-refacing',   'base_price' => 700,  'description' => 'Cabinet door replacement, refacing and hardware upgrade'],
            ],
            'painting' => [
                ['name' => 'Fence Painting',             'slug' => 'fence-painting',            'base_price' => 400,  'description' => 'Painting and staining of wooden and metal fences'],
                ['name' => 'Ceiling Painting',           'slug' => 'ceiling-painting',          'base_price' => 400,  'description' => 'Ceiling painting including patch touch-ups and edge cutting'],
                ['name' => 'Wallpaper Removal',          'slug' => 'wallpaper-removal',         'base_price' => 350,  'description' => 'Old wallpaper stripping and wall surface preparation'],
                ['name' => 'Garage Floor Coating',       'slug' => 'garage-floor-coating',      'base_price' => 500,  'description' => 'Epoxy and concrete floor coating for garage surfaces'],
            ],
            'aircon' => [
                ['name' => 'Gas Refill',                 'slug' => 'gas-refill',                'base_price' => 500,  'description' => 'Refrigerant gas refill and pressure check for all AC types'],
                ['name' => 'Duct Cleaning',              'slug' => 'duct-cleaning',             'base_price' => 600,  'description' => 'Air duct and vent cleaning for central AC systems'],
                ['name' => 'Compressor Repair',          'slug' => 'compressor-repair',         'base_price' => 800,  'description' => 'AC compressor diagnosis, repair and replacement'],
                ['name' => 'Filter Replacement',         'slug' => 'filter-replacement',        'base_price' => 200,  'description' => 'Replacement of AC air filters for better air quality'],
            ],
        ];

        foreach ($existingExtensions as $catSlug => $services) {
            $category = ServiceCategory::where('slug', $catSlug)->first();
            if ($category) {
                foreach ($services as $data) {
                    Service::firstOrCreate(
                        ['slug' => $data['slug']],
                        $data + ['category_id' => $category->id]
                    );
                }
            }
        }

        $this->command->info('Service categories and services expanded successfully!');
        $this->command->info('Categories: ' . ServiceCategory::count());
        $this->command->info('Services: ' . Service::count());
    }
}
