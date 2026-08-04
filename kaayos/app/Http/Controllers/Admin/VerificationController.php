<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveRejectVerificationRequest;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Support\WorkerDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'worker')
            ->with(['workerDocuments' => fn ($q) => $q->latest(), 'workerProfile']);

        switch ($request->input('status')) {
            case 'not_submitted':
                $query->whereDoesntHave('workerDocuments');
                break;
            case 'pending':
                $query->whereHas('workerDocuments', fn ($q) => $q->where('status', 'pending'));
                break;
            case 'rejected':
                $query->whereHas('workerDocuments', fn ($q) => $q->where('status', 'rejected'))
                      ->whereDoesntHave('workerDocuments', fn ($q) => $q->where('status', 'pending'));
                break;
            case 'verified':
                $query->whereHas('workerDocuments')
                      ->whereDoesntHave('workerDocuments', fn ($q) => $q->whereIn('status', ['pending', 'rejected']));
                break;
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $workers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $pendingCount = WorkerDocument::pending()->count();

        return view('admin.verification.index', compact('workers', 'pendingCount'));
    }

    public function show(WorkerDocument $verification)
    {
        $verification->load('user.workerProfile', 'reviewedBy');
        $types    = collect(WorkerDocuments::types());
        $userDocs = WorkerDocument::where('user_id', $verification->user_id)
            ->latest()
            ->get()
            ->keyBy('document_type');

        $slots = $types->map(fn ($type) => [
            'name'        => $type['name'],
            'description' => $type['description'],
            'icon'        => $type['icon'],
            'doc'         => $userDocs->get($type['name']),
        ]);

        return view('admin.verification.show', [
            'verification' => $verification,
            'slots'        => $slots,
        ]);
    }

    public function approve(ApproveRejectVerificationRequest $request, WorkerDocument $verification)
    {
        $verification->update([
            'status'      => 'verified',
            'admin_notes' => $request->input('notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'verified_at' => now(),
        ]);

        $user = $verification->user;
        $this->checkWorkerVerification($user);
        $user->notify(new \App\Notifications\VerificationApproved($user));

        return redirect()->back()
            ->with('success', "Verification for {$user->name} has been approved.");
    }

    public function reject(ApproveRejectVerificationRequest $request, WorkerDocument $verification)
    {
        $reason = (string) $request->input('rejection_reason');
        $notes  = (string) $request->input('notes');

        $verification->update([
            'status'      => 'rejected',
            'admin_notes' => trim($notes ? "{$reason}\n\nPrivate notes: {$notes}" : $reason),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $user = $verification->user;
        if ($user->workerProfile) {
            $user->workerProfile->update(['government_id_verified' => false]);
        }
        $user->notify(new \App\Notifications\VerificationRejected($user, $request->input('rejection_reason')));

        return redirect()->back()
            ->with('error', "Verification for {$user->name} has been rejected.");
    }

    private function checkWorkerVerification(User $user): void
    {
        $types = collect(WorkerDocuments::types())->pluck('name');
        $verifiedTypes = WorkerDocument::where('user_id', $user->id)
            ->whereIn('document_type', $types)
            ->where('status', 'verified')
            ->pluck('document_type');

        $allVerified = $types->diff($verifiedTypes)->isEmpty();

        if ($user->workerProfile) {
            $user->workerProfile->update([
                'government_id_verified' => $allVerified,
            ]);
        }
    }
}
