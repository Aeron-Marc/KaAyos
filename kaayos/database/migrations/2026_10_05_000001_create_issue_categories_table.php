<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['Plumbing Leak / Burst Pipe', 'plumbing-leak-burst-pipe', 'fa-droplet'],
            ['Drain / Toilet Clog', 'drain-toilet-clog', 'fa-water'],
            ['Electrical Outage / No Power', 'electrical-outage-no-power', 'fa-bolt'],
            ['Faulty Outlet / Switch', 'faulty-outlet-switch', 'fa-plug'],
            ['Appliance Repair', 'appliance-repair', 'fa-screwdriver-wrench'],
            ['Aircon / Cooling Issue', 'aircon-cooling-issue', 'fa-snowflake'],
            ['Roof Leak', 'roof-leak', 'fa-house-flood-water'],
            ['Wall / Ceiling Crack', 'wall-ceiling-crack', 'fa-triangle-exclamation'],
            ['Pest Infestation', 'pest-infestation', 'fa-bug'],
            ['Lock / Door Repair', 'lock-door-repair', 'fa-lock'],
            ['Painting', 'painting', 'fa-paint-roller'],
            ['Flooring', 'flooring', 'fa-border-all'],
            ['Cleaning', 'cleaning', 'fa-broom'],
            ['General Maintenance', 'general-maintenance', 'fa-helmet-safety'],
            ['Other / Not Listed', 'other-not-listed', 'fa-ellipsis'],
        ];

        foreach ($defaults as [$name, $slug, $icon]) {
            DB::table('issue_categories')->insertOrIgnore([
                'name'        => $name,
                'slug'        => $slug,
                'description' => null,
                'icon'        => $icon,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_categories');
    }
};
