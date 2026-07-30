<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_verification_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('worker_verification_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_events');
    }
};
