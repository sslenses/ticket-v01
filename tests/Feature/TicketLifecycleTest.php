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
                'cable_details' => ['length' => 10, 'color' => 'blue', 'user_name' => 'Test User'],
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
            'cable_details' => ['user_name' => 'Test User'],
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
        $response->assertJsonValidationErrors(['label', 'cable_details.user_name']);

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
            'cable_details' => ['user_name' => 'Test User'],
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
        $response = $this->actingAs($destManager)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_APPROVED_DESTINATION,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_APPROVED_DESTINATION, $ticket->fresh()->status);

        // 2. Transition to Approved Admin
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_APPROVED_ADMIN,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_APPROVED_ADMIN, $ticket->fresh()->status);

        // 3. Transition to Sended Cable
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_SENDED_CABLE,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_SENDED_CABLE, $ticket->fresh()->status);

        // 4. Transition to Received Cable
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_RECEIVED_CABLE,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(Ticket::STATUS_RECEIVED_CABLE, $ticket->fresh()->status);

        // 5. Transition to Done
        // Admin can do this
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
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
        $response = $this->actingAs($staff)->patchJson("/api/tickets/{$ticket->uuid}/status", [
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
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
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

        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
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

        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}", [
            'label' => 'TICKET-EDITED-NEW',
            'source_device' => 'Device Changed',
            'destination_device' => 'Device Changed B',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'SC',
            'cable_details' => ['user_name' => 'Test User'],
        ]);

        $response->assertStatus(200);
        $this->assertEquals('TICKET-EDITED-NEW', $ticket->fresh()->label);
        $this->assertEquals('Device Changed', $ticket->fresh()->source_device);

        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'from_state' => 'edit_details',
            'to_state' => 'edit_details',
        ]);
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
        $response1 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_APPROVED_DESTINATION,
        ]);
        $response1->assertStatus(403);

        // Attempting edit details
        $response2 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}", [
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

        $response = $this->get("/tickets/{$ticket->uuid}");
        $response->assertStatus(403);
    }

    /**
     * Test that ticket can be created and edited with custom user and network details.
     */
    public function test_custom_user_network_fields_validation_and_persistence()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Create with custom user & network fields
        $payload = [
            'label' => 'TICKET-CUSTOM-FIELDS',
            'source_device' => 'Source Dev 1',
            'destination_device' => 'Dest Dev 1',
            'source_tenant_id' => Str::uuid()->toString(),
            'destination_tenant_id' => Str::uuid()->toString(),
            'connector_type' => 'FC',
            'cable_details' => [
                'user_name' => 'Alice Client',
                'user_contact' => 'alice@example.com',
                'backhaul' => 'BH-ROUTE-1',
                'metro' => 'METRO-CORE-X',
                'destination_site' => 'Site Alpha',
                'capacity' => '100 Gbps',
                'length' => 50,
                'color' => 'Red',
                'type' => 'Multi-Mode OM4',
                'alamat' => 'Jalan Malioboro No. 12',
                'titik_koordinat' => '-7.7925, 110.3658',
                'link_maps' => 'https://maps.google.com/123',
            ],
        ];

        $response = $this->actingAs($admin)->postJson('/api/tickets', $payload);
        $response->assertStatus(201);

        $ticket = Ticket::where('label', 'TICKET-CUSTOM-FIELDS')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('Alice Client', $ticket->cable_details['user_name']);
        $this->assertEquals('alice@example.com', $ticket->cable_details['user_contact']);
        $this->assertEquals('BH-ROUTE-1', $ticket->cable_details['backhaul']);
        $this->assertEquals('METRO-CORE-X', $ticket->cable_details['metro']);
        $this->assertEquals('Site Alpha', $ticket->cable_details['destination_site']);
        $this->assertEquals('100 Gbps', $ticket->cable_details['capacity']);
        $this->assertEquals('Jalan Malioboro No. 12', $ticket->cable_details['alamat']);
        $this->assertEquals('-7.7925, 110.3658', $ticket->cable_details['titik_koordinat']);
        $this->assertEquals('https://maps.google.com/123', $ticket->cable_details['link_maps']);

        // 2. Edit custom user & network fields
        $editPayload = [
            'label' => 'TICKET-CUSTOM-FIELDS',
            'source_device' => 'Source Dev 1',
            'destination_device' => 'Dest Dev 1',
            'source_tenant_id' => $payload['source_tenant_id'],
            'destination_tenant_id' => $payload['destination_tenant_id'],
            'connector_type' => 'FC',
            'cable_details' => [
                'user_name' => 'Bob Client',
                'user_contact' => 'bob@example.com',
                'backhaul' => 'BH-ROUTE-2',
                'metro' => 'METRO-CORE-Y',
                'destination_site' => 'Site Beta',
                'capacity' => '40 Gbps',
                'length' => 45,
                'color' => 'Blue',
                'type' => 'Single-Mode OS2',
                'alamat' => 'Jalan Kaliurang KM 5',
                'titik_koordinat' => '-7.7561, 110.3789',
                'link_maps' => 'https://maps.google.com/456',
            ],
        ];

        $response2 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}", $editPayload);
        $response2->assertStatus(200);

        $freshTicket = $ticket->fresh();
        $this->assertEquals('Bob Client', $freshTicket->cable_details['user_name']);
        $this->assertEquals('bob@example.com', $freshTicket->cable_details['user_contact']);
        $this->assertEquals('BH-ROUTE-2', $freshTicket->fresh()->cable_details['backhaul']);
        $this->assertEquals('METRO-CORE-Y', $freshTicket->fresh()->cable_details['metro']);
        $this->assertEquals('Site Beta', $freshTicket->fresh()->cable_details['destination_site']);
        $this->assertEquals('40 Gbps', $freshTicket->fresh()->cable_details['capacity']);
        $this->assertEquals('Jalan Kaliurang KM 5', $freshTicket->fresh()->cable_details['alamat']);
        $this->assertEquals('-7.7561, 110.3789', $freshTicket->fresh()->cable_details['titik_koordinat']);
        $this->assertEquals('https://maps.google.com/456', $freshTicket->fresh()->cable_details['link_maps']);
    }

    /**
     * Test creating and editing a ticket with manual tenant names.
     */
    public function test_can_create_and_edit_ticket_with_manual_tenant_input()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Create with new source/destination tenant names
        $payload = [
            'label' => 'TICKET-MANUAL-TENANTS',
            'source_device' => 'Source Dev 1',
            'destination_device' => 'Dest Dev 1',
            'source_tenant_id' => 'NEW_TENANT',
            'new_source_tenant_name' => 'Manual Source Tenant',
            'destination_tenant_id' => 'NEW_TENANT',
            'new_destination_tenant_name' => 'Manual Dest Tenant',
            'connector_type' => 'LC',
            'cable_details' => ['user_name' => 'Test User'],
        ];

        $response = $this->actingAs($admin)->postJson('/api/tickets', $payload);
        $response->assertStatus(201);

        // Assert tenants were created in database
        $this->assertDatabaseHas('tenants', [
            'name' => 'Manual Source Tenant',
            'code' => 'T-MANUAL-SOURCE-TENANT',
        ]);
        $this->assertDatabaseHas('tenants', [
            'name' => 'Manual Dest Tenant',
            'code' => 'T-MANUAL-DEST-TENANT',
        ]);

        $sourceTenant = \App\Models\Tenant::where('code', 'T-MANUAL-SOURCE-TENANT')->first();
        $destTenant = \App\Models\Tenant::where('code', 'T-MANUAL-DEST-TENANT')->first();

        // Assert ticket has correct tenant IDs linked
        $ticket = Ticket::where('label', 'TICKET-MANUAL-TENANTS')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($sourceTenant->id, $ticket->source_tenant_id);
        $this->assertEquals($destTenant->id, $ticket->destination_tenant_id);

        // 2. Edit with another new tenant name
        $editPayload = [
            'label' => 'TICKET-MANUAL-TENANTS',
            'source_device' => 'Source Dev 1',
            'destination_device' => 'Dest Dev 1',
            'source_tenant_id' => $sourceTenant->id, // keep existing
            'destination_tenant_id' => 'NEW_TENANT',
            'new_destination_tenant_name' => 'Manual Dest Tenant Two',
            'connector_type' => 'SC',
            'cable_details' => ['user_name' => 'Test User'],
        ];

        $response2 = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}", $editPayload);
        $response2->assertStatus(200);

        // Assert new destination tenant was created
        $this->assertDatabaseHas('tenants', [
            'name' => 'Manual Dest Tenant Two',
            'code' => 'T-MANUAL-DEST-TENANT-TWO',
        ]);
        $destTenantTwo = \App\Models\Tenant::where('code', 'T-MANUAL-DEST-TENANT-TWO')->first();

        $freshTicket = $ticket->fresh();
        $this->assertEquals($sourceTenant->id, $freshTicket->source_tenant_id);
        $this->assertEquals($destTenantTwo->id, $freshTicket->destination_tenant_id);
    }

    /**
     * Test that transitioning a PO ticket to done requires FAB, BA files (PDF) and description.
     */
    public function test_po_ticket_done_transition_requires_files_and_keterangan()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \Illuminate\Support\Facades\Storage::fake('public');

        // Create a PO ticket at the RECEIVED_CABLE status
        $ticket = Ticket::create([
            'label' => 'PO-TEST-001',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_RECEIVED_CABLE,
        ]);

        // 1. Try to transition to DONE without files and keterangan
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_DONE,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fab_file', 'ba_file', 'keterangan']);

        // 2. Try to transition with invalid non-PDF file formats
        $invalidFile = \Illuminate\Http\UploadedFile::fake()->create('invalid.txt', 100, 'text/plain');
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_DONE,
            'fab_file' => $invalidFile,
            'ba_file' => $invalidFile,
            'keterangan' => 'Test Keterangan',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fab_file', 'ba_file']);

        // 3. Try to transition with valid PDF files
        $pdfFile1 = \Illuminate\Http\UploadedFile::fake()->create('fab.pdf', 100, 'application/pdf');
        $pdfFile2 = \Illuminate\Http\UploadedFile::fake()->create('ba.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_DONE,
            'fab_file' => $pdfFile1,
            'ba_file' => $pdfFile2,
            'keterangan' => 'Uji terima OK selesai!',
        ]);
        $response->assertStatus(200);

        $freshTicket = $ticket->fresh();
        $this->assertEquals(Ticket::STATUS_DONE, $freshTicket->status);

        // Assert file storage and database logs
        $latestLog = $freshTicket->logs()->where('to_state', Ticket::STATUS_DONE)->first();
        $this->assertNotNull($latestLog);
        $this->assertNotNull($latestLog->fab_file);
        $this->assertNotNull($latestLog->ba_file);
        $this->assertEquals('Uji terima OK selesai!', $latestLog->keterangan);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists(str_replace('/storage/', '', $latestLog->fab_file));
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists(str_replace('/storage/', '', $latestLog->ba_file));
    }

    /**
     * Test that transitioning a PO ticket to received_cable (Kirim Uji Terima) requires btest, qos proofs and network info.
     */
    public function test_po_ticket_received_cable_transition_requires_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \Illuminate\Support\Facades\Storage::fake('public');

        // Create a PO ticket at the SENDED_CABLE status (Provisioning)
        $ticket = Ticket::create([
            'label' => 'PO-TEST-002',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => Str::uuid(),
            'destination_tenant_id' => Str::uuid(),
            'connector_type' => 'LC',
            'status' => Ticket::STATUS_SENDED_CABLE,
        ]);

        // 1. Try to transition to RECEIVED_CABLE without screenshots and network details
        $response = $this->actingAs($admin)->patchJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_RECEIVED_CABLE,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'btest_proof', 'qos_proof', 'ip_ptp', 'ip_public'
        ]);
        $response->assertJsonMissingValidationErrors(['source_device', 'destination_device', 'vlan', 'device_name', 'device_port']);

        // 2. Transition successfully with correct files and fields (including optional devices)
        $btestImage = \Illuminate\Http\UploadedFile::fake()->create('btest.png', 100, 'image/png');
        $qosImage = \Illuminate\Http\UploadedFile::fake()->create('qos.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin)->postJson("/api/tickets/{$ticket->uuid}/status", [
            'status' => Ticket::STATUS_RECEIVED_CABLE,
            'btest_proof' => $btestImage,
            'qos_proof' => $qosImage,
            'ip_ptp' => '10.0.0.1/30',
            'ip_public' => '103.11.22.33',
            'vlan' => '120',
            'device_name' => 'Switch-Core',
            'device_port' => 'sfp1',
            'source_device' => 'Device X',
            'destination_device' => 'Device Y',
        ]);
        $response->assertStatus(200);

        $fresh = $ticket->fresh();
        $this->assertEquals(Ticket::STATUS_RECEIVED_CABLE, $fresh->status);
        $this->assertEquals('10.0.0.1/30', $fresh->getCableDetail('ip_ptp'));
        $this->assertEquals('120', $fresh->getCableDetail('vlan'));
        $this->assertEquals('Device X', $fresh->source_device);
        $this->assertEquals('Device Y', $fresh->destination_device);
        $this->assertNotNull($fresh->getCableDetail('btest_proof'));

        // 3. Reset and transition successfully with null/empty values for optional devices
        $ticket2 = Ticket::create([
            'label' => 'PO-TEST-003',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'status' => Ticket::STATUS_SENDED_CABLE,
        ]);
        $btestImage2 = \Illuminate\Http\UploadedFile::fake()->create('btest2.png', 100, 'image/png');
        $qosImage2 = \Illuminate\Http\UploadedFile::fake()->create('qos2.jpg', 100, 'image/jpeg');
        $response2 = $this->actingAs($admin)->postJson("/api/tickets/{$ticket2->uuid}/status", [
            'status' => Ticket::STATUS_RECEIVED_CABLE,
            'btest_proof' => $btestImage2,
            'qos_proof' => $qosImage2,
            'ip_ptp' => '10.0.0.2/30',
            'ip_public' => '103.11.22.34',
            'vlan' => '121',
            'device_name' => 'Switch-Core-2',
            'device_port' => 'sfp2',
            'source_device' => '',
            'destination_device' => null,
        ]);
        $response2->assertStatus(200);
        $fresh2 = $ticket2->fresh();
        $this->assertNull($fresh2->source_device);
        $this->assertNull($fresh2->destination_device);
    }
}
