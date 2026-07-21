<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE messages MODIFY sender_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE messages MODIFY receiver_id BIGINT UNSIGNED NULL');

        try {
            DB::statement('ALTER TABLE messages DROP FOREIGN KEY messages_sender_id_foreign');
        } catch (\Exception) {}

        try {
            DB::statement('ALTER TABLE messages DROP FOREIGN KEY messages_receiver_id_foreign');
        } catch (\Exception) {}

        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_sender_id_foreign FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_receiver_id_foreign FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE messages DROP FOREIGN KEY messages_sender_id_foreign');
        } catch (\Exception) {}

        try {
            DB::statement('ALTER TABLE messages DROP FOREIGN KEY messages_receiver_id_foreign');
        } catch (\Exception) {}

        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_sender_id_foreign FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_receiver_id_foreign FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE');
    }
};
