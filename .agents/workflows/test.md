# Testing & Verification Workflow

## Objective
Standardized test execution, tenant boundary verification, and browser session validation.

## Steps
1. Execute Unit & Feature test suite: `php artisan test`.
2. Execute Tenant Isolation test suite: `php artisan test --filter=TenantIsolationTest`.
3. Launch development server and run browser-based verification subagent.
4. Verify user flows: Signup → Login → Onboard School → Add Class/Teacher/Student → Mark Attendance → Generate AI Lesson Plan → Evaluate Answer Sheet → Logout.
5. Confirm zero console errors and network inspection passes.
6. Generate walkthrough report artifact.
