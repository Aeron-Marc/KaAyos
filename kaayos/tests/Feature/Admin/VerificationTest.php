<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WorkerDocument;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_admin_can_approve_document(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        WorkerProfile::create([
            'user_id' => $worker->id,
            'government_id_verified' => false,
        ]);

        $doc = WorkerDocument::create([
            'user_id' => $worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'docs/test-id.jpg',
            'status' => 'pending',
        ]);

        $this->post(route('admin.verification.approve', $doc), [
            'notes' => 'Looks good',
        ])->assertRedirect();

        $doc->refresh();
        $this->assertEquals('verified', $doc->status);
        $this->assertNotNull($doc->verified_at);
    }

    public function test_admin_can_reject_document(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $doc = WorkerDocument::create([
            'user_id' => $worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'docs/test-id.jpg',
            'status' => 'pending',
        ]);

        $this->post(route('admin.verification.reject', $doc), [
            'rejection_reason' => 'Image is blurry',
        ])->assertRedirect();

        $doc->refresh();
        $this->assertEquals('rejected', $doc->status);
    }

    public function test_approval_updates_worker_verification_flag_when_all_verified(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $profile = WorkerProfile::create([
            'user_id' => $worker->id,
            'government_id_verified' => false,
        ]);

        $docTypes = [
            'Government-Issued ID',
            'National Police or NBI Clearance',
            'Barangay Clearance',
            'Proof of Competency',
        ];

        foreach ($docTypes as $type) {
            WorkerDocument::create([
                'user_id' => $worker->id,
                'document_type' => $type,
                'file_path' => "docs/{$type}.jpg",
                'status' => 'verified',
            ]);
        }

        $doc = WorkerDocument::create([
            'user_id' => $worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'docs/gov-id-new.jpg',
            'status' => 'pending',
        ]);

        $this->post(route('admin.verification.approve', $doc), [
            'notes' => 'Approved',
        ]);

        $profile->refresh();
        $this->assertTrue($profile->government_id_verified);
    }

    public function test_rejection_clears_worker_verification_flag(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $profile = WorkerProfile::create([
            'user_id' => $worker->id,
            'government_id_verified' => true,
        ]);

        $doc = WorkerDocument::create([
            'user_id' => $worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'docs/test-id.jpg',
            'status' => 'pending',
        ]);

        $this->post(route('admin.verification.reject', $doc), [
            'rejection_reason' => 'Invalid document',
        ]);

        $profile->refresh();
        $this->assertFalse($profile->government_id_verified);
    }
}
