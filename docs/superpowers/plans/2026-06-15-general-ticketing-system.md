# General Ticketing System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a robust ticketing module with a 6-stage state machine, RBAC, and full audit logging using Laravel and PostgreSQL.

**Architecture:** Uses a centralized `TicketStateService` to manage transitions, ensuring that each state change is validated against RBAC rules and recorded in a `ticket_logs` table for compliance.

**Tech Stack:** Laravel 11.x, PostgreSQL, PHP 8.x.

---

### Task 1: Project Initialization

- [x] **Step 1: Create Laravel Project**
Run: `composer create-project laravel/laravel .`

- [x] **Step 2: Configure Environment**
Modify `.env` to use PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ticket_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

- [x] **Step 3: Commit**
```bash
git add .
git commit -m "chore: initialize laravel project"
```

---

### Task 2: Database Migrations

- [x] **Step 1: Create Tickets Migration**
Run: `php artisan make:migration create_tickets_table`
Modify `database/migrations/xxxx_create_tickets_table.php`:
```php
Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('label')->unique();
    $table->string('source_device');
    $table->string('destination_device');
    $table->uuid('source_tenant_id');
    $table->uuid('destination_tenant_id');
    $table->string('connector_type');
    $table->jsonb('cable_details')->nullable();
    $table->string('status')->default('waiting_destination');
    $table->timestamps();
});
```

- [x] **Step 2: Create Ticket Logs Migration**
Run: `php artisan make:migration create_ticket_logs_table`
Modify `database/migrations/xxxx_create_ticket_logs_table.php`:
```php
Schema::create('ticket_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained();
    $table->string('from_state');
    $table->string('to_state');
    $table->timestamps();
});
```

- [x] **Step 3: Run Migrations**
Run: `php artisan migrate`

- [x] **Step 4: Commit**
```bash
git add .
git commit -m "feat: add migrations for tickets and logs"
```

---

### Task 3: Models & State Logic

- [x] **Step 1: Define Ticket Model Constants**
Modify `app/Models/Ticket.php`:
```php
const STATUS_WAITING_DESTINATION = 'waiting_destination';
const STATUS_APPROVED_DESTINATION = 'approved_destination';
const STATUS_APPROVED_ADMIN = 'approved_admin';
const STATUS_SENDED_CABLE = 'sended_cable';
const STATUS_RECEIVED_CABLE = 'received_cable';
const STATUS_DONE = 'done';
```

- [x] **Step 2: Create TicketStateService**
Create `app/Services/TicketStateService.php`:
```php
namespace App\Services;
use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Support\Facades\Auth;

class TicketStateService {
    public function transition(Ticket $ticket, string $toState) {
        $fromState = $ticket->status;
        $ticket->update(['status' => $toState]);
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'from_state' => $fromState,
            'to_state' => $toState,
        ]);
    }
}
```

- [x] **Step 3: Commit**
```bash
git add .
git commit -m "feat: implement state transition service"
```

---

### Task 4: RBAC & Controller

- [x] **Step 1: Define Policies**
Run: `php artisan make:policy TicketPolicy --model=Ticket`
Implement logic for each stage:
```php
public function approveDestination(User $user, Ticket $ticket) {
    return $user->hasRole('dest_manager') || $user->hasRole('admin');
}
```

- [x] **Step 2: Create TicketController**
Implement `store` and `updateStatus` methods.

- [x] **Step 3: Write Feature Test (TDD)**
Run: `php artisan make:test TicketLifecycleTest`
Verify creation and forbidden transitions.

- [x] **Step 4: Commit**
```bash
git add .
git commit -m "feat: add controller and lifecycle tests"
```
