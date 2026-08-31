<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected array $clientData = [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'email' => 'juan@example.com',
        'role' => 'client',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'terms' => true,
    ];

    protected array $workerData = [
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'maria@example.com',
        'role' => 'worker',
        'service_category' => 'Plumbing',
        'barangay' => 'Acle',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'terms' => true,
    ];

    public function test_client_can_register(): void
    {
        $this->post('/register', $this->clientData)
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'role' => 'client',
        ]);
    }

    public function test_worker_can_register_with_service_category(): void
    {
        $this->post('/register', $this->workerData)
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => 'worker',
            'service_category' => 'Plumbing',
        ]);
    }

    public function test_worker_registration_requires_service_category(): void
    {
        $data = $this->workerData;
        unset($data['service_category']);

        $this->post('/register', $data)
            ->assertSessionHasErrors('service_category');
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $data = $this->clientData;
        $data['terms'] = false;

        $this->post('/register', $data)
            ->assertSessionHasErrors('terms');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $data = $this->clientData;
        $data['password_confirmation'] = 'different';

        $this->post('/register', $data)
            ->assertSessionHasErrors('password');
    }

    public function test_intended_forces_client_role(): void
    {
        $data = $this->workerData;
        $data['intended'] = '/book/some-service';

        $this->post('/register', $data)->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => 'client',
        ]);
    }
}
