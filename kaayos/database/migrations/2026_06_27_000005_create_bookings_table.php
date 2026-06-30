<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('worker_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('service_category');
            $table->datetime('scheduled_at');
            $table->string('address');
            $table->text('notes')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->decimal('price', 10, 2)->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};