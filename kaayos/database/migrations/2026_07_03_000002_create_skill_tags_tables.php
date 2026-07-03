<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('worker_skill_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['worker_profile_id', 'skill_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_skill_tag');
        Schema::dropIfExists('skill_tags');
    }
};
