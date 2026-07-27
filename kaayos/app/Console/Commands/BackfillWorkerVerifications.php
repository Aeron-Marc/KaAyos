<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkerDocument;
use App\Models\WorkerVerification;
use App\Support\WorkerDocuments;
use Illuminate\Console\Command;

class BackfillWorkerVerifications extends Command
{
    protected $signature = 'verifications:backfill';
    protected $description = 'Create WorkerVerification records for existing WorkerDocuments';

    public function handle(): int
    {
        $requiredTypes = collect(WorkerDocuments::types())->pluck('name');

        $userIds = WorkerDocument::distinct()->pluck('user_id');

        if ($userIds->isEmpty()) {
            $this->info('No existing WorkerDocument records found. Nothing to backfill.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($userIds->count());
        $bar->start();

        foreach ($userIds as $userId) {
            $docs = WorkerDocument::where('user_id', $userId)->get();
            $totalDocs = $docs->count();
            $statuses = $docs->pluck('status', 'document_type');
            $hasRejected = $statuses->contains('rejected');
            $verifiedCount = $statuses->filter(fn($s) => $s === 'verified')->count();
            $allRequiredPresent = $requiredTypes->every(fn($t) => $statuses->has($t));

            if ($verifiedCount === $requiredTypes->count() && !$hasRejected) {
                $status = 'verified';
            } elseif ($hasRejected) {
                $status = 'changes_requested';
            } elseif ($allRequiredPresent) {
                $status = 'pending_review';
            } else {
                $status = 'pending_documents';
            }

            $reviewedDoc = $docs->whereNotNull('reviewed_by')->first();

            $verification = WorkerVerification::create([
                'user_id'      => $userId,
                'status'       => $status,
                'submitted_at' => $allRequiredPresent ? $docs->min('created_at') : null,
                'reviewed_by'  => $reviewedDoc?->reviewed_by,
                'reviewed_at'  => $reviewedDoc?->reviewed_at,
                'verified_at'  => $status === 'verified' ? $docs->where('status', 'verified')->max('verified_at') : null,
            ]);

            WorkerDocument::where('user_id', $userId)->update([
                'worker_verification_id' => $verification->id,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Backfilled {$userIds->count()} WorkerVerification records.");

        return Command::SUCCESS;
    }
}
