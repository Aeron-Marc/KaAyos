<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_services');
    }
};
