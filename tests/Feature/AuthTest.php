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
     * Test that register page is no longer accessible.
     */
    public function test_register_page_is_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    /**
     * Test that admin can create a user.
     */
    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'New Staff Member',
            'email' => 'newstaff@example.com',
            'role' => 'staff',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'New Staff Member',
            'email' => 'newstaff@example.com',
            'role' => 'staff',
        ]);
    }

    /**
     * Test that user creation validates email uniqueness.
     */
    public function test_admin_cannot_create_user_with_duplicate_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Duplicate Email User',
            'email' => 'duplicate@example.com',
            'role' => 'staff',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /**
     * Test that dest_manager cannot create a user.
     */
    public function test_dest_manager_cannot_create_user(): void
    {
        $destManager = User::factory()->create(['role' => 'dest_manager']);

        $response = $this->actingAs($destManager)->postJson('/api/users', [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
            'role' => 'staff',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
    }

    /**
     * Test that unauthorized roles cannot create a user.
     */
    public function test_staff_cannot_create_user(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->postJson('/api/users', [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
            'role' => 'staff',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access the user list.
     */
    public function test_unauthenticated_user_cannot_access_user_list(): void
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users cannot access the user list.
     */
    public function test_non_admin_user_cannot_access_user_list(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);
    }

    /**
     * Test that admin users can access the user list.
     */
    public function test_admin_user_can_access_user_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'staff', 'name' => 'John Staff']);

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('John Staff');
    }

    /**
     * Test that dest_manager users can access the user list.
     */
    public function test_dest_manager_user_can_access_user_list(): void
    {
        $destManager = User::factory()->create(['role' => 'dest_manager']);
        $user = User::factory()->create(['role' => 'staff', 'name' => 'John Staff']);

        $response = $this->actingAs($destManager)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('John Staff');
    }

    /**
     * Test that dest_manager can edit users but cannot edit admin or change roles.
     */
    public function test_dest_manager_can_edit_users_with_restrictions(): void
    {
        $destManager = User::factory()->create(['role' => 'dest_manager']);
        $targetUser = User::factory()->create(['role' => 'staff', 'name' => 'Original Name', 'email' => 'original@example.com']);
        $adminUser = User::factory()->create(['role' => 'admin', 'name' => 'Admin Name']);

        // Can edit staff name and email
        $response = $this->actingAs($destManager)->patchJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'staff',
        ]);

        // Cannot change role (dest_manager role input is ignored)
        $response = $this->actingAs($destManager)->patchJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated Name Again',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'staff',
        ]);

        // Cannot edit admin user
        $response = $this->actingAs($destManager)->patchJson("/api/users/{$adminUser->id}", [
            'name' => 'Hacked Admin Name',
            'email' => 'hacked@example.com',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test that dest_manager cannot delete users.
     */
    public function test_dest_manager_cannot_delete_users(): void
    {
        $destManager = User::factory()->create(['role' => 'dest_manager']);
        $targetUser = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($destManager)->deleteJson("/api/users/{$targetUser->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
    }

    /**
     * Test that admin can edit any user's details and roles.
     */
    public function test_admin_can_edit_any_user_and_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'staff', 'name' => 'Original Name']);

        $response = $this->actingAs($admin)->patchJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated Name By Admin',
            'email' => 'updatedbyadmin@example.com',
            'role' => 'dest_manager',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name By Admin',
            'email' => 'updatedbyadmin@example.com',
            'role' => 'dest_manager',
        ]);
    }

    /**
     * Test that admin can delete users but cannot delete themselves.
     */
    public function test_admin_can_delete_users_but_not_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'staff']);

        // Can delete other user
        $response = $this->actingAs($admin)->deleteJson("/api/users/{$targetUser->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);

        // Cannot delete self
        $response = $this->actingAs($admin)->deleteJson("/api/users/{$admin->id}");
        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
