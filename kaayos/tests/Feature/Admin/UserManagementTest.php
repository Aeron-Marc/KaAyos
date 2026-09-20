<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_suspend_user(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->post(route('admin.users.suspend', $user), [
            'reason' => 'Violation of terms',
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->suspended_at);
        $this->assertEquals('Violation of terms', $user->suspended_reason);
    }

    public function test_admin_cannot_suspend_other_admin(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->post(route('admin.users.suspend', $otherAdmin), [
            'reason' => 'Testing',
        ])->assertSessionHas('error');

        $otherAdmin->refresh();
        $this->assertNull($otherAdmin->suspended_at);
    }

    public function test_suspension_cancels_active_bookings(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);
        $client = User::factory()->create(['role' => 'client']);

        $booking = Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'status' => Booking::STATUS_ACCEPTED,
            'service_category' => 'Plumbing',
            'scheduled_at' => now()->addDay(),
            'address' => '123 Brgy. Bayanan, Tuy, Batangas',
            'house_no' => '123',
            'barangay' => 'Brgy. Bayanan',
            'price' => 500,
        ]);

        $this->post(route('admin.users.suspend', $client), [
            'reason' => 'Suspicious activity',
        ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
    }

    public function test_admin_can_reactivate_user(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'suspended_at' => now(),
            'suspended_reason' => 'Test suspension',
        ]);

        $this->post(route('admin.users.reactivate', $user))
            ->assertRedirect();

        $user->refresh();
        $this->assertNull($user->suspended_at);
        $this->assertNull($user->suspended_reason);
    }

    public function test_non_admin_cannot_suspend_user(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'client']));
        $user = User::factory()->create(['role' => 'worker']);

        $this->post(route('admin.users.suspend', $user), [
            'reason' => 'Test',
        ])->assertStatus(403);
    }
}
