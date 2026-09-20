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
        Schema::table('users', function (Blueprint $table) {
            $table->string('client_type', 50)->default('homeowner')->after('role');
            $table->string('organization_name')->nullable()->after('client_type');
            $table->string('tin_number', 50)->nullable()->after('organization_name');
            $table->text('billing_address')->nullable()->after('tin_number');
            $table->string('oauth_provider', 50)->nullable()->after('avatar');
            $table->string('oauth_id')->nullable()->after('oauth_provider');
            $table->text('oauth_avatar')->nullable()->after('oauth_id');
            $table->index(['oauth_provider', 'oauth_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['oauth_provider', 'oauth_id']);
            $table->dropColumn([
                'client_type',
                'organization_name',
                'tin_number',
                'billing_address',
                'oauth_provider',
                'oauth_id',
                'oauth_avatar',
            ]);
        });
    }
};
