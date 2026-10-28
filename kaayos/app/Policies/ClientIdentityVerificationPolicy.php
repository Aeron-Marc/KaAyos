<?php

namespace App\Policies;

use App\Models\ClientIdentityVerification;
use App\Models\User;

class ClientIdentityVerificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ClientIdentityVerification $verification): bool
    {
        return $user->role === 'admin' || $user->id === $verification->user_id;
    }

    public function review(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function viewDocument(User $user, ClientIdentityVerification $verification): bool
    {
        return $user->role === 'admin';
    }

    public function submit(User $user): bool
    {
        return $user->role === 'client';
    }

    public function update(User $user, ClientIdentityVerification $verification): bool
    {
        return false;
    }

    public function delete(User $user, ClientIdentityVerification $verification): bool
    {
        return false;
    }
}
