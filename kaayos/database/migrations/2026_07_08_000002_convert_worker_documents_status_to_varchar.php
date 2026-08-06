<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE worker_documents MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'not_submitted'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE worker_documents MODIFY COLUMN status ENUM('verified','pending','not_submitted') NOT NULL DEFAULT 'not_submitted'");
        }
    }
};
