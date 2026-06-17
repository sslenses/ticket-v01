<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ticket creation with authorized roles.
     */
    public function test_authorized_roles_can_create_ticket()
    {
        $roles = ['staff', 'dest_manager', 'admin'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)->postJson('/api/tickets', [
                'label' => 'TICKET-' . Str::random(5),
                'source_device' => 'Device A',
                'destination_device' => 'Device B',
                'source_tenant_id' => Str::uuid()->toString(),
                'destination_tenant_id' => Str::uuid()->toString(),
                'connector_type' => 'LC',
                'cable_details' => ['length' => 10, 'color' => 'blue'],
            ]);

            $response->assertStatus(201);
            $response->assertJsonPath('status', Ticket::STATUS_WAITING_DESTINATION);
        }
    }

    /**
     * Test ticket creation is forbidden for unauthorized roles.
     */
    public function test_unauthorized_roles_cannot_create_ticket()
    {
        // Default role is 'user'
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'label' => 'TICKET-XYZ',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test ticket creation validation rules.
     */
    public function test_ticket_creation_validation_rules()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Missing required fields
        $response = $this->actingAs($user)->postJson('/api/tickets', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['label', 'source_device', 'destination_device', 'source_tenant_id', 'destination_tenant_id', 'connector_type']);

        // Duplicate label validation
        $ticket = Ticket::create([
            'label' => 'DUP-123',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
        ]);

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'label' => 'DUP-123',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['label']);
    }

    /**
     * Test state transitions with appropriate roles.
     */
    public function test_authorized_transitions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $destManager = User::factory()->create(['role' => 'dest_manager']);

        // Create initial ticket
        $ticket = Ticket::create([
            'label' => 'TRANS-001',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        // 1. Transition to Approved Destination
        // Dest Manager can do this
        $response = $this->actingAs($destManager)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_APPROVED_DESTINATION,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_APPROVED_DESTINATION, $ticket->fresh()->status);

        // 2. Transition to Approved Admin
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_APPROVED_ADMIN,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_APPROVED_ADMIN, $ticket->fresh()->status);

        // 3. Transition to Sended Cable
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_SENDED_CABLE,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_SENDED_CABLE, $ticket->fresh()->status);

        // 4. Transition to Received Cable
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_RECEIVED_CABLE,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_RECEIVED_CABLE, $ticket->fresh()->status);

        // 5. Transition to Done
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_DONE,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_DONE, $ticket->fresh()->status);

        // Verify logs table
        $this->assertDatabaseCount('ticket_logs', 5);
        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'from_state' => Ticket::STATUS_RECEIVED_CABLE,
            'to_state' => Ticket::STATUS_DONE,
        ]);
    }

    /**
     * Test transitions unauthorized by roles are forbidden.
     */
    public function test_unauthorized_roles_cannot_transition()
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $ticket = Ticket::create([
            'label' => 'TRANS-002',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        // Staff cannot approve destination
        $response = $this->actingAs($staff)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_APPROVED_DESTINATION,
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test invalid or out-of-order state transitions are rejected.
     */
    public function test_invalid_state_transitions_are_rejected()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ticket = Ticket::create([
            'label' => 'TRANS-003',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        // Trying to skip straight to Done from Waiting Destination
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_DONE,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonPath('message', "Invalid state transition from 'waiting_destination' to 'done'.");
    }

    /**
     * Test that staff or admin can successfully cancel a ticket.
     */
    public function test_authorized_user_can_cancel_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'label' => 'TICKET-CANCEL-1',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_CANCELLED,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_CANCELLED, $ticket->fresh()->status);
    }

    /**
     * Test that staff or admin can edit details of a pending ticket.
     */
    public function test_authorized_user_can_edit_ticket_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'label' => 'TICKET-EDIT-1',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_WAITING_DESTINATION,
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}", [
            'label' => 'TICKET-EDITED-NEW',
            'source_device' => 'Device Changed',
            'destination_device' => 'Device Changed B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'SC',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('TICKET-EDITED-NEW', $ticket->fresh()->label);
        $this->assertEquals('Device Changed', $ticket->fresh()->source_device);
    }

    /**
     * Test that no further transitions or edits can be done once a ticket is cancelled.
     */
    public function test_cancelled_ticket_cannot_be_transitioned_or_edited()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::create([
            'label' => 'TICKET-CANCEL-2',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_CANCELLED,
        ]);

        // Attempting status transition
        $response1 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_APPROVED_DESTINATION,
        ]);
        $response1->assertStatus(403);

        // Attempting edit details
        $response2 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->id}", [
            'label' => 'TICKET-EDITED-FAIL',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
        ]);
        $response2->assertStatus(403);
    }

    /**
     * Test that guest users cannot access cancelled tickets (returns 403).
     */
    public function test_guest_cannot_access_cancelled_ticket()
    {
        $ticket = Ticket::create([
            'label' => 'TICKET-CANCEL-3',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_CANCELLED,
        ]);

        $response = $this->get("/tickets/{$ticket->id}");
        $response->assertStatus(403);
    }
}
