# Task 3: Models & State Logic Implementation Plan

I'm using the writing-plans skill to create the implementation plan.

**Goal:** Implement Ticket model constants and a service for state transitions with logging.

**Architecture:** Use Laravel models for data representation and a dedicated Service class for business logic. Use a log model to track state changes.

**Tech Stack:** Laravel, PHP, PHPUnit.

---

### Task 3.1: Setup Models

**Files:**
- Create: `app/Models/Ticket.php`
- Create: `app/Models/TicketLog.php`

- [x] **Step 1: Create Ticket model with constants**
    - Create `app/Models/Ticket.php` with the required constants and fillable properties.
- [x] **Step 2: Create TicketLog model**
    - Create `app/Models/TicketLog.php` with fillable properties and basic relationships.
- [x] **Step 3: Commit models**
    - `git add app/Models/Ticket.php app/Models/TicketLog.php`
    - `git commit -m "feat: add Ticket and TicketLog models"`

### Task 3.2: Implement TicketStateService with TDD

**Files:**
- Create: `app/Services/TicketStateService.php`
- Create: `tests/Feature/Services/TicketStateServiceTest.php`

- [x] **Step 1: Write failing test for state transition**
    - Create a test that asserts a ticket's status changes and a log entry is created.
- [x] **Step 2: Run test to verify it fails**
    - Run: `docker compose exec app php artisan test tests/Feature/Services/TicketStateServiceTest.php`
- [x] **Step 3: Implement TicketStateService**
    - Create `app/Services/TicketStateService.php` with the `transition` method.
- [x] **Step 4: Run test to verify it passes**
    - Run: `docker compose exec app php artisan test tests/Feature/Services/TicketStateServiceTest.php`
- [x] **Step 5: Commit service and tests**
    - `git add app/Services/TicketStateService.php tests/Feature/Services/TicketStateServiceTest.php`
    - `git commit -m "feat: implement TicketStateService for state transitions"`
