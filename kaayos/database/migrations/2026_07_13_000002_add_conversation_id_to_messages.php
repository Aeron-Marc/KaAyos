<?php

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('conversation_id')->nullable()->after('booking_id')
                ->constrained('conversations')->nullOnDelete();
        });

        foreach (Booking::select('client_id', 'worker_id')->distinct()->cursor() as $booking) {
            $conversation = Conversation::create([
                'client_id' => $booking->client_id,
                'worker_id' => $booking->worker_id,
            ]);

            Message::whereIn('booking_id', function ($q) use ($booking) {
                $q->select('id')->from('bookings')
                    ->where('client_id', $booking->client_id)
                    ->where('worker_id', $booking->worker_id);
            })->update(['conversation_id' => $conversation->id]);

            $lastMsg = Message::where('conversation_id', $conversation->id)->latest('created_at')->first();
            if ($lastMsg) {
                $conversation->update(['last_message_at' => $lastMsg->created_at]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conversation_id');
        });
    }
};
