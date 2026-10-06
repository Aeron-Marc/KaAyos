<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function makeService(): Service
    {
        $category = ServiceCategory::create([
            'name'        => 'Plumbing',
            'slug'        => 'plumbing-calc-test',
            'description' => 'Pipe repairs and installations',
            'icon'        => 'fa-wrench',
        ]);

        return Service::create([
            'category_id' => $category->id,
            'name'        => 'Leak Repair',
            'slug'        => 'leak-repair-calc-test',
            'base_price'  => 350,
            'is_active'   => true,
        ]);
    }

    public function test_guest_can_view_calculator_page(): void
    {
        $this->makeService();

        $response = $this->get('/calculator');

        $response->assertOk();
        $response->assertSee('Job Cost Calculator');
        $response->assertSee('id="services-data"', false);
        $response->assertSee('Leak Repair');
        $response->assertSee('Custom base price');
    }

    public function test_services_payload_is_json_encoded_in_bridge(): void
    {
        $service = $this->makeService();

        $response = $this->get('/calculator');

        $response->assertOk();
        $response->assertSee('"id":' . $service->id, false);
        $response->assertSee('"name":"Leak Repair"', false);
        $response->assertSee('"base_price":"350.00"', false);
    }

    public function test_search_page_nav_links_to_calculator(): void
    {
        $this->get('/search')
            ->assertOk()
            ->assertSee('href="/calculator"', false);
    }

    public function test_client_sidebar_links_to_calculator(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee(route('calculator.index'), false);
    }

    public function test_calculator_falls_back_to_custom_price_when_no_services(): void
    {
        $response = $this->get('/calculator');

        $response->assertOk();
        $response->assertSee('id="services-data"', false);
        $response->assertDontSee('<optgroup', false);
    }
}
