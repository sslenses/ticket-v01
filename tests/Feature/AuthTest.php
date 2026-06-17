<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that unauthenticated users are redirected to the login page.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test that the login page renders successfully.
     */
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
    }

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login failure with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test that an authenticated user can logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test that guests can access a ticket's detail page in a simplified form.
     */
    public function test_guest_can_access_active_ticket_details_simplified(): void
    {
        $ticket = \App\Models\Ticket::create([
            'label' => 'TICKET-ACTIVE-101',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => \Illuminate\Support\Str::uuid(),
            'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
            'connector_type' => 'LC',
            'status' => \App\Models\Ticket::STATUS_WAITING_DESTINATION,
        ]);

        $response = $this->get("/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertSee('Technical Data Locked');
        $response->assertSee('Audit logs Locked');
        $response->assertDontSee($ticket->source_device);
    }

    /**
     * Test that guests cannot access a ticket's detail page if it is completed (done).
     */
    public function test_guest_cannot_access_done_ticket(): void
    {
        $ticket = \App\Models\Ticket::create([
            'label' => 'TICKET-DONE-102',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => \Illuminate\Support\Str::uuid(),
            'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
            'connector_type' => 'LC',
            'status' => \App\Models\Ticket::STATUS_DONE,
        ]);

        $response = $this->get("/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    /**
     * Test that authenticated users can access completed (done) tickets.
     */
    public function test_authenticated_user_can_access_done_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = \App\Models\Ticket::create([
            'label' => 'TICKET-DONE-103',
            'source_device' => 'Device A',
            'destination_device' => 'Device B',
            'source_tenant_id' => \Illuminate\Support\Str::uuid(),
            'destination_tenant_id' => \Illuminate\Support\Str::uuid(),
            'connector_type' => 'LC',
            'status' => \App\Models\Ticket::STATUS_DONE,
        ]);

        $response = $this->actingAs($user)->get("/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertSee($ticket->source_device);
        $response->assertDontSee('Technical Data Locked');
    }

    /**
     * Test that register page renders successfully.
     */
    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    /**
     * Test successful registration with valid details and role.
     */
    public function test_user_can_register_with_valid_details_and_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dest_manager',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', 'Registration successful! Please sign in using your credentials.');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'dest_manager',
        ]);
    }

    /**
     * Test registration fails with invalid role.
     */
    public function test_user_cannot_register_with_invalid_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test registration fails with mismatched passwords.
     */
    public function test_user_cannot_register_with_mismatched_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
            'role' => 'staff',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
        ]);
    }
}
