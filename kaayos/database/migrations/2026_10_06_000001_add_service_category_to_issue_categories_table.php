<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_categories', function (Blueprint $table) {
            $table->string('service_category')->nullable()->after('icon');
            $table->index('service_category');
        });

        // Backfill: link existing issues to the trade they belong to (null = all trades)
        $map = [
            'plumbing-leak-burst-pipe'   => 'plumbing',
            'drain-toilet-clog'          => 'plumbing',
            'electrical-outage-no-power' => 'electrical',
            'faulty-outlet-switch'       => 'electrical',
            'appliance-repair'           => 'electrical',
            'aircon-cooling-issue'       => 'aircon',
            'roof-leak'                  => 'roofing',
            'lock-door-repair'           => 'carpentry',
            'flooring'                   => 'carpentry',
            'painting'                   => 'painting',
            'cleaning'                   => 'cleaning',
            // left as null (all trades): wall-ceiling-crack, pest-infestation,
            // general-maintenance, other-not-listed
        ];

        foreach ($map as $slug => $trade) {
            DB::table('issue_categories')->where('slug', $slug)->update(['service_category' => $trade]);
        }

        // New issues so gardening and welding have trade-specific options
        $now = now();
        $extras = [
            ['Garden / Plant Care', 'garden-plant-care', 'gardening', 'fa-leaf'],
            ['Tree & Vegetation Trimming', 'tree-vegetation-trimming', 'gardening', 'fa-tree'],
            ['Gate / Railing / Metal Repair', 'gate-railing-repair', 'welding', 'fa-hammer'],
            ['Metal Fabrication / Welding', 'metal-fabrication-welding', 'welding', 'fa-gears'],
        ];

        foreach ($extras as [$name, $slug, $trade, $icon]) {
            DB::table('issue_categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name'             => $name,
                    'service_category' => $trade,
                    'icon'             => $icon,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('issue_categories')->whereIn('slug', [
            'garden-plant-care',
            'tree-vegetation-trimming',
            'gate-railing-repair',
            'metal-fabrication-welding',
        ])->delete();

        Schema::table('issue_categories', function (Blueprint $table) {
            $table->dropIndex(['service_category']);
            $table->dropColumn('service_category');
        });
    }
};
