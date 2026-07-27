<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_documents', function (Blueprint $table) {
            $table->foreignId('worker_verification_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('worker_documents', function (Blueprint $table) {
            $table->dropForeign(['worker_verification_id']);
            $table->dropColumn(['worker_verification_id', 'rejection_reason']);
        });
    }
};
