<?php

namespace App\Observers;

use App\Models\ClientIdentityVerification;
use Illuminate\Support\Facades\Storage;

class ClientIdentityVerificationObserver
{
    public function deleting(ClientIdentityVerification $verification): void
    {
        $verification->purgeFiles();
    }
}
