<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitClientIdentityRequest;
use App\Models\ClientIdentityVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdentityVerificationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        Gate::authorize('submit', ClientIdentityVerification::class);

        $latest = $user->latestClientIdentityVerification;
        $history = $user->clientIdentityVerifications()->latest()->get();
        $idTypes = config('kaayos.client_identity.id_types', []);

        return view('client.account.identity', compact('latest', 'history', 'idTypes'));
    }

    public function store(SubmitClientIdentityRequest $request)
    {
        $user = $request->user();

        $alreadyPending = ClientIdentityVerification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()->withErrors([
                'identity' => 'You already have an identity verification pending review. Please wait for a decision before submitting again.',
            ])->withInput();
        }

        DB::transaction(function () use ($user, $request) {
            $pending = ClientIdentityVerification::where('user_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->exists();

            if ($pending) {
                return;
            }

            $idPath     = $this->storeFile($request->file('id'), $user->id, 'id');
            $selfiePath = $this->storeFile($request->file('selfie'), $user->id, 'selfie');

            ClientIdentityVerification::create([
                'user_id'           => $user->id,
                'id_type'           => $request->input('id_type'),
                'id_document_path'  => $idPath,
                'selfie_path'       => $selfiePath,
                'status'            => ClientIdentityVerification::STATUS_PENDING,
                'submitted_at'      => now(),
                'consent_at'        => now(),
                'consent_version'   => config('kaayos.client_identity.notice_version'),
                'age_attested_at'   => now(),
            ]);
        });

        Log::channel('identity')->info('client.identity.submitted', [
            'user_id'   => $user->id,
            'document'  => 'id+selfie',
        ]);

        return redirect()->route('client.account.identity')
            ->with('success', 'Your identity documents have been submitted and are pending review.');
    }

    private function storeFile($file, int $userId, string $type): string
    {
        $mime = $file->getMimeType();

        $extension = match ($mime) {
            'image/jpeg'    => 'jpg',
            'image/png'     => 'png',
            'image/webp'    => 'webp',
            'application/pdf' => 'pdf',
            default         => $file->getClientOriginalExtension(),
        };

        $path = "{$userId}/".Str::uuid().'.'.$extension;

        Storage::disk(config('client_identity.disk', 'identity'))
            ->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }
}
