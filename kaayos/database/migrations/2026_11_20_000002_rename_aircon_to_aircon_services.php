<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceCategory;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Rename service category
        ServiceCategory::where('slug', 'aircon')
            ->update(['name' => 'Aircon Services', 'slug' => 'aircon-services']);

        // Update worker profiles that reference old category name
        User::where('service_category', 'Aircon')
            ->update(['service_category' => 'Aircon Services']);
    }

    public function down(): void
    {
        ServiceCategory::where('slug', 'aircon-services')
            ->update(['name' => 'Aircon', 'slug' => 'aircon']);

        User::where('service_category', 'Aircon Services')
            ->update(['service_category' => 'Aircon']);
    }
};
