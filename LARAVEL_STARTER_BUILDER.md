# Laravel Starter Builder

## Professional Project Generator & Scaffolding Platform

Version: 1.0  
Status: Master Product Specification  
Target: Cursor AI Development  
Language: PHP  
Framework: Latest Stable Laravel  

## 1. Product Vision

Build a professional Laravel project generator inspired by Spring Initializr, Laravel Installer, Rails generators, Filament, Livewire, Inertia, and modern full-stack project generators.

The generator must allow developers to select:

- Laravel/PHP version
- Architecture
- Design patterns
- API
- Authentication
- RBAC
- Frontend
- Database
- Cache
- Queue
- Storage
- Admin panel
- Payments
- Social authentication
- OAuth
- Monitoring
- Docker
- Testing
- Code quality
- CI/CD
- CRUD/resource/module scaffolding

The core principle is:

> Generate exactly what the developer selected. Do not install unnecessary packages or create unnecessary abstractions.

---

## 2. CLI

Primary command:

```bash
laravel-starter new my-project
```

Additional commands:

```bash
laravel-starter list
laravel-starter validate
laravel-starter config
laravel-starter status
laravel-starter install <feature>
laravel-starter remove <feature>
laravel-starter update

laravel-starter make:crud <Model>
laravel-starter make:resource <Model>
laravel-starter make:module <Module>
laravel-starter make:service <Name>
laravel-starter make:repository <Name>
laravel-starter make:action <Name>
laravel-starter make:dto <Name>
laravel-starter make:policy <Name>
laravel-starter make:event <Name>
laravel-starter make:component <Name>
laravel-starter make:page <Name>
laravel-starter make:panel <Name>
laravel-starter make:pattern <pattern> <Name>

laravel-starter patterns
```

Support non-interactive configuration:

```bash
laravel-starter new shop \
  --frontend=next \
  --api=sanctum \
  --database=pgsql \
  --architecture=modular-monolith \
  --rbac=spatie \
  --docker
```

Support:

```bash
laravel-starter new shop --config=starter.yaml
laravel-starter new shop --dry-run
```

---

# 3. Interactive Configuration Flow

Ask in this order:

1. Application
2. Laravel/PHP version
3. Architecture
4. Design patterns
5. Backend/API
6. Authentication
7. Authorization/RBAC
8. Frontend
9. Database
10. Cache
11. Queue
12. Storage
13. Admin panel
14. Payments
15. Social authentication
16. Notifications
17. Monitoring
18. Docker
19. Testing
20. Code quality
21. CI/CD
22. Deployment
23. CRUD/module scaffolding

Before installation, display a complete installation plan and require confirmation.

---

# 4. Architecture Options

Support:

- Standard Laravel MVC
- MVC + Service Layer
- Repository Pattern
- Modular Monolith
- Domain Driven Design
- Clean Architecture
- Hexagonal Architecture
- Onion Architecture
- CQRS
- Event Driven
- Microservice Ready
- Multi-Tenant
- Custom

Allow compatible combinations.

Example:

```text
Architecture:
Modular Monolith

Patterns:
Service Layer
Repository
DTO
Action
Strategy
```

Do not recommend DDD, CQRS, microservices, or excessive abstractions for simple CRUD applications.

---

# 5. Design Pattern System

Design patterns are first-class selectable features.

Support:

## Creational

- Factory
- Abstract Factory
- Builder
- Prototype
- Singleton (with warning)

## Structural

- Adapter
- Bridge
- Composite
- Decorator
- Facade
- Flyweight
- Proxy

## Behavioral

- Strategy
- Observer
- Command
- Chain of Responsibility
- State
- Template Method
- Mediator
- Memento
- Iterator
- Visitor
- Interpreter
- Specification

Also support Laravel-oriented patterns:

- Service Layer
- Repository
- Action
- DTO
- Domain Service
- Value Object
- Domain Event
- Aggregate
- CQRS

Example:

```bash
laravel-starter make:pattern strategy Payment
laravel-starter make:pattern adapter PaymentGateway
laravel-starter make:pattern factory Notification
laravel-starter make:pattern builder Order
laravel-starter make:pattern specification Discount
laravel-starter make:pattern state Order
laravel-starter make:pattern decorator Payment
laravel-starter make:pattern command ProcessPayment
```

The generator must not create a pattern merely because it exists. Patterns must solve a real problem.

Example:

```text
Multiple payment providers
→ Strategy + Adapter + Factory

Complex business rule
→ Specification

Complex object creation
→ Builder

Domain event
→ Event / Domain Event
```

---

# 6. Pattern Registry

Patterns must be metadata-driven.

Example:

```php
[
    'id' => 'strategy',
    'name' => 'Strategy Pattern',
    'category' => 'behavioral',
    'compatible_with' => [
        'service-layer',
        'modular-monolith',
        'ddd',
    ],
    'generator' => StrategyGenerator::class,
]
```

Pattern installers must be independently testable.

The CLI must be able to list available patterns:

```bash
laravel-starter patterns
```

---

# 7. Pattern Recommendation Engine

The CLI should recommend patterns based on selected features.

Example:

```text
You selected:

Payment:
Stripe
bKash
PayPal

Recommended patterns:

✓ Strategy
✓ Adapter
✓ Factory

Reason:
Multiple payment providers require interchangeable implementations.

Apply recommendations? [Y/n]
```

Recommendations must never be silently applied.

Warn about questionable choices:

```text
You selected Singleton.

Laravel's service container is usually preferable.
Singleton can introduce hidden global state.

Continue? [y/N]
```

---

# 8. Service Layer

Option:

```text
Service Layer?
Yes / No
```

Generate:

```text
app/Services/
```

Controllers should remain thin.

---

# 9. Repository Pattern

Options:

```text
None
Basic
Interface + Implementation
Domain Repository
```

Generate:

```text
app/Repositories/
app/Contracts/Repositories/
```

DDD:

```text
Domain/Contracts/
Infrastructure/Persistence/
```

Do not create repositories just to wrap trivial Eloquent calls.

---

# 10. Action Pattern

Generate business operations such as:

```text
CreateOrderAction
RegisterCustomerAction
ProcessPaymentAction
CreateUserAction
```

Directory:

```text
app/Actions/
```

An Action normally represents one meaningful business operation.

---

# 11. DTO

Options:

```text
None
Custom DTO
Spatie Laravel Data
```

Generate:

```text
app/Data/
```

Examples:

```text
CreateOrderData
UpdateUserData
PaymentData
```

---

# 12. DDD

DDD mode supports:

- Entities
- Value Objects
- Aggregates
- Domain Services
- Domain Events
- Specifications
- Repository Interfaces

Structure:

```text
app/
├── Domain/
│   ├── Entities/
│   ├── ValueObjects/
│   ├── Aggregates/
│   ├── Events/
│   ├── Services/
│   ├── Specifications/
│   └── Contracts/
│
├── Application/
│   ├── Commands/
│   ├── Queries/
│   ├── DTOs/
│   ├── Actions/
│   └── Services/
│
├── Infrastructure/
│   ├── Persistence/
│   ├── Adapters/
│   └── External/
│
└── Presentation/
    ├── Http/
    └── Console/
```

Domain must not depend on infrastructure.

Value objects should be immutable where practical.

Aggregates must respect business boundaries.

---

# 13. Clean Architecture

Structure:

```text
app/
├── Domain/
├── Application/
├── Infrastructure/
└── Presentation/
```

Dependency direction:

```text
Presentation
    ↓
Application
    ↓
Domain

Infrastructure
    ↓
Application/Domain contracts
```

---

# 14. Hexagonal Architecture

Structure:

```text
Domain
Application
Ports
Adapters
```

Example ports:

```text
PaymentGateway
UserRepository
NotificationService
```

Adapters:

```text
StripePaymentAdapter
EloquentUserRepository
MailNotificationAdapter
```

---

# 15. Modular Monolith

Structure:

```text
Modules/
├── Auth/
├── User/
├── Admin/
├── Order/
├── Payment/
├── Notification/
└── Shared/
```

A module may contain:

```text
Domain
Application
Infrastructure
Presentation
Tests
README.md
```

Modules must have clear boundaries.

---

# 16. CQRS

Options:

```text
Disabled
Basic CQRS
CQRS + Events
```

Structure:

```text
Application/
├── Commands/
├── Queries/
└── Handlers/
```

Do not enable CQRS for simple CRUD unless the developer explicitly chooses it.

---

# 17. Microservice Ready

Do not automatically deploy multiple services.

Generate service boundaries, API contracts, events, queues, and service interfaces.

Example:

```text
services/
├── auth
├── user
├── order
└── payment
```

---

# 18. Backend / API

Options:

```text
No API
REST API
REST API + OpenAPI
```

API versioning:

```text
/api/v1
/api/v2
```

Support:

- Form Requests
- API Resources
- Validation
- Pagination
- Filtering
- Sorting
- Search
- Rate limiting
- Consistent error responses

Example:

```json
{
    "message": "Validation failed",
    "errors": {},
    "code": "VALIDATION_ERROR"
}
```

---

# 19. Authentication

Options:

```text
None
Breeze
Fortify
Sanctum
Passport
Custom
Sanctum + Passport
```

Support:

- User authentication
- Customer authentication
- Admin authentication
- Vendor authentication
- API authentication
- Social login
- OAuth2

The CLI must explain the difference between Sanctum and Passport and configure only the selected solution.

---

# 20. Sanctum

Configure:

- API tokens
- SPA authentication
- Cookies
- CSRF
- CORS
- Stateful domains

For Laravel SPA authentication, prefer secure cookie-based authentication.

---

# 21. Passport

Configure OAuth2 capabilities:

- Authorization Code
- Client Credentials
- Refresh Tokens
- Personal Access

Only install Passport when OAuth2 functionality is required.

---

# 22. Social Authentication

Support:

- Google
- Facebook
- GitHub
- LinkedIn
- Apple
- X/Twitter

Use a provider abstraction.

---

# 23. RBAC

Options:

```text
None
Custom RBAC
Spatie Permission
```

Generate:

- Roles
- Permissions
- Policies
- Middleware
- Seeders
- Authorization helpers

Support:

```text
Admin
Manager
Vendor
Customer
Staff
Custom roles
```

---

# 24. Frontend Options

Support:

```text
Blade
Livewire
Inertia React
Inertia Vue
Inertia Svelte
React
Vue
Next.js
Nuxt
Svelte
SvelteKit
Angular
```

Architecture choices:

```text
Laravel-integrated
Separate SPA
Separate SSR
Monorepo
Separate repositories
```

---

# 25. React

Support:

- React
- TypeScript
- Vite
- React Router
- TanStack Query
- Zustand
- Redux Toolkit
- Tailwind
- shadcn/ui

Structure:

```text
src/
├── components/
├── features/
├── hooks/
├── layouts/
├── pages/
├── services/
├── stores/
├── types/
└── utils/
```

---

# 26. Next.js

Support:

- Next.js
- TypeScript
- App Router
- SSR
- SSG
- ISR
- Hybrid rendering
- TanStack Query
- Zustand
- Tailwind
- shadcn/ui

Structure:

```text
app/
components/
features/
hooks/
lib/
services/
stores/
types/
middleware.ts
```

Laravel remains the backend API unless explicitly configured otherwise.

---

# 27. Vue

Support:

- Vue
- TypeScript
- Vue Router
- Pinia
- Axios
- TanStack Query
- Tailwind
- PrimeVue
- Vuetify

---

# 28. Nuxt

Support:

- Nuxt
- TypeScript
- SSR
- SSG
- Hybrid
- Pinia
- `$fetch`
- Axios
- TanStack Query
- Tailwind

Use Nuxt-native conventions.

---

# 29. Svelte / SvelteKit

Support:

- Svelte
- SvelteKit
- TypeScript
- SSR
- Routing
- Tailwind

---

# 30. Angular

Support:

- Angular
- TypeScript
- Signals
- NgRx
- Angular Material
- PrimeNG
- Tailwind

---

# 31. Inertia

Support:

```text
React
Vue
Svelte
```

Configure Laravel + Inertia automatically.

---

# 32. Frontend State

Support:

```text
None
Zustand
Redux Toolkit
Pinia
NgRx
TanStack Query
```

Prefer TanStack Query or the framework equivalent for server state.

Do not create unnecessary global state.

---

# 33. Database

Support:

```text
MySQL
PostgreSQL
MariaDB
SQLite
SQL Server
```

Optional:

```text
Redis
```

Generate correct Laravel `.env`, config, Docker services, and documentation.

---

# 34. Cache

Options:

```text
File
Database
Redis
```

---

# 35. Queue

Options:

```text
Sync
Database
Redis
SQS
```

Generate worker configuration only when needed.

---

# 36. Storage

Support:

```text
Local
S3
Cloudflare R2
MinIO
```

Only create environment variables for selected storage.

---

# 37. Docker

Options:

```text
No
Development
Development + Production
```

Possible services:

```text
Laravel
Nginx
Database
Redis
Queue
Scheduler
Mailpit
MinIO
Frontend
```

Only generate selected services.

---

# 38. Admin Panel

Options:

```text
None
Custom Admin
Filament
```

Support:

- Panels
- Resources
- Forms
- Tables
- Filters
- Actions
- Widgets
- Dashboard
- Roles
- Permissions
- Navigation

When Filament is selected, use Filament-native resources and panels rather than duplicating Filament internally.

---

# 39. Filament-Style Resource System

Provide a generic resource abstraction when custom scaffolding is selected:

```text
Resource
Form
Table
Filters
Actions
Pages
Widgets
Panel
```

Example:

```text
ProductResource
├── Form
├── Table
├── Filters
├── Actions
└── Pages
```

---

# 40. CRUD Generator

Command:

```bash
laravel-starter make:crud Product
```

Ask fields interactively.

Example:

```text
name:string|required
price:decimal|required
description:text
status:boolean
category_id:foreign
```

Generate only selected layers:

```text
Model
Migration
Factory
Seeder
Form Requests
Policy
Controller
Routes
API Resource
Frontend
Admin Resource
Tests
```

---

# 41. Resource Generator

```bash
laravel-starter make:resource Product
```

Generate:

```text
ProductResource
ProductForm
ProductTable
ProductFilters
ProductActions
```

---

# 42. Module Generator

```bash
laravel-starter make:module Product
```

Options:

```text
Domain
Application
Infrastructure
Presentation
Migration
Model
Repository
Service
DTO
Policy
API
Frontend
Tests
```

For modular monolith:

```text
Modules/Product/
├── Domain/
├── Application/
├── Infrastructure/
├── Presentation/
└── Tests/
```

---

# 43. Frontend CRUD Generation

Next.js:

```text
app/products/
├── page.tsx
├── create/page.tsx
├── [id]/page.tsx
├── [id]/edit/page.tsx
└── components/
```

Nuxt:

```text
pages/products/
├── index.vue
├── create.vue
├── [id].vue
└── [id]/edit.vue
```

Vue:

```text
pages/products/
```

React:

```text
features/products/
```

Blade:

```text
resources/views/products/
```

Livewire:

```text
app/Livewire/Products/
```

---

# 44. CRUD Field Mapping

Automatically map:

```text
string
→ Input

text
→ Textarea

boolean
→ Checkbox

integer
→ Number Input

decimal
→ Currency Input

date
→ Date Picker

datetime
→ DateTime Picker

enum
→ Select

foreign
→ Relation Select

file
→ File Upload

image
→ Image Upload
```

---

# 45. API Client Generator

For separate frontends:

```text
services/api/
├── client
├── auth
├── users
├── products
└── orders
```

Support:

```text
Fetch
Axios
TanStack Query
```

---

# 46. OpenAPI

Options:

```text
None
OpenAPI
Swagger
OpenAPI + TypeScript
```

Generate:

```text
openapi.yaml
```

Optional:

```text
api-types.ts
```

---

# 47. Payments

Support:

- Stripe
- PayPal
- bKash
- Nagad
- SSLCommerz
- Razorpay

Architecture:

```text
Controller
    ↓
PaymentService
    ↓
PaymentGatewayInterface
    ↓
Adapter / Strategy
```

Stripe features:

- Payment Intent
- Checkout
- Refund
- Webhooks
- Subscriptions when selected

Always verify webhooks.

---

# 48. Notifications

Support:

```text
Database
Email
SMS
Slack
Push
```

Use Laravel-native notifications where appropriate.

---

# 49. Monitoring

Support:

```text
Telescope
Sentry
Health Checks
Audit Logs
Performance Monitoring
```

Health endpoint:

```text
/health
```

Check:

```text
Application
Database
Cache
Queue
Storage
```

Do not expose sensitive infrastructure information publicly.

---

# 50. Multi-Tenancy

Options:

```text
None
Shared Database
Database per Tenant
Package-based
```

Generate:

```text
Tenant
TenantResolver
TenantMiddleware
TenantContext
```

Enforce tenant isolation.

---

# 51. Testing

Backend:

```text
Pest
PHPUnit
Dusk
```

Frontend:

```text
Vitest
Jest
Playwright
Cypress
```

Recommended combinations:

```text
Pest
Vitest
Playwright
```

Generated CRUD tests should cover:

- Create
- Read
- Update
- Delete
- Authorization
- Validation

---

# 52. Code Quality

Backend:

```text
Pint
PHPStan
Larastan
Rector
```

Frontend:

```text
ESLint
Prettier
TypeScript strict
Husky
lint-staged
```

---

# 53. CI/CD

Support:

```text
GitHub Actions
GitLab CI
```

Pipeline:

```text
Install dependencies
Run tests
Run formatter
Run static analysis
Build frontend
Build Docker
```

---

# 54. Security

Generated projects must include appropriate:

- CSRF protection
- CORS
- Rate limiting
- Validation
- Authorization
- Secure cookies
- Password hashing
- Webhook verification
- Mass assignment protection
- Parameterized/database framework APIs

Never disable security globally.

Never put secrets in source code.

Never recommend unsafe token storage by default.

---

# 55. Environment Configuration

Only add variables for selected features.

Stripe example:

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

Redis example:

```env
REDIS_HOST=
REDIS_PORT=
```

Next.js:

```env
NEXT_PUBLIC_API_URL=
```

Nuxt:

```env
NUXT_PUBLIC_API_URL=
```

Never commit actual secrets.

---

# 56. Feature Registry

Every feature must contain:

```text
id
name
description
dependencies
conflicts
requirements
packages
environment variables
templates
installer
validator
```

Example:

```php
[
    'id' => 'stripe',
    'name' => 'Stripe',
    'dependencies' => ['payments'],
    'installer' => StripeInstaller::class,
]
```

---

# 57. Installer Contract

```php
interface FeatureInstaller
{
    public function id(): string;

    public function name(): string;

    public function supports(StarterConfig $config): bool;

    public function validate(StarterConfig $config): array;

    public function install(StarterContext $context): void;

    public function remove(StarterContext $context): void;
}
```

---

# 58. Dependency and Conflict Resolution

Before installation:

1. Validate PHP version.
2. Validate Laravel version.
3. Validate Node version.
4. Validate package compatibility.
5. Resolve dependencies.
6. Detect conflicts.
7. Show the final plan.
8. Require confirmation.

Example:

```text
Passport requires OAuth2 configuration.
Sanctum is configured for SPA/API authentication.

You selected both.

This is valid, but they serve different purposes.

Continue? [Y/n]
```

---

# 59. Idempotency

Running an installer twice must not duplicate:

- Routes
- Service providers
- Environment variables
- Migrations
- Config entries
- Dependencies

---

# 60. File Overwrite Protection

If a file exists:

```text
File already exists.

1. Skip
2. Replace
3. Merge
4. Cancel
```

Never silently overwrite application code.

---

# 61. Transactional Generation

Generation must use a temporary workspace.

Flow:

```text
Collect configuration
        ↓
Validate
        ↓
Resolve dependencies
        ↓
Resolve conflicts
        ↓
Recommend patterns
        ↓
Show installation plan
        ↓
Confirm
        ↓
Create temporary project
        ↓
Install Composer packages
        ↓
Install NPM packages
        ↓
Generate architecture
        ↓
Generate features
        ↓
Generate patterns
        ↓
Generate frontend
        ↓
Generate Docker
        ↓
Generate tests
        ↓
Generate documentation
        ↓
Run validation
        ↓
Finalize project
```

If generation fails:

- Do not leave a broken project.
- Clean temporary files.
- Show the actual error.
- Never claim success.

---

# 62. Starter Manifest

Generate:

```text
starter.json
```

Example:

```json
{
    "version": 1,
    "laravel": "latest",
    "php": "latest",
    "architecture": "modular-monolith",
    "patterns": [
        "repository",
        "service",
        "strategy",
        "adapter",
        "dto"
    ],
    "frontend": {
        "framework": "next",
        "typescript": true,
        "router": "app",
        "state": "tanstack-query"
    },
    "api": {
        "enabled": true,
        "authentication": "sanctum"
    },
    "database": "pgsql",
    "cache": "redis",
    "queue": "redis",
    "payments": [
        "stripe",
        "bkash"
    ],
    "docker": true
}
```

---

# 63. Documentation Generation

Generate:

```text
README.md
STARTER.md
ARCHITECTURE.md
API.md
AUTH.md
DATABASE.md
DOCKER.md
DEPLOYMENT.md
AI_CONTEXT.md
```

Optional:

```text
docs/
├── architecture/
├── authentication/
├── payments/
├── frontend/
├── database/
├── deployment/
└── patterns/
```

---

# 64. Cursor / AI Rules

Generate:

```text
.cursor/
└── rules/
    ├── architecture.mdc
    ├── backend.mdc
    ├── frontend.mdc
    ├── testing.mdc
    └── security.mdc
```

Rules must reflect the selected project.

Example:

```text
Do not put business logic inside controllers.

Use Application Actions for business operations.

Use repositories only where configured.

Do not bypass domain boundaries.

Never expose secrets.

Follow the generated architecture.
```

Also generate:

```text
AI_CONTEXT.md
```

containing:

- Architecture
- Folder structure
- Design patterns
- API conventions
- Authentication
- Database
- Testing
- Frontend conventions
- Security rules

---

# 65. Presets

## Basic

```text
Laravel
Blade
MySQL
Pest
Pint
Standard MVC
```

## API

```text
Laravel
REST API
Sanctum
PostgreSQL
API Resources
Form Requests
Rate Limiting
Pest
Pint
Docker
```

## React

```text
Laravel API
React
TypeScript
TanStack Query
Tailwind
Sanctum
```

## Next.js

```text
Laravel API
Next.js
TypeScript
App Router
TanStack Query
Tailwind
Sanctum
PostgreSQL
Redis
Docker
```

## Nuxt

```text
Laravel API
Nuxt
TypeScript
Pinia
Tailwind
Sanctum
PostgreSQL
Redis
Docker
```

## SaaS

```text
Modular Monolith
Laravel API
Next.js
TypeScript
Sanctum
RBAC
Multi-tenancy
PostgreSQL
Redis
Queue
Stripe
S3
Sentry
Docker
Pest
PHPStan
```

## Enterprise

```text
DDD
Modular Monolith
Laravel API
React/Angular
OAuth2
RBAC
PostgreSQL
Redis
Queue
S3
Sentry
OpenAPI
CQRS optional
Docker
CI/CD
Pest
PHPStan
Rector
```

## E-commerce

```text
Modular Monolith
Laravel API
Livewire OR Next.js
Customer Auth
Admin Auth
Sanctum
RBAC
MySQL/PostgreSQL
Redis
Queue
S3/R2
Stripe
bKash
Nagad
Notifications
Audit Logs
Docker
Pest
```

Do not automatically generate Product/Order/etc. domains unless selected.

---

# 66. Example CLI Experience

```text
╭────────────────────────────────────────────╮
│       Laravel Starter Builder              │
│       Production Project Generator         │
╰────────────────────────────────────────────╯

Application
> ecommerce

Architecture
> Modular Monolith

Design Patterns
> Repository
> Service
> DTO
> Strategy
> Adapter
> Factory

Backend
> Laravel REST API

Authentication
> Sanctum

RBAC
> Spatie Permission

Frontend
> Next.js

TypeScript
> Yes

State Management
> TanStack Query

UI
> Tailwind + shadcn/ui

Database
> PostgreSQL

Cache
> Redis

Queue
> Redis

Storage
> S3

Payments
> Stripe
> bKash

Social Login
> Google

Monitoring
> Sentry

Docker
> Yes

Testing
> Pest
> Vitest
> Playwright

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Architecture Plan

Backend:
Laravel API

Frontend:
Next.js

Architecture:
Modular Monolith

Patterns:
Repository
Service
DTO
Strategy
Adapter
Factory

Infrastructure:
PostgreSQL
Redis
S3
Docker

Authentication:
Sanctum

RBAC:
Spatie

Payments:
Stripe + bKash

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Generate project?

> Yes
```

---

# 67. Success Output

```text
╭────────────────────────────────────────────╮
│       Project Created Successfully         │
╰────────────────────────────────────────────╯

Project:
ecommerce

Backend:
./apps/backend

Frontend:
./apps/frontend

Architecture:
Modular Monolith

Frontend:
Next.js

API:
Laravel REST API

Authentication:
Sanctum

RBAC:
Spatie Permission

Database:
PostgreSQL

Cache:
Redis

Payments:
Stripe + bKash

Patterns:
Repository
Service
DTO
Strategy
Adapter
Factory

Next steps:

cd ecommerce

cp apps/backend/.env.example apps/backend/.env

php artisan key:generate

docker compose up -d

php artisan migrate

npm install

npm run dev
```

---

# 68. Internal Architecture of the Builder

Do NOT implement the entire builder in one massive class.

Use:

```text
Domain
Application
Infrastructure
Presentation
```

The builder itself should contain:

```text
Feature Registry
Pattern Registry
Installer System
Generator System
Template System
Configuration System
Dependency Resolver
Conflict Resolver
Scaffolding Engine
```

Use metadata-driven configuration.

Avoid giant switch statements.

Prefer:

```text
FrontendDefinition
FrontendInstaller
FrontendGenerator
FrontendConfiguration
```

The same architecture should be used for:

```text
Backend
Frontend
Authentication
RBAC
Database
Infrastructure
Payments
Monitoring
Design Patterns
Scaffolding
```

Every feature, architecture, frontend generator, and design pattern must be independently testable.

---

# 69. Generated Project Structure

Standard:

```text
app/
├── Actions/
├── Console/
├── Data/
├── Exceptions/
├── Http/
├── Models/
├── Policies/
├── Services/
├── Repositories/
├── Strategies/
├── Factories/
└── Support/
```

Only create selected directories.

Full-stack:

```text
project/
├── apps/
│   ├── backend/
│   └── frontend/
├── packages/
│   └── api-types/
├── docker/
├── docs/
├── .github/
├── docker-compose.yml
├── README.md
├── ARCHITECTURE.md
├── AI_CONTEXT.md
└── starter.json
```

---

# 70. Cursor Implementation Rules

You are building this as a real production-grade developer tool.

Do not create a giant boilerplate repository.

Build a reusable generator engine.

Use SOLID principles.

Use dependency injection.

Use interfaces only when they provide meaningful abstraction.

Prefer Laravel-native features when they are the best solution.

Do not reinvent Laravel features unnecessarily.

Do not force design patterns.

Do not install unused packages.

Do not create unused modules.

Do not generate insecure authentication.

Do not store secrets in source code.

Do not silently overwrite user files.

Do not silently change an existing project architecture.

Always show what will be installed.

Always validate dependencies and conflicts.

Always generate tests for the generator itself.

Always generate documentation for generated projects.

The final result should feel like:

> Spring Initializr + Laravel Installer + Filament Resource Generator + modern full-stack scaffolding + architecture advisor.

---

# 71. Acceptance Criteria

The system is complete when:

- [ ] CLI works
- [ ] Interactive mode works
- [ ] Non-interactive mode works
- [ ] YAML configuration works
- [ ] JSON configuration works
- [ ] Dry-run works
- [ ] Feature registry works
- [ ] Dependency resolver works
- [ ] Conflict resolver works
- [ ] Pattern registry works
- [ ] Pattern recommendation works
- [ ] Standard Laravel works
- [ ] Service Layer works
- [ ] Repository works
- [ ] Modular Monolith works
- [ ] DDD works
- [ ] Clean Architecture works
- [ ] Hexagonal Architecture works
- [ ] CQRS works
- [ ] Event Driven works
- [ ] Multi-tenancy works
- [ ] Blade works
- [ ] Livewire works
- [ ] Inertia React works
- [ ] Inertia Vue works
- [ ] React works
- [ ] Vue works
- [ ] Next.js works
- [ ] Nuxt works
- [ ] Svelte works
- [ ] SvelteKit works
- [ ] Angular works
- [ ] Sanctum works
- [ ] Passport works
- [ ] Customer authentication works
- [ ] Admin authentication works
- [ ] Social authentication works
- [ ] OAuth works
- [ ] Custom RBAC works
- [ ] Spatie RBAC works
- [ ] Filament works
- [ ] CRUD generation works
- [ ] Resource generation works
- [ ] Page generation works
- [ ] Component generation works
- [ ] Module generation works
- [ ] Frontend CRUD works
- [ ] OpenAPI works
- [ ] Type generation works
- [ ] API client generation works
- [ ] MySQL works
- [ ] PostgreSQL works
- [ ] MariaDB works
- [ ] SQLite works
- [ ] SQL Server works
- [ ] Redis works
- [ ] Queue works
- [ ] S3 works
- [ ] Cloudflare R2 works
- [ ] Docker works
- [ ] Stripe works
- [ ] PayPal works
- [ ] bKash works
- [ ] Nagad works
- [ ] SSLCommerz works
- [ ] Notifications work
- [ ] Sentry works
- [ ] Telescope works
- [ ] Health checks work
- [ ] Audit logs work
- [ ] Pest works
- [ ] PHPUnit works
- [ ] Vitest works
- [ ] Playwright works
- [ ] PHPStan works
- [ ] Larastan works
- [ ] Pint works
- [ ] Rector works
- [ ] ESLint works
- [ ] Prettier works
- [ ] CI/CD works
- [ ] Documentation generation works
- [ ] AI_CONTEXT.md generated
- [ ] Cursor rules generated
- [ ] starter.json generated
- [ ] Generated applications pass tests
- [ ] Generated applications are production-ready

# END OF SPECIFICATION
