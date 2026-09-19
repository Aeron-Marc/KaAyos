<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'role'       => 'client',
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'barangay'   => 'Brgy. Bayanan',
        ]);

        $this->worker = User::factory()->create([
            'role'       => 'worker',
            'first_name' => 'Pedro',
            'last_name'  => 'Santos',
            'barangay'   => 'Brgy. Lumampon',
        ]);
    }

    // --- Auth guards ---

    public function test_guest_cannot_view_create_page(): void
    {
        $response = $this->get(route('client.testimonials.create'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_store_testimonial(): void
    {
        $response = $this->postJson(route('client.testimonials.store'), [
            'rating'  => 5,
            'content' => 'Great service!',
        ]);

        $response->assertUnauthorized();
    }

    // --- Client testimonial submission ---

    public function test_client_can_submit_testimonial(): void
    {
        $response = $this->actingAs($this->client)
            ->post(route('client.testimonials.store'), [
                'rating'  => 5,
                'content' => 'Excellent work by the team!',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('testimonials', [
            'user_id'  => $this->client->id,
            'content'  => 'Excellent work by the team!',
            'rating'   => 5,
            'status'   => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_client_testimonial_auto_generates_name(): void
    {
        $this->actingAs($this->client)
            ->post(route('client.testimonials.store'), [
                'rating'  => 4,
                'content' => 'Reliable service.',
            ]);

        $this->assertDatabaseHas('testimonials', [
            'user_id' => $this->client->id,
            'name'    => 'Juan Dela Cruz',
            'role'    => 'Homeowner, Brgy. Bayanan',
        ]);
    }

    public function test_client_testimonial_auto_generates_avatar_initials(): void
    {
        $this->actingAs($this->client)
            ->post(route('client.testimonials.store'), [
                'rating'  => 3,
                'content' => 'Decent work.',
            ]);

        $testimonial = Testimonial::where('user_id', $this->client->id)->first();

        $this->assertEquals('JD', $testimonial->avatar_initials);
    }

    // --- Worker testimonial submission ---

    public function test_worker_can_submit_testimonial(): void
    {
        $response = $this->actingAs($this->worker)
            ->post(route('worker.testimonials.store'), [
                'rating'  => 5,
                'content' => 'Great platform for workers.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('testimonials', [
            'user_id'  => $this->worker->id,
            'content'  => 'Great platform for workers.',
            'rating'   => 5,
            'status'   => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_worker_testimonial_role_set_to_trabahador(): void
    {
        $this->actingAs($this->worker)
            ->post(route('worker.testimonials.store'), [
                'rating'  => 4,
                'content' => 'Good experience.',
            ]);

        $this->assertDatabaseHas('testimonials', [
            'user_id' => $this->worker->id,
            'role'    => 'Trabahador, Brgy. Lumampon',
        ]);
    }

    // --- Validation ---

    public function test_rating_is_required(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.testimonials.store'), [
                'content' => 'Some content.',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('rating');
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.testimonials.store'), [
                'rating'  => 6,
                'content' => 'Some content.',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('rating');
    }

    public function test_content_is_required(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.testimonials.store'), [
                'rating' => 3,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('content');
    }

    public function test_content_max_1000_characters(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.testimonials.store'), [
                'rating'  => 5,
                'content' => str_repeat('a', 1001),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('content');
    }

    // --- Index shows user's own testimonials only ---

    public function test_client_index_shows_only_own_testimonials(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);

        Testimonial::create([
            'user_id'         => $this->client->id,
            'name'            => 'Juan',
            'role'            => 'Homeowner',
            'content'         => 'My review.',
            'rating'          => 5,
            'avatar_initials' => 'JD',
            'status'          => 'approved',
            'is_active'       => true,
        ]);

        Testimonial::create([
            'user_id'         => $otherClient->id,
            'name'            => 'Other',
            'role'            => 'Homeowner',
            'content'         => 'Other review.',
            'rating'          => 4,
            'avatar_initials' => 'OT',
            'status'          => 'approved',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('client.testimonials.index'));

        $response->assertOk();
    }

    // --- Admin routes ---

    public function test_admin_can_list_testimonials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Testimonial::create([
            'user_id'         => $this->client->id,
            'name'            => 'Juan',
            'role'            => 'Homeowner',
            'content'         => 'My review.',
            'rating'          => 5,
            'avatar_initials' => 'JD',
            'status'          => 'approved',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.testimonials.index'));

        $response->assertOk();
    }

    public function test_admin_can_view_single_testimonial(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $testimonial = Testimonial::create([
            'user_id'         => $this->client->id,
            'name'            => 'Juan',
            'role'            => 'Homeowner',
            'content'         => 'My review.',
            'rating'          => 5,
            'avatar_initials' => 'JD',
            'status'          => 'approved',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.testimonials.show', $testimonial));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_testimonial_routes(): void
    {
        $testimonial = Testimonial::create([
            'user_id'         => $this->client->id,
            'name'            => 'Juan',
            'role'            => 'Homeowner',
            'content'         => 'My review.',
            'rating'          => 5,
            'avatar_initials' => 'JD',
            'status'          => 'approved',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('admin.testimonials.index'));

        $response->assertStatus(403);

        $response = $this->actingAs($this->client)
            ->get(route('admin.testimonials.show', $testimonial));

        $response->assertStatus(403);
    }
}
