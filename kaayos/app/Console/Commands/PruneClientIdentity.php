<?php

namespace App\Console\Commands;

use App\Models\ClientIdentityVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneClientIdentity extends Command
{
    protected $signature = 'identity:prune';

    protected $description = 'Purge expired client identity verification files per retention policy.';

    public function handle(): int
    {
        $retentionDays = (int) config('kaayos.client_identity.retention_days', 365);
        $purgeDays = (int) config('kaayos.client_identity.purge_after_days', 30);

        $now = now();

        $verifications = ClientIdentityVerification::whereNotNull('files_purge_after')
            ->where('files_purge_after', '<=', $now)
            ->whereNull('files_purged_at')
            ->get();

        $purged = 0;

        foreach ($verifications as $verification) {
            $verification->purgeFiles();
            $verification->update(['files_purged_at' => $now]);
            $purged++;

            Log::channel('identity')->info('client.identity.files_purged', [
                'verification_id' => $verification->id,
                'user_id'         => $verification->user_id,
            ]);
        }

        $this->info("Purged {$purged} file set(s). (retention={$retentionDays}d, purge_after={$purgeDays}d)");

        return self::SUCCESS;
    }
}
