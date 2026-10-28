<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveRejectClientIdentityRequest;
use App\Models\ClientIdentityVerification;
use App\Models\User;
use App\Notifications\ClientIdentityApproved;
use App\Notifications\ClientIdentityRejected;
use App\Notifications\ClientIdentityResubmissionRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientVerificationController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::where('role', 'client')
            ->with('latestClientIdentityVerification')
            ->get();

        $status = $request->input('status');
        $search = $request->input('search');

        $rows = $clients->filter(function ($client) use ($status, $search) {
            $latest = $client->latestClientIdentityVerification;

            if ($status) {
                $match = match ($status) {
                    'pending'              => $latest?->isPending(),
                    'verified'             => $latest?->isVerified(),
                    'rejected'             => $latest?->isRejected(),
                    'resubmission_required'=> $latest?->needsResubmission(),
                    'not_submitted'        => is_null($latest),
                    default                => true,
                };

                if (! $match) {
                    return false;
                }
            }

            if ($search) {
                $needle = Str::lower($search);
                if (! Str::contains(Str::lower($client->name ?? ''), $needle)
                    && ! Str::contains(Str::lower($client->email ?? ''), $needle)) {
                    return false;
                }
            }

            return true;
        })->values();

        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $paginated = new LengthAwarePaginator(
            $rows->slice($offset, $perPage)->all(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $pendingCount = ClientIdentityVerification::pending()->count();

        return view('admin.client-verifications.index', compact('paginated', 'pendingCount'));
    }

    public function show(ClientIdentityVerification $verification)
    {
        $verification->load('user', 'reviewedBy');
        $history = $verification->user->clientIdentityVerifications()->latest()->get();

        return view('admin.client-verifications.show', compact('verification', 'history'));
    }

    public function approve(ApproveRejectClientIdentityRequest $request, ClientIdentityVerification $verification)
    {
        if (! $verification->canBeReviewed()) {
            return back()->with('error', 'Only a pending submission can be approved.');
        }

        $verification->status          = ClientIdentityVerification::STATUS_VERIFIED;
        $verification->admin_notes     = trim((string) $request->input('notes'));
        $verification->reviewed_by     = $request->user()->id;
        $verification->reviewed_at     = now();
        $verification->files_purge_after = now()->addDays((int) config('kaayos.client_identity.retention_days', 365));
        $verification->save();

        DB::transaction(function () use ($verification, $request) {
            $verification->user->notify(new ClientIdentityApproved($verification));
        });

        DB::transaction(function () use ($verification, $request) {
            $verification->user->notify(new ClientIdentityApproved($verification));
        });

        Log::channel('identity')->info('client.identity.reviewed', [
            'verification_id' => $verification->id,
            'user_id'         => $verification->user_id,
            'document'        => 'id+selfie',
            'decision'        => 'verified',
        ]);

        return redirect()->back()->with('success', 'Identity verification approved.');
    }

    public function reject(ApproveRejectClientIdentityRequest $request, ClientIdentityVerification $verification)
    {
        if (! $verification->canBeReviewed()) {
            return back()->with('error', 'Only a pending submission can be rejected.');
        }

        $reason = (string) $request->input('rejection_reason');
        $notes = (string) $request->input('notes');

        $verification->status           = ClientIdentityVerification::STATUS_REJECTED;
        $verification->rejection_reason = $reason;
        $verification->admin_notes      = $notes ? "Reason: {$reason}\n\nNotes: {$notes}" : $reason;
        $verification->reviewed_by      = $request->user()->id;
        $verification->reviewed_at      = now();
        $verification->files_purge_after = now()->addDays((int) config('kaayos.client_identity.purge_after_days', 30));
        $verification->save();

        DB::transaction(function () use ($verification, $reason) {
            $verification->user->notify(new ClientIdentityRejected($verification, $reason));
        });

        DB::transaction(function () use ($verification, $reason) {
            $verification->user->notify(new ClientIdentityRejected($verification, $reason));
        });

        Log::channel('identity')->info('client.identity.reviewed', [
            'verification_id' => $verification->id,
            'user_id'         => $verification->user_id,
            'document'        => 'id+selfie',
            'decision'        => 'rejected',
        ]);

        return redirect()->back()->with('success', 'Identity verification rejected.');
    }

    public function requestResubmission(ApproveRejectClientIdentityRequest $request, ClientIdentityVerification $verification)
    {
        if (! $verification->canBeReviewed()) {
            return back()->with('error', 'Only a pending submission can be returned for resubmission.');
        }

        $explanation = (string) $request->input('rejection_reason');
        $notes = (string) $request->input('notes');

        $verification->status           = ClientIdentityVerification::STATUS_RESUBMISSION_REQUIRED;
        $verification->rejection_reason = $explanation;
        $verification->admin_notes      = $notes ? "Explanation: {$explanation}\n\nNotes: {$notes}" : $explanation;
        $verification->reviewed_by      = $request->user()->id;
        $verification->reviewed_at      = now();
        $verification->files_purge_after = now()->addDays((int) config('kaayos.client_identity.purge_after_days', 30));
        $verification->save();

        DB::transaction(function () use ($verification, $explanation) {
            $verification->user->notify(new ClientIdentityResubmissionRequested($verification, $explanation));
        });

        DB::transaction(function () use ($verification, $explanation) {
            $verification->user->notify(new ClientIdentityResubmissionRequested($verification, $explanation));
        });

        Log::channel('identity')->info('client.identity.reviewed', [
            'verification_id' => $verification->id,
            'user_id'         => $verification->user_id,
            'document'        => 'id+selfie',
            'decision'        => 'resubmission_required',
        ]);

        return redirect()->back()->with('success', 'Resubmission requested.');
    }

    public function document(Request $request, ClientIdentityVerification $verification, string $document)
    {
        Gate::authorize('viewDocument', $verification);

        if (! in_array($document, ['id', 'selfie'], true)) {
            abort(404);
        }

        $path = $document === 'id' ? $verification->id_document_path : $verification->selfie_path;
        $disk = Storage::disk(config('client_identity.disk', 'identity'));

        if (! $path || ! $disk->exists($path)) {
            abort(404);
        }

        // Defensive: the path comes from the DB, never the URL. Block traversal anyway.
        if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            abort(403);
        }

        $fullPath = $disk->path($path);
        $mime = mime_content_type($fullPath) ?? 'application/octet-stream';
        $contents = $disk->get($path);

        Log::channel('identity')->info('client.identity.document_accessed', [
            'verification_id' => $verification->id,
            'user_id'         => $verification->user_id,
            'document'        => $document,
        ]);

        return response($contents, 200, [
            'Content-Type'           => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition'    => 'inline',
            'Cache-Control'          => 'private, no-store, max-age=0',
            'Content-Security-Policy'=> "default-src 'none'",
        ]);
    }
}
