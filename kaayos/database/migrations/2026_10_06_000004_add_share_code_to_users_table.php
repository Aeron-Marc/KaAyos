<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('share_code', 16)->nullable()->unique()->after('role');
        });

        DB::table('users')
            ->where('role', 'worker')
            ->whereNull('share_code')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    do {
                        $code = 'ID-' . \App\Models\User::generateShareCode();
                    } while (
                        DB::table('users')->where('share_code', $code)->exists()
                    );

                    DB::table('users')->where('id', $user->id)->update(['share_code' => $code]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['share_code']);
            $table->dropColumn('share_code');
        });
    }
};
