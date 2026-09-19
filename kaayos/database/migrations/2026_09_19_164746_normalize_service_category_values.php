<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed missing categories
        $newCategories = [
            ['name' => 'Roofing', 'slug' => 'roofing', 'description' => 'Roof installation, repair, and maintenance', 'icon' => 'fa-house-chimney', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Welding', 'slug' => 'welding', 'description' => 'Metal fabrication, welding repairs, and structural work', 'icon' => 'fa-fire', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'General', 'slug' => 'general', 'description' => 'General repair and maintenance services', 'icon' => 'fa-wrench', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($newCategories as $cat) {
            DB::table('service_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 2. Seed placeholder services under new categories
        $roofingId = DB::table('service_categories')->where('slug', 'roofing')->value('id');
        $weldingId = DB::table('service_categories')->where('slug', 'welding')->value('id');
        $generalId = DB::table('service_categories')->where('slug', 'general')->value('id');

        $newServices = [
            ['category_id' => $roofingId, 'name' => 'Roof Installation', 'slug' => 'roof-installation', 'base_price' => 1500, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $roofingId, 'name' => 'Roof Repair', 'slug' => 'roof-repair', 'base_price' => 800, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $roofingId, 'name' => 'Gutter Cleaning', 'slug' => 'gutter-cleaning', 'base_price' => 400, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $weldingId, 'name' => 'Welding Repair', 'slug' => 'welding-repair', 'base_price' => 600, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $weldingId, 'name' => 'Metal Fabrication', 'slug' => 'metal-fabrication', 'base_price' => 800, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $weldingId, 'name' => 'Gate & Railing Repair', 'slug' => 'gate-railing-repair', 'base_price' => 700, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $generalId, 'name' => 'General Repair', 'slug' => 'general-repair', 'base_price' => 400, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $generalId, 'name' => 'Home Maintenance', 'slug' => 'home-maintenance', 'base_price' => 350, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['category_id' => $generalId, 'name' => 'Furniture Assembly', 'slug' => 'general-furniture-assembly', 'base_price' => 300, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($newServices as $svc) {
            DB::table('services')->updateOrInsert(
                ['slug' => $svc['slug']],
                $svc
            );
        }

        // 3. Build mapping: lowercase variant -> proper case name from service_categories
        $categories = DB::table('service_categories')->pluck('name', 'slug')->toArray();
        // slug => proper name: e.g. 'plumbing' => 'Plumbing'

        $mapping = [];
        foreach ($categories as $slug => $name) {
            $mapping[strtolower($name)] = $name;
            $mapping[$slug] = $name;
        }
        // Add explicit edge-case mappings
        $mapping['aircon services'] = $categories['aircon'] ?? 'Aircon';
        $mapping['general repair'] = $categories['general'] ?? 'General';
        $mapping['gardening'] = $categories['landscaping'] ?? 'Landscaping';
        $mapping['other'] = $categories['general'] ?? 'General';
        $mapping['roofing'] = $categories['roofing'] ?? 'Roofing';
        $mapping['welding'] = $categories['welding'] ?? 'Welding';

        // 4. Normalize users.service_category
        $workers = DB::table('users')->where('role', 'worker')->whereNotNull('service_category')->get();
        foreach ($workers as $worker) {
            $raw = trim($worker->service_category);
            $normalized = $mapping[strtolower($raw)] ?? null;
            if ($normalized && $normalized !== $raw) {
                DB::table('users')->where('id', $worker->id)->update(['service_category' => $normalized]);
            }
        }

        // 5. Normalize bookings.service_category
        $bookings = DB::table('bookings')->whereNotNull('service_category')->get();
        foreach ($bookings as $booking) {
            $raw = trim($booking->service_category);
            $normalized = $mapping[strtolower($raw)] ?? $raw;
            if ($normalized !== $raw) {
                DB::table('bookings')->where('id', $booking->id)->update(['service_category' => $normalized]);
            }
        }
    }

    public function down(): void
    {
        // Remove newly seeded categories and services
        DB::table('services')->whereIn('slug', [
            'roof-installation', 'roof-repair', 'gutter-cleaning',
            'welding-repair', 'metal-fabrication', 'gate-railing-repair',
            'general-repair', 'home-maintenance', 'general-furniture-assembly',
        ])->delete();

        DB::table('service_categories')->whereIn('slug', ['roofing', 'welding', 'general'])->delete();
    }
};
