<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Implements Wage-Based Compensation Scheme & Service Extras catalog / booking items.
     */
    public function up(): void
    {
        // 1. Worker Profiles - wage schemes
        Schema::table('worker_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('worker_profiles', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->nullable()->after('hourly_rate');
            }
            if (!Schema::hasColumn('worker_profiles', 'task_base_rate')) {
                $table->decimal('task_base_rate', 10, 2)->nullable()->after('daily_rate');
            }
            if (!Schema::hasColumn('worker_profiles', 'preferred_payment_scheme')) {
                $table->enum('preferred_payment_scheme', ['hourly', 'daily', 'task'])->default('task')->after('task_base_rate');
            }
        });

        // 2. Services - ensure default base_price is 0
        if (Schema::hasTable('services') && Schema::hasColumn('services', 'base_price')) {
            Schema::table('services', function (Blueprint $table) {
                $table->decimal('base_price', 10, 2)->default(0)->change();
            });
        }

        // 3. Service Extras catalog
        if (!Schema::hasTable('service_extras')) {
            Schema::create('service_extras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('suggested_cost', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Bookings - service reference and pricing fields
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('worker_id')->constrained('services')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'payment_scheme')) {
                $table->enum('payment_scheme', ['hourly', 'daily', 'task'])->default('task')->after('pricing_type');
            }
            if (!Schema::hasColumn('bookings', 'agreed_rate')) {
                $table->decimal('agreed_rate', 10, 2)->nullable()->after('payment_scheme');
            }
            if (!Schema::hasColumn('bookings', 'extras_total')) {
                $table->decimal('extras_total', 10, 2)->default(0)->after('agreed_rate');
            }
            if (!Schema::hasColumn('bookings', 'payment_method')) {
                $table->string('payment_method', 30)->default('cash')->after('price');
            }
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'settled'])->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'payment_settled_at')) {
                $table->timestamp('payment_settled_at')->nullable()->after('payment_status');
            }
        });

        // 5. Booking Extras - itemized extras attached to a booking
        if (!Schema::hasTable('booking_extras')) {
            Schema::create('booking_extras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('service_extra_id')->nullable()->constrained('service_extras')->nullOnDelete();
                $table->string('name');
                $table->decimal('cost', 10, 2);
                $table->enum('source', ['catalog', 'custom'])->default('catalog');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_extras');
        Schema::dropIfExists('service_extras');

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
            $table->dropColumn([
                'payment_scheme',
                'agreed_rate',
                'extras_total',
                'payment_method',
                'payment_status',
                'payment_settled_at',
            ]);
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'daily_rate',
                'task_base_rate',
                'preferred_payment_scheme',
            ]);
        });
    }
};
