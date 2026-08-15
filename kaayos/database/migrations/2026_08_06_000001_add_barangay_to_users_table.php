<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tuyBarangays = [
        'Acle', 'Bayudbud', 'Bolbok', 'Burgos', 'Dalima', 'Dao',
        'Guinhawa', 'Lumbangan', 'Luna', 'Luntal', 'Magahis', 'Malibu',
        'Mataywanac', 'Palincaro', 'Putol', 'Rillo', 'Rizal', 'Sabang',
        'San Jose', 'Talon', 'Toong', 'Tuyon-Tuyon',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('barangay')->nullable()->after('city');
        });

        // Backfill each existing worker with a random Tuy barangay.
        $workerIds = DB::table('users')->where('role', 'worker')->pluck('id');
        foreach ($workerIds as $id) {
            DB::table('users')->where('id', $id)->update([
                'barangay' => $this->tuyBarangays[array_rand($this->tuyBarangays)],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('barangay');
        });
    }
};