<?php

use App\Support\BillingTypeCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Apply the fixed/hourly/either classification to services that already
     * exist (the seeder covers fresh installs; this covers live databases).
     */
    public function up(): void
    {
        foreach (BillingTypeCatalog::MAP as $serviceName => $billingType) {
            DB::table('services')->where('name', $serviceName)->update(['billing_type' => $billingType]);
        }
    }

    public function down(): void
    {
        DB::table('services')->update(['billing_type' => 'either']);
    }
};
