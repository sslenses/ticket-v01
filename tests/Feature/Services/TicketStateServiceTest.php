<?php

namespace Tests\Feature\Services;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\TicketStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TicketStateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_transitions_ticket_state_and_logs_it()
    {
        // 1. Setup
        $user = User::factory()->create();
        Auth::login($user);

        $ticket = Ticket::create([
            'label' => 'TICKET-001',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => \Illuminate\Support\Str::uuid(),
            'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
            'connector_type' => 'LC-LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        $service = new TicketStateService();
        $nextState = Ticket::STATUS_APPROVED_DESTINATION;

        // 2. Act
        $service->transition($ticket, $nextState);

        // 3. Assert
        $this->assertEquals($nextState, $ticket->fresh()->status);
        
        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'from_state' => Ticket::STATUS_WAITING_DESTINATION,
            'to_state' => $nextState,
        ]);
    }
}
