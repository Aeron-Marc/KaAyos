<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveRejectVerificationRequest;
use App\Models\User;
use App\Models\WorkerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkerDocument::with('user.workerProfile');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return view('admin.verification.index', compact('documents'));
    }

    public function show(WorkerDocument $verification)
    {
        $verification->load('user.workerProfile', 'reviewedBy');
        $documents = WorkerDocument::where('user_id', $verification->user_id)
            ->latest()
            ->get();

        return view('admin.verification.show', [
            'verification' => $verification,
            'documents'    => $documents,
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
        $user->notify(new \App\Notifications\VerificationApproved($user));

        return redirect()->route('admin.verification.index')
            ->with('success', "Verification for {$user->name} has been approved.");
    }

    public function reject(ApproveRejectVerificationRequest $request, WorkerDocument $verification)
    {
        $verification->update([
            'status'      => 'rejected',
            'admin_notes' => $request->input('rejection_reason'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $user = $verification->user;
        $user->notify(new \App\Notifications\VerificationRejected($user, $request->input('rejection_reason')));

        return redirect()->route('admin.verification.index')
            ->with('error', "Verification for {$user->name} has been rejected.");
    }
}
