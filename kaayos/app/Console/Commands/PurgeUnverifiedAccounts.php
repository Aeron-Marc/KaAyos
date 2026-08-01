<?php

namespace App\Console\Commands;

use App\Services\UnverifiedAccountService;
use Illuminate\Console\Command;

class PurgeUnverifiedAccounts extends Command
{
    protected $signature = 'app:purge-unverified-accounts';

    protected $description = 'Delete accounts that never verified their email within the TTL';

    public function handle(UnverifiedAccountService $service): int
    {
        $purged = $service->purgeExpired();

        $this->info("Purged {$purged} expired unverified account(s).");

        return self::SUCCESS;
    }
}
