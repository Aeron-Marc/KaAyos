<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationEvent;
use App\Models\WorkerDocument;
use App\Models\WorkerVerification;
use App\Support\WorkerDocuments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = WorkerVerification::with([
            'user.workerProfile',
            'documents' => fn($q) => $q->select('id', 'worker_verification_id', 'status', 'document_type'),
        ]);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $verifications = $query->latest()->paginate(20)->withQueryString();

        $totalTypes = count(WorkerDocuments::types());
        $statusCounts = [
            'pending_review'    => WorkerVerification::where('status', 'pending_review')->count(),
            'changes_requested' => WorkerVerification::where('status', 'changes_requested')->count(),
            'pending_documents' => WorkerVerification::where('status', 'pending_documents')->count(),
            'under_review'      => WorkerVerification::where('status', 'under_review')->count(),
        ];

        return view('admin.verification.index', compact('verifications', 'totalTypes', 'statusCounts'));
    }

    public function show(WorkerVerification $verification): View
    {
        $verification->load([
            'user.workerProfile',
            'reviewedBy',
            'documents',
            'events.actor',
        ]);

        $documentTypes = WorkerDocuments::types();

        return view('admin.verification.show', compact('verification', 'documentTypes'));
    }

    public function startReview(Request $request, WorkerVerification $verification): RedirectResponse
    {
        $oldStatus = $verification->status;
        $verification->update([
            'status'       => 'under_review',
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        $this->logEvent($verification, 'admin_review_started', $request->user(), $oldStatus, 'under_review');

        return redirect()->back()->with('info', 'Verification review started.');
    }

    public function approveDocument(Request $request, WorkerVerification $verification, WorkerDocument $document): RedirectResponse
    {
        $oldStatus = $document->status;
        $document->update([
            'status'      => 'verified',
            'admin_notes' => $request->input('notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'verified_at' => now(),
        ]);

        $this->logEvent($verification, 'document_approved', $request->user(), null, null, [
            'document_type' => $document->document_type,
            'document_id'   => $document->id,
        ]);

        $document->user->notify(new \App\Notifications\DocumentVerified($document));

        $this->checkCompletion($verification);

        return redirect()->back()->with('success', '"' . str_replace('_', ' ', ucfirst($document->document_type)) . '" has been approved.');
    }

    public function rejectDocument(Request $request, WorkerVerification $verification, WorkerDocument $document): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $document->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
            'admin_notes'      => $request->input('notes'),
            'reviewed_by'      => $request->user()->id,
            'reviewed_at'      => now(),
        ]);

        $this->logEvent($verification, 'document_rejected', $request->user(), null, null, [
            'document_type'    => $document->document_type,
            'document_id'      => $document->id,
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        $document->user->notify(new \App\Notifications\DocumentRejected($document, $request->input('rejection_reason')));

        $verification->update(['status' => 'changes_requested']);

        return redirect()->back()->with('error', '"' . str_replace('_', ' ', ucfirst($document->document_type)) . '" has been rejected.');
    }

    public function approveAll(Request $request, WorkerVerification $verification): RedirectResponse
    {
        $user = $verification->user;
        $pendingDocs = WorkerDocument::where('worker_verification_id', $verification->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingDocs as $doc) {
            $doc->update([
                'status'      => 'verified',
                'admin_notes' => $request->input('notes'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'verified_at' => now(),
            ]);
            $user->notify(new \App\Notifications\DocumentVerified($doc));
        }

        $this->logEvent($verification, 'bulk_approved', $request->user(), $verification->status, null, [
            'count' => $pendingDocs->count(),
        ]);

        $this->checkCompletion($verification);

        return redirect()->back()->with('success', "All {$pendingDocs->count()} pending documents have been approved.");
    }

    public function rejectAll(Request $request, WorkerVerification $verification): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $user = $verification->user;
        $pendingDocs = WorkerDocument::where('worker_verification_id', $verification->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingDocs as $doc) {
            $doc->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->input('rejection_reason'),
                'admin_notes'      => $request->input('notes'),
                'reviewed_by'      => $request->user()->id,
                'reviewed_at'      => now(),
            ]);
            $user->notify(new \App\Notifications\DocumentRejected($doc, $request->input('rejection_reason')));
        }

        $this->logEvent($verification, 'bulk_rejected', $request->user(), $verification->status, 'changes_requested', [
            'count' => $pendingDocs->count(),
        ]);

        $verification->update(['status' => 'changes_requested']);

        if ($user->workerProfile) {
            $user->workerProfile->update(['government_id_verified' => false]);
        }

        $user->notify(new \App\Notifications\ChangesRequested($verification));

        return redirect()->back()->with('error', "All {$pendingDocs->count()} pending documents have been rejected.");
    }

    public function requestChanges(Request $request, WorkerVerification $verification): RedirectResponse
    {
        $oldStatus = $verification->status;
        $verification->update([
            'status'       => 'changes_requested',
            'reviewed_by'  => $request->user()->id,
            'reviewed_at'  => now(),
        ]);

        $this->logEvent($verification, 'changes_requested', $request->user(), $oldStatus, 'changes_requested');

        $verification->user->notify(new \App\Notifications\ChangesRequested($verification));

        return redirect()->back()->with('warning', 'Changes requested from the worker.');
    }

    public function reject(Request $request, WorkerVerification $verification): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $verification->status;
        $verification->update([
            'status'      => 'rejected',
            'admin_notes' => $request->input('reason'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejected_at' => now(),
        ]);

        WorkerDocument::where('worker_verification_id', $verification->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        $this->logEvent($verification, 'rejected', $request->user(), $oldStatus, 'rejected', [
            'reason' => $request->input('reason'),
        ]);

        $verification->user->notify(new \App\Notifications\VerificationRejected(
            $verification->user,
            $request->input('reason')
        ));

        if ($verification->user->workerProfile) {
            $verification->user->workerProfile->update(['government_id_verified' => false]);
        }

        return redirect()->route('admin.verifications.index')
            ->with('error', "Verification for {$verification->user->name} has been rejected.");
    }

    private function checkCompletion(WorkerVerification $verification): void
    {
        $requiredTypes = collect(WorkerDocuments::types())->pluck('name');

        $verifiedTypes = WorkerDocument::where('worker_verification_id', $verification->id)
            ->whereIn('document_type', $requiredTypes)
            ->where('status', 'verified')
            ->pluck('document_type');

        $allVerified = $requiredTypes->diff($verifiedTypes)->isEmpty();

        if ($allVerified) {
            $oldStatus = $verification->status;
            $verification->update([
                'status'      => 'verified',
                'verified_at' => now(),
            ]);
            $this->logEvent($verification, 'verified', null, $oldStatus, 'verified');

            if ($verification->user->workerProfile) {
                $verification->user->workerProfile->update(['government_id_verified' => true]);
            }

            $verification->user->notify(new \App\Notifications\VerificationApproved($verification->user));
        } else {
            if ($verification->user->workerProfile) {
                $verification->user->workerProfile->update(['government_id_verified' => false]);
            }
        }
    }

    private function logEvent(WorkerVerification $verification, string $eventType, $actor, ?string $oldStatus = null, ?string $newStatus = null, ?array $metadata = null): void
    {
        VerificationEvent::create([
            'worker_verification_id' => $verification->id,
            'event_type'             => $eventType,
            'old_status'             => $oldStatus,
            'new_status'             => $newStatus,
            'actor_id'               => $actor?->id,
            'metadata'               => $metadata,
        ]);
    }
}
