# RBAC & Controller Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement RBAC using Policies and create TicketController for ticket lifecycle management.

**Architecture:** Use Laravel Policies for authorization logic and TicketController for API endpoints. Roles will be managed via a `role` column in the `users` table.

**Tech Stack:** Laravel, PHPUnit.

---

### Task 1: Add Role to User Model

**Files:**
- Create: `database/migrations/2026_06_15_080000_add_role_to_users_table.php`
- Modify: `app/Models/User.php`

- [x] **Step 1: Create migration to add role column**
```bash
php artisan make:migration add_role_to_users_table --table=users
```
Add `$table->string('role')->default('user')->after('email');` to the migration.

- [x] **Step 2: Run migration**
```bash
php artisan migrate
```

- [x] **Step 3: Implement hasRole in User model**
```php
public function hasRole(string $role): bool
{
    return $this->role === $role;
}
```

- [x] **Step 4: Update User model fillable**
Add `role` to `#[Fillable]` or `$fillable`.

---

### Task 2: Create TicketPolicy

**Files:**
- Create: `app/Policies/TicketPolicy.php`
- Modify: `app/Providers/AppServiceProvider.php` (if needed for auto-discovery)

- [x] **Step 1: Generate Policy**
```bash
php artisan make:policy TicketPolicy --model=Ticket
```

- [x] **Step 2: Implement logic for each stage**
```php
public function approveDestination(User $user, Ticket $ticket) {
    return $user->hasRole('dest_manager') || $user->hasRole('admin');
}
public function approveAdmin(User $user, Ticket $ticket) {
    return $user->hasRole('admin');
}
public function sendCable(User $user, Ticket $ticket) {
    return $user->hasRole('admin');
}
public function receiveCable(User $user, Ticket $ticket) {
    return $user->hasRole('admin');
}
public function markDone(User $user, Ticket $ticket) {
    return $user->hasRole('admin');
}
```

---

### Task 3: Create TicketController

**Files:**
- Create: `app/Http/Controllers/TicketController.php`
- Modify: `routes/web.php` or `routes/api.php`

- [x] **Step 1: Generate Controller**
```bash
php artisan make:controller TicketController
```

- [x] **Step 2: Implement store method**
Use `StoreTicketRequest` (need to create this if it doesn't exist).
```php
public function store(StoreTicketRequest $request)
{
    $ticket = Ticket::create($request->validated() + ['status' => Ticket::STATUS_WAITING_DESTINATION]);
    return response()->json($ticket, 201);
}
```

- [x] **Step 3: Create StoreTicketRequest**
```bash
php artisan make:request StoreTicketRequest
```
Implement validation rules.

- [x] **Step 4: Implement updateStatus method**
```php
public function updateStatus(Request $request, Ticket $ticket, string $status)
{
    $this->authorize($status, $ticket);
    $this->stateService->transition($ticket, $status);
    return response()->json($ticket);
}
```
Wait, Laravel Policy methods usually match the action name. I should map `$status` to policy method.

---

### Task 4: Write Feature Test (TDD)

**Files:**
- Create: `tests/Feature/TicketLifecycleTest.php`

- [x] **Step 1: Write tests for creation and forbidden transitions**
Verify 'dest_manager' can ONLY approve destination.

- [x] **Step 2: Run tests**
```bash
php artisan test tests/Feature/TicketLifecycleTest.php
```

---

### Task 5: Final Review & Commit

- [x] **Step 1: Self-review**
- [x] **Step 2: Commit**
```bash
git add .
git commit -m "feat: add controller and lifecycle tests"
```
