<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bill of Materials Table (Panel item: Billing of materials)
        if (!Schema::hasTable('booking_materials')) {
            Schema::create('booking_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('worker_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('item_name');
                $table->decimal('quantity', 8, 2)->default(1.00);
                $table->string('unit', 50)->default('pcs');
                $table->decimal('unit_cost', 10, 2);
                $table->decimal('total_cost', 10, 2);
                $table->string('receipt_photo_path')->nullable();
                $table->timestamps();
            });
        }

        // 2. Warranty Claims & Service Recalls Table (Panel items: Service warranty & Back jobs)
        if (!Schema::hasTable('warranty_claims')) {
            Schema::create('warranty_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('worker_id')->constrained('users')->cascadeOnDelete();
                $table->text('claim_reason');
                $table->string('evidence_photo_path')->nullable();
                $table->enum('status', ['submitted', 'worker_accepted', 'in_progress', 'resolved', 'disputed'])->default('submitted');
                $table->timestamp('warranty_valid_until')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Worker Suspension Appeals & Counter-Claims Table (Panel items: Counter-claims & Suspension policy)
        if (!Schema::hasTable('suspension_appeals')) {
            Schema::create('suspension_appeals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('grounds_of_appeal');
                $table->string('supporting_document_path')->nullable();
                $table->enum('status', ['pending', 'under_review', 'reinstated', 'rejected'])->default('pending');
                $table->text('admin_decision_notes')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Booking Table additions (Warranty, Priority, Queue Broadcast, Payment Channel)
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'is_priority')) {
                $table->boolean('is_priority')->default(false)->after('status');
            }
            if (!Schema::hasColumn('bookings', 'is_broadcast_pool')) {
                $table->boolean('is_broadcast_pool')->default(false)->after('is_priority');
            }
            if (!Schema::hasColumn('bookings', 'broadcast_expires_at')) {
                $table->timestamp('broadcast_expires_at')->nullable()->after('is_broadcast_pool');
            }
            if (!Schema::hasColumn('bookings', 'warranty_days')) {
                $table->unsignedSmallInteger('warranty_days')->default(7)->after('broadcast_expires_at');
            }
            if (!Schema::hasColumn('bookings', 'warranty_expires_at')) {
                $table->timestamp('warranty_expires_at')->nullable()->after('warranty_days');
            }
            if (!Schema::hasColumn('bookings', 'payment_channel')) {
                $table->string('payment_channel', 50)->default('cash_on_completion')->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'materials_total')) {
                $table->decimal('materials_total', 10, 2)->default(0.00)->after('extras_total');
            }
        });

        // 5. Review Table additions (Editable feedback within 48h)
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'is_edited')) {
                $table->boolean('is_edited')->default(false)->after('is_anonymous');
            }
            if (!Schema::hasColumn('reviews', 'editable_until')) {
                $table->timestamp('editable_until')->nullable()->after('is_edited');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspension_appeals');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('booking_materials');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'is_priority',
                'is_broadcast_pool',
                'broadcast_expires_at',
                'warranty_days',
                'warranty_expires_at',
                'payment_channel',
                'materials_total',
            ]);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'editable_until']);
        });
    }
};
