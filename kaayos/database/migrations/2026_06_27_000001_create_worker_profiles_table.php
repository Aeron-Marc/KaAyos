<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->string('available_days')->nullable();
            $table->string('preferred_hours')->nullable();
            $table->json('service_areas')->nullable();
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->unsignedSmallInteger('service_radius')->nullable();
            $table->json('service_zone')->nullable();
            $table->string('cover_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
