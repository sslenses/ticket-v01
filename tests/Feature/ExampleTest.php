<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test that dashboard contains search bar and Alpine.js ticket data structure.
     */
    public function test_dashboard_contains_search_bar_and_alpine_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ticket = \App\Models\Ticket::create([
            'label' => 'TICKET-SEARCH-101',
            'source_device' => 'Device X',
            'destination_device' => 'Device Y',
            'source_tenant_id' => \Illuminate\Support\Str::uuid(),
            'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
            'connector_type' => 'LC',
            'status' => \App\Models\Ticket::STATUS_WAITING_DESTINATION,
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('x-model="searchQuery"', false);
        $response->assertSee('placeholder="Search by label, device, or status..."', false);
        $response->assertSee('TICKET-SEARCH-101');
        $response->assertSee('filteredTickets()');
        $response->assertSee('activeStatusFilter: \'all\'', false);
        $response->assertSee('@click="activeStatusFilter = \'all\'"', false);
        $response->assertSee('@click="activeStatusFilter = \'waiting_destination\'"', false);
        $response->assertSee('@click="activeStatusFilter = \'in_progress\'"', false);
        $response->assertSee('@click="activeStatusFilter = \'completed\'"', false);
    }
}
