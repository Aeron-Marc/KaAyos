<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('suspended_at');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('worker_id');
            $table->index('client_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('conversation_id');
            $table->index('sender_id');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->index('last_message_at');
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['suspended_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['worker_id']);
            $table->dropIndex(['client_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['sender_id']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['last_message_at']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
