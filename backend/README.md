# 🧠 EMS AI Memory Layer (Persistent Rules)

> This file is the single source of truth for all AI agents and developers working on EMS (Employee Management System).
> MUST NOT be deleted or rewritten. Only append updates.

---

# 📌 1. Project Identity

- Name: Employee Management System (EMS)
- Type: RESTful API (Laravel)
- Architecture: Modular Monolith (Clean Architecture)
- Database: MySQL (Eloquent ORM)
- Auth: JWT (tymon/jwt-auth)

---

# 🌍 2. Domain Overview

EMS is a business system for managing:

- Users & Roles
- Employees
- Departments
- Salaries
- Leaves
- Attendance
- Reporting & Analytics

---

# 🧱 3. Database Policy

- UUID for primary keys (preferred)
- Strict foreign key constraints
- Relations MUST NOT be broken
- No schema changes without approval

---

# 🏗 4. Architecture Rules (MANDATORY)

Controller → Service → Repository → Model

| Layer | Responsibility |
|------|--------------|
| Controller | Request/Response only |
| Service | Business Logic |
| Repository | DB access |
| Model | Eloquent models |
| Resource | API formatting |

---

# 🚫 Rules

- NO business logic in controllers
- NO DB queries in controllers
- NO raw models returned
- ALWAYS use Resources
- ALWAYS use Services

---

# 📡 5. API Response Standard

Success:
{
  "success": true,
  "message": "string",
  "data": {},
  "meta": {}
}

Error:
{
  "success": false,
  "message": "string",
  "errors": {},
  "code": "ERROR_CODE"
}

---

# 🔐 6. Authentication & Roles

Roles:
- admin
- manager
- employee

JWT Authentication:
- Stateless
- Token blacklist on logout
- Rate limit login

---

# 🧩 7. Core Systems

### Users
- CRUD (admin only)

### Employees
- Linked to departments
- Profile management

### Departments
- Has manager
- Has employees

### Salaries
- base_salary
- bonuses
- deductions

### Leaves
- apply / approve / reject

### Attendance
- check-in / check-out

---

# 🔄 8. Feature Workflow (MANDATORY)

For every feature:

1. FormRequest
2. Service
3. Repository
4. Resource
5. Controller
6. Routes
7. Postman
8. Tests

---

# 🧪 9. Testing Policy

- Pest required
- Cover:
  - success
  - validation
  - authorization
  - edge cases

---

# 📬 10. Postman Rules

- NEVER delete requests
- ALWAYS add new
- Include:
  - request
  - headers
  - body
  - response

---

# 🚀 11. Execution Roadmap

Phase 1: Auth + Roles
Phase 2: Users
Phase 3: Employees + Departments
Phase 4: Salary + Leaves + Attendance
Phase 5: Reports + Analytics
Phase 6: Optimization + Caching

---

# 🚫 12. Guardrails

NEVER:
- break architecture
- expose sensitive data
- skip validation
- skip service layer

ALWAYS:
- use DTO/Resources
- validate requests
- handle errors properly

---

# 🧠 13. Logging Rules

- Log login event:
  "We are logged in to the EMS OSP"

- Log:
  - user creation
  - leave approval
  - salary update

---

# 🚀 14. Performance Rules

- DB filtering ONLY (no collection filtering)
- Use pagination
- Avoid N+1
- Use eager loading
- Index frequently used columns

---

# 🧠 15. Agent Behavior Rules

Agent MUST:

- follow clean architecture
- never skip layers
- always validate inputs
- always optimize queries
- always update Postman
- always write production code

---

# 📋 16. Current Status

NOT STARTED

---

# 🔄 17. Memory Policy

- NEVER delete content
- ONLY append
- version updates required

---

# 📘 18. README Maintenance Rule (MANDATORY)

* README MUST be updated after every completed feature
* README update is part of Definition of Done
* A feature is NOT considered complete without updating README

## Update Policy

* Updates MUST be append-only
* NEVER rewrite or delete existing README content

## Required Sections to Update

* Features section
* Project Status section

---

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

<<<<<<< HEAD
- Simplified User Management scope to admin-only for the current project phase.
- Enforced admin-only authorization across all user endpoints: index, show, store, update, and destroy.
- Removed early-phase manager and employee user-management read complexity from service and repository paths.
- Standardized non-admin user-management access denial response to `Forbidden. Admin access is required.` with `AUTH_FORBIDDEN`.
- Simplified Postman Users folder to admin-only requests and removed manager/employee users listing requests.

## Project Status

- User Management Scope Correction (Admin-Only Phase): Completed.
- Users RBAC Read Complexity Rollback: Completed.
- Postman Users Scope Simplification: Completed.

## Features

- Implemented production-grade Departments module using clean architecture (Controller -> Service -> Repository -> Model -> Resource).
- Added admin-only department CRUD endpoints with standardized forbidden response: `Forbidden. Admin access is required.` and `AUTH_FORBIDDEN`.
- Added strict FormRequest validation for department create/update payloads, including manager role enforcement on `manager_id`.
- Added paginated department listing with response pagination metadata and eager loading for manager role data to avoid N+1 queries.
- Added Department API resource output with DTO-safe fields and nested manager role details.
- Added migration for `departments.manager_id` (UUID, nullable) with foreign key to `users.id` for manager assignment.
- Added Postman Departments folder with all CRUD requests, token pre-request checks, and automatic `department_id` storage after successful create.
- Added Pest feature tests for admin/non-admin access, CRUD behavior, validation errors, manager role validation, and not-found handling.

## Project Status

- Departments Module (Admin-Only CRUD): Completed.
- Department Manager Assignment (`manager_id`): Completed.
- Department Pagination and Resource Output: Completed.
- Departments Testing and Postman Artifacts: Completed.

## Features

- Implemented production-grade Employee module using clean architecture (Controller -> Service -> Repository -> Model -> Resource).
- Added admin-only employee CRUD endpoints: `GET /api/employees`, `GET /api/employees/{id}`, `POST /api/employees`, `PUT /api/employees/{id}`, `DELETE /api/employees/{id}`.
- Added strict FormRequest validation for employee create/update payloads with required `user_id` and `department_id`, UUID validation, and role guard ensuring `user_id` belongs to employee role only.
- Added service-level duplicate profile protection to prevent linking the same user to more than one employee profile.
- Added paginated employees listing with eager loading for `user.role` and `department` relations to avoid N+1.
- Added Employee API resource exposing safe fields only (`id`, `user_id`, `department_id`, `phone`, `address`, timestamps) with nested safe user and department details.
- Added Postman Employees folder with all CRUD requests, token pre-request checks, and automatic `employee_id` storage after successful create.
- Added Pest feature tests for employee CRUD, admin-only authorization, validation failures, role constraints, duplicate prevention, and not-found behavior.

## Project Status

- Employees Module (Admin-Only CRUD): Completed.
- Employee Role Validation and Duplicate Link Guardrails: Completed.
- Employees Pagination, Resource Output, and Postman Coverage: Completed.
- Employees Testing and Stability Verification: Completed.

## Features

- Hardened admin authorization reuse by introducing a shared service concern and dedicated admin access exception across Users, Departments, and Employees modules.
- Added business-level duplicate employee protection contract with exact response code `EMPLOYEE_ALREADY_EXISTS` and message `Employee profile already exists for this user.`.
- Added optional DB-level filtering for employees (`department_id`, `search` on related user name/email) and departments (`search` on department name) while preserving pagination behavior.
- Standardized forbidden handling paths through centralized exceptions without breaking existing endpoint contracts.
- Fixed Postman employee flow by storing `employee_user_id` in Login Employee tests and using it in Create Employee request body.
- Extended Pest feature coverage for duplicate employee edge case contract, malformed UUID validation, employees/department filtering behavior, forbidden consistency, and pagination `last_page` consistency.

## Project Status

- System Hardening for Auth/Users/Departments/Employees: Completed.
- Admin Authorization Centralization: Completed.
- Employee Duplicate Contract Alignment (`EMPLOYEE_ALREADY_EXISTS`): Completed.
- Optional Filtering and Pagination Consistency Improvements: Completed.
- Postman Employee Variable Flow Fix (`employee_user_id`): Completed.

## Features

- Implemented production-grade Salary module using clean architecture (Controller -> Service -> Repository -> Model -> Resource).
- Added strict confidential salary access policy with admin-only authorization for all salary endpoints.
- Enforced privacy rule that managers and employees cannot access salary endpoints, including managers in their own department and employees for their own salary.
- Added salary API endpoints: `GET /api/salaries`, `GET /api/salaries/{id}`, `POST /api/salaries`, `PUT /api/salaries/{id}`, `DELETE /api/salaries/{id}`.
- Added FormRequest validation for salary payloads using `employee_id`, `amount`, `bonus`, and `deductions`.
- Added model accessor-based `net_salary` calculation in `Salary` model and exposed it via resource output.
- Added optional duplicate salary guard for one salary record per employee with conflict response handling.
- Added Postman Salaries folder with request scripts and `salary_id` environment variable automation.
- Added Pest feature tests for salary creation, manager salary assignment, strict non-admin denial, duplicate guard, and accessor-driven net salary output.

## Project Status

- Salary Module: Completed.
- Salary Confidentiality (Admin-Only): Completed.
- Manager and Employee Salary Access Restriction: Completed.
- Salary Postman Artifacts and Automated Tests: Completed.

## Features

- Upgraded Employee domain modeling so employee profiles can be created for both `employee` and `manager` role users, while `admin` remains strictly excluded.
- Updated employee create/update validation to use `Rule::exists(...)->whereIn(...)` logic for allowed roles (`employee`, `manager`) and reject admin users.
- Preserved one-employee-profile-per-user rule with existing DB unique constraint on `employees.user_id` and service-layer duplicate guard.
- Kept Salary architecture unchanged (`salary -> employee_id`) and confirmed compatibility with manager-as-employee salary assignment.
- Updated Postman auth flow so manager login also stores `employee_user_id`, enabling manager employee-profile flows without manual variable edits.
- Added and aligned tests for manager employee-profile creation, admin exclusion from employee profiles, and manager/employee salary compatibility.

## Project Status

- Employee Domain Modeling Upgrade (Manager as Employee): Completed.
- Admin Exclusion from Employee Profiles: Completed.
- Salary Compatibility with Manager Employee Profiles: Completed.

## Features

- Implemented production-grade Leave Management module using clean architecture (Controller -> Service -> Repository -> Model -> Resource).
- Added leave API endpoints: `GET /api/leaves`, `GET /api/leaves/{id}`, `POST /api/leaves`, `PUT /api/leaves/{id}`, `PATCH /api/leaves/{id}/approve`, and `PATCH /api/leaves/{id}/reject`.
- Enforced role workflow rules: employees can apply and manage their own pending leaves, managers can review and decide leaves only in their managed departments, and admins are restricted to view-only access across all departments.
- Added strict FormRequest validation for leave create/update payloads with date constraints (`start_date >= today`, `end_date >= start_date`).
- Implemented overlap prevention in repository layer using DB-level interval checks excluding rejected requests.
- Enforced pending-only leave updates and pending-only manager decision operations (approve/reject).
- Added approval audit fields handling (`approved_by`, `approved_at`) during manager decisions.
- Added paginated, role-scoped leave listing with DB-level filtering and response metadata.
- Added Postman Leaves folder with all leave endpoints, token pre-request checks, and automatic `leave_id` capture after successful apply.
- Added comprehensive Pest Leave feature tests for apply, role-scoped visibility, department decision boundaries, overlap blocking, validation errors, and non-pending update protections.

## Project Status

- Leave Management Module: Completed.
- Leave Workflow Authorization and Department Boundaries: Completed.
- Leave Overlap Validation and Pending-State Guardrails: Completed.
- Leave Postman Artifacts and Automated Test Coverage: Completed.

## Features

- Implemented production-grade Attendance Tracking module using clean architecture (Controller -> Service -> Repository -> Model -> Resource).
- Added attendance endpoints: `POST /api/attendance/check-in`, `POST /api/attendance/check-out`, `GET /api/attendance`, and `GET /api/attendance/{id}`.
- Enforced strict daily attendance rules: one check-in per employee per day, check-out requires prior check-in, and duplicate check-out is blocked.
- Added leave integration guardrail to block attendance check-in when an employee has an approved leave covering the current server date.
- Implemented role-based attendance visibility with DB-level filtering: employees see own records, managers see only managed department attendance, and admins can view all attendance.
- Added optional attendance listing filters (`date`, `employee_id`) with paginated response metadata.
- Added Attendance API resource with safe nested `employee`, `user`, and `department` fields.
- Implemented `total_hours` in Attendance model via accessor (difference between check-out and check-in) to keep calculation logic out of controller/service layers.
- Added Postman Attendance folder with token pre-request validation and automatic `attendance_id` variable storage.
- Added comprehensive Pest attendance tests covering check-in/check-out workflows, leave blocking, role-based visibility, and total hours computation.

## Project Status

- Attendance Tracking Module: Completed.
- Attendance Check-In and Check-Out Workflow: Completed.
- Attendance Leave Integration and Role-Based Access Control: Completed.
- Attendance Postman Artifacts and Automated Test Coverage: Completed.

## Features

- Implemented production-grade Reporting & Analytics module focused on management insights with DB-level aggregations only.
- Added report endpoints under `/api/reports`: `employees`, `departments`, `attendance`, `salaries`, and `leaves`.
- Enforced admin-only access for all reporting endpoints using existing centralized admin guardrails.
- Added employee analytics: total employees, employees per department, and employees per role.
- Added department analytics: total departments, employees count per department, and manager details per department.
- Added attendance analytics with date-range filtering (`from_date`, `to_date`), including computed absence metrics.
- Added salary analytics using net salary expression (`base_salary + bonus - deduction`) for total, average, min, max, and per-department distribution.
- Added leave analytics for total, approved, rejected, pending leaves, and per-department leave counts.
- Added Reports Postman folder with all report requests and token pre-request checks.
- Added Pest reporting feature tests for access control and analytics correctness.

## Project Status

- Reporting & Analytics Module: Completed.
- Admin-Only Reporting Access Control: Completed.
- Attendance, Salary, and Leave Aggregation Insights: Completed.
- Reporting Postman Artifacts and Automated Test Coverage: Completed.

## Reporting Computed Fields

## Features

- Enhanced Salary API payload with canonical salary fields: `base_salary`, `bonus`, `deduction`, and accessor-driven `net_salary`.
- Preserved backward compatibility by keeping existing alias fields (`amount`, `deductions`) while exposing canonical fields.
- Enforced non-negative salary validation rules for create and update requests (`base_salary/amount`, `bonus`, `deduction/deductions`).
- Added optional monthly salary filtering via query parameter: `GET /api/salaries?month=YYYY-MM`.
- Updated salary service/repository flow to apply month filtering at repository level and avoid business logic duplication.
- Extended Pest salary feature tests to cover canonical salary fields and month filter behavior.

## Project Status

- Salary Net Calculation Enhancement: Completed.
- Salary Canonical Fields + Backward Compatibility: Completed.
- Salary Monthly Filter (`month=YYYY-MM`): Completed.
- Salary Validation Hardening (non-negative values): Completed.

- `total_absent` is computed, not stored: `max(total_employees - employees_with_attendance, 0)`.
- `attendance_percentage` is computed as: `(total_present / total_employees) * 100` (or `0` when no employees).

## Features

- Persisted salary `net_salary` in the database with dedicated migration column `net_salary DECIMAL(10,2)`.
- Added migration-level backfill to populate existing salary records using: `base_salary + bonus - deduction`.
- Moved net salary write logic to Salary service create/update flow to ensure DB and API consistency.
- Enforced always-recalculate behavior for `net_salary` on every salary update operation.

## Project Status

- Salary Net Salary Persistence (DB column + backfill): Completed.
- Salary Service Net Recalculation on Create/Update: Completed.
- Salary API Net Salary Consistency with Stored Value: Completed.

## Features

- Added advanced employees listing filters on `GET /api/employees`: `name` (partial match), `department_id`, and `role` with DB-level filtering and pagination preserved.
- Added attendance listing date-range filters on `GET /api/attendance`: `from_date`, `to_date`, and existing `employee_id` support.
- Added leave listing filters on `GET /api/leaves`: `status`, `employee_id`, `from_date`, and `to_date`.
- Added query validation for employees, attendance, and leaves list endpoints with consistent API validation envelope handling.
- Added structured domain logging for attendance check-in/check-out, salary create/update, and leave apply/approve/reject actions.

## Project Status

- Search & Filter Improvements (Employees, Attendance, Leaves): Completed.
- Query Validation for List Filters: Completed.
- Structured Logging for Attendance, Salary, and Leaves Events: Completed.

## Features

- Validation & Error Handling hardening completed across API list endpoints by introducing validated query FormRequests for Users, Departments, and Salaries.
- Unified API exception rendering expanded for route not found, method not allowed, generic HTTP errors, and unhandled server exceptions.
- API validation failures consistently return `success=false`, `message=Validation failed.`, `errors`, and `code=VALIDATION_ERROR`.
- API auth and permission failures consistently return standardized error envelopes with `AUTH_UNAUTHORIZED` and `AUTH_FORBIDDEN` codes.

## Validation & Error Handling

- Global API exception rendering is centralized in `bootstrap/app.php` and enforces JSON error envelopes for API requests.
- Validation behavior includes UUID, exists, numeric, enum, and date-order constraints for supported query/body inputs.
- API error responses do not expose stack traces and use stable error codes for consumers.

## Logging & Accessors

- Login logging includes required message `We are logged in to the EMS OSP` with structured context (`user_id`, `email`, `role`, `logged_at`).
- Structured logs are emitted for attendance (`check-in`, `check-out`), leaves (`apply`, `approve`, `reject`), and salary (`create`, `update`) actions.
- Salary accessor `net_salary` is implemented in the Salary model.
- Attendance accessor `total_hours` is implemented in the Attendance model.

## Project Status

- Validation & Error Handling Standardization: Completed.
- Logging & Accessors Verification and Completion: Completed.

## Features

- Added automated absent marking with scheduled background execution using `MarkEmployeesAbsentJob` at 23:59 daily.
- Introduced centralized attendance policy configuration in `config/attendance.php` with `work_start`, `late_after`, and `work_end` values.
- Implemented overtime computation accessor on attendance records and exposed `overtime_hours` in Attendance API resources.
- Extended attendance reporting to include `total_overtime_hours` within the attendance analytics payload.
- Added structured logging for auto-absent job lifecycle and overtime detection events.
- Added dedicated Pest coverage for auto-absent creation, leave exclusion, duplicate prevention, and overtime reporting/accessor behavior.

## Project Status

- Auto Absent Scheduling and Daily Marking: Completed.
- Attendance Policy Configuration Centralization: Completed.
- Overtime Accessor and Reporting Aggregation: Completed.
- Auto Absent and Overtime Test Coverage: Completed.
=======
</laravel-boost-guidelines>
>>>>>>> c2f376ca134e4147be831328ec2266a756ba07b2
