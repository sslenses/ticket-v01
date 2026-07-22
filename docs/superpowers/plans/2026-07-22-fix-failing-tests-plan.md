# Bug Fixing & Test Suite Stabilization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix all automated test failures, runtime Blade null-pointer errors, role validation mismatches, and test storage permission issues to achieve a 100% passing test suite.

**Architecture:** Add `@auth` and null-safe checking (`auth()->check()`) in Blade layout headers, update `AuthController` validation rule for roles to include `staff` as per PRD, and fix storage disk test directory initialization.

**Tech Stack:** PHP 8.3, Laravel 11, PHPUnit.

## Global Constraints

- Preserve all existing routes, models, and PRD specifications.
- Use explicit TDD cycle: test -> fail -> fix -> pass -> commit.
- Standard role list: `admin`, `dest_manager`, `staff`, `teknisi`, `user`.

---

### Task 1: Blade Header Null-Safety Fix

**Files:**
- Modify: `resources/views/layouts/header.blade.php:23-28`, `resources/views/layouts/header.blade.php:48-74`, `resources/views/layouts/header.blade.php:137-150`
- Test: `tests/Feature/AuthTest.php:140-150`

**Interfaces:**
- Consumes: Auth helper `auth()->check()`, `auth()->user()?->hasRole(...)`
- Produces: Null-safe rendering of header layout for guest users.

- [ ] **Step 1: Write/verify failing test for guest access to layout header**

Check `tests/Feature/AuthTest.php` line 144: `test_guest_can_access_recently_completed_ticket`.
Run test to verify failure:
```bash
./vendor/bin/phpunit --filter test_guest_can_access_recently_completed_ticket
```
Expected: FAIL with `Call to a member function hasRole() on null` or similar.

- [ ] **Step 2: Add null-safe checks in `resources/views/layouts/header.blade.php`**

Replace un-guarded user calls with `@auth` / `auth()->check()`:
```blade
@if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager')))
    <a href="/users" class="text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $activeMenu == 'users' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
        Pengguna
    </a>
@endif
```
And wrap profile dropdown / user details in `@auth ... @endauth`.

- [ ] **Step 3: Run test to verify it passes**

Run:
```bash
./vendor/bin/phpunit --filter test_guest_can_access_recently_completed_ticket
```
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/views/layouts/header.blade.php
git commit -m "fix(view): add auth guard check for header layout to prevent guest null pointer"
```

---

### Task 2: AuthController Role Validation & Access Control Fix

**Files:**
- Modify: `app/Http/Controllers/AuthController.php:71`, `app/Http/Controllers/AuthController.php:114`
- Test: `tests/Feature/AuthTest.php:200-350`

**Interfaces:**
- Consumes: Request role parameter
- Produces: Valid user creation and update for role `staff`

- [ ] **Step 1: Verify failing test for user creation with role `staff`**

Run:
```bash
./vendor/bin/phpunit --filter test_admin_can_create_user
```
Expected: FAIL with `The selected role is invalid.`

- [ ] **Step 2: Update role validation rule in `AuthController.php`**

In `app/Http/Controllers/AuthController.php`:
Change:
```php
'role' => ['required', 'string', 'in:admin,dest_manager,teknisi,user'],
```
To:
```php
'role' => ['required', 'string', 'in:admin,dest_manager,staff,teknisi,user'],
```

- [ ] **Step 3: Run test to verify it passes**

Run:
```bash
./vendor/bin/phpunit --filter test_admin_can_create_user
```
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/AuthController.php
git commit -m "fix(auth): include staff role in user store and update validation rules"
```

---

### Task 3: Test Storage Permissions & Final Suite Pass Verification

**Files:**
- Modify: `tests/TestCase.php` or `tests/Feature/TicketLifecycleTest.php`
- Test: `./vendor/bin/phpunit`

**Interfaces:**
- Consumes: Storage disk setup
- Produces: 100% clean test suite run

- [ ] **Step 1: Ensure storage testing disk permissions**

Initialize storage fake directory in test setup or fix directory permissions:
```php
chmod -R 777 storage/framework/testing/disks/public
```

- [ ] **Step 2: Run full PHPUnit suite**

Run:
```bash
./vendor/bin/phpunit
```
Expected: PASS (All tests passing)

- [ ] **Step 3: Commit**

```bash
git add tests/
git commit -m "test: fix storage test setup and verify full test suite passes"
```
