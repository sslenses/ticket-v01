# Design Document: Bug Fixing & Test Suite Stabilization

## Executive Summary
This design document details the fixes required to stabilize the automated test suite and address runtime null-pointer and validation issues across the General Ticketing application. The changes focus on layout null-safety, role validation alignment with the PRD, permission policy consistency, and storage test setup.

---

## 1. Problem Definition & Root Cause Analysis

1. **Header Layout Null Pointer Exception (`AuthTest`)**:
   - **Symptom**: `Call to a member function hasRole() on null` when an unauthenticated user (guest) accesses ticket views.
   - **Root Cause**: `resources/views/layouts/header.blade.php` directly accesses `auth()->user()->hasRole(...)` and `auth()->user()->name` without checking `auth()->check()`.

2. **Invalid Role Validation Failure (`AuthTest`)**:
   - **Symptom**: Validation error `The selected role is invalid.` when creating or updating users with the `staff` role.
   - **Root Cause**: `AuthController.php` validates role against `'in:admin,dest_manager,teknisi,user'`, omitting `'staff'` which is specified as a valid role in the PRD (Section 5: RBAC Matrix).

3. **RBAC & User Access Policy Mismatches (`AuthTest` & `TicketLifecycleTest`)**:
   - **Symptom**: `403 Forbidden` errors during test runs for authorized roles.
   - **Root Cause**: Policy / Controller checks enforce stricter checks than PRD specifications or test expectations for `dest_manager` and `staff` roles.

4. **Storage Permission Denied in Integration Tests**:
   - **Symptom**: `FilesystemIterator::__construct(...): Failed to open directory: Permission denied` on `storage/framework/testing/disks/public/uploads`.
   - **Root Cause**: Disk storage directory created with restrictive permissions during test execution.

---

## 2. Proposed Changes

### Component 1: Blade Layout Safety (`resources/views/layouts/header.blade.php`)
- Add `@auth` block wrappers around user-specific elements (profile dropdown, user name, role badge, logout form).
- Safely check role permissions for navigation links:
  ```blade
  @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('dest_manager')))
  ```

### Component 2: AuthController Role Validation Alignment (`app/Http/Controllers/AuthController.php`)
- Expand role validation rules in `store()` and `update()` methods:
  ```php
  'role' => ['required', 'string', 'in:admin,dest_manager,staff,teknisi,user'],
  ```
- Ensure `userList()` and `update()` permission checks align with PRD authorization requirements for `admin` and `dest_manager`.

### Component 3: Test Environment Storage Reset (`tests/TestCase.php` & Feature Tests)
- Ensure test cases initialize storage disk using `Storage::fake('public')` and clear lingering test directories with appropriate permissions.

---

## 3. Verification Plan

### Automated Tests
Run unit and feature test suite using PHPUnit:
```bash
./vendor/bin/phpunit
```
**Success Criteria**: All tests in `AuthTest.php` and `TicketLifecycleTest.php` pass cleanly with zero failures and zero errors.
