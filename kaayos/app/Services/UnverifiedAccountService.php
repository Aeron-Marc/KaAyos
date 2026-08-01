<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnverifiedAccountService
{
    public function purgeExpired(): int
    {
        $cutoff = now()->subMinutes((int) config('kaayos.unverified_ttl_minutes', 15));

        $userIds = User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return 0;
        }

        $purged = 0;

        DB::transaction(function () use ($userIds, &$purged) {
            // Tables that exist without migrations/FK constraints — clean explicitly
            $verificationIds = DB::table('worker_verifications')
                ->whereIn('user_id', $userIds)
                ->pluck('id');

            if ($verificationIds->isNotEmpty()) {
                DB::table('verification_events')
                    ->whereIn('worker_verification_id', $verificationIds)
                    ->delete();
                DB::table('worker_verifications')
                    ->whereIn('user_id', $userIds)
                    ->delete();
            }

            $purged = User::whereIn('id', $userIds)->delete();
        });

        if ($purged > 0) {
            Log::info("Purged {$purged} expired unverified account(s).", ['user_ids' => $userIds->all()]);
        }

        return $purged;
    }
}
