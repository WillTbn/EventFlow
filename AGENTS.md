<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines — EventFlow (Fact Sphere)

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Atalhos: docs/ (navegacao rapida)

### Indice rapido
- Base e visao geral
- Multi-tenant e arquitetura
- Frontend (Inertia + Vue)
- PWA e admin
- Planos e quotas

### Base e visao geral
- docs/_PROJECT_GUIDE.md
- docs/visao-geral.md

### Multi-tenant e arquitetura
-docs/ _PROJECT_GUIDE.md

### Frontend (Inertia + Vue)
- docs/skills/inertia-vue-development/SKILL.md

### PWA e admin
- docs/skills/inertia-vue-development/SKILL.md
- docs/skills/wayfinder-development/SKILL.md
- docs/skills/tailwindcss-development/SKILL.md

### Planos e quotas
- docs/_PROJECT_GUIDE.md
- docs/_DONE_CHECKLIST.md

## Foundational Context
This application is a Laravel application and its main Laravel ecosystem packages & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.1
- laravel/framework (LARAVEL) - v12.48.1
- laravel/fortify (FORTIFY) - v1.33.0
- laravel/prompts (PROMPTS) - v0.3.10
- laravel/wayfinder (WAYFINDER) - v0.1.13
- laravel/pint (PINT) - v1.27.0
- laravel/sail (SAIL) - v1.52.0
- phpunit/phpunit (PHPUNIT) - v11.5.49
- inertiajs/inertia-laravel (INERTIA) - v2.0.19
- @inertiajs/vue3 (INERTIA_VUE) - v2.3.10
- vue (VUE) - v3.5.26
- tailwindcss (TAILWINDCSS) - v4.1.18
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0.1.7
- eslint (ESLINT) - v9.39.2
- prettier (PRETTIER) - v3.8.0

## Conventions
- Follow existing code conventions. Check sibling files for structure, naming, and approach.
- Use descriptive names for variables and methods.
- Reuse existing components before creating new ones.
- Only create documentation files when explicitly requested by the user.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality.

## Application Structure & Architecture
- Stick to existing directory structure; do not create new base folders without approval.
- Do not change dependencies without approval.
- Multi-tenant is single DB with tenant isolation by tenant_id (see _PROJECT_GUIDE.md).
- Do not trust tenant_id from request; enforce via middleware and model scope.

## Frontend Bundling
- If a frontend change is not reflected, the user may need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise and focus on what matters.

## Git Commits
- Always prefix commit messages with a tag like `[FEAT]`, `[FIX]`, `[CHORE]`, etc.
- TODO commit deve ter tag no inicio da mensagem.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server with app-specific tools. Use them.

## Artisan
- Use the `list-artisan-commands` tool to verify available parameters when you need to call Artisan.

## URLs
- When sharing project URLs, use `get-absolute-url` to ensure correct scheme/host/port.

## Tinker / Debugging
- Use `tinker` to execute PHP for debugging or Eloquent queries.
- Use `database-query` for read-only database queries.

## Reading Browser Logs
- Use `browser-logs` to read recent browser errors/logs.

## Searching Documentation (Critically Important)
- Use `search-docs` before any other approach for Laravel or Laravel ecosystem packages.
- Pass multiple broad queries.
- Do not add package names to queries.

=== php rules ===

## PHP
- Always use curly braces for control structures.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
- Do not allow empty `__construct()` with zero parameters unless private.

### Type Declarations
- Always add explicit return types.
- Use appropriate parameter type hints.

### Comments
- Prefer PHPDoc blocks over inline comments. Avoid inline comments unless complex.

### PHPDoc Blocks
- Add array shape types when appropriate.

### Enums
- Use TitleCase keys.

=== tests rules ===

## Test Enforcement
- Every change must be programmatically tested.
- Run minimal tests with `php artisan test --compact` targeting the affected tests.

=== laravel/core rules ===

## Do Things the Laravel Way
- Use `php artisan make:` for new files.
- Pass `--no-interaction` to Artisan commands.

### Database
- Prefer Eloquent relationships over raw queries.
- Avoid `DB::`; use `Model::query()`.
- Prevent N+1 with eager loading.

### Model Creation
- Create factories/seeders for new models; ask if needed.

### APIs & Eloquent Resources
- Default to API Resources and versioning unless app conventions differ.

### Controllers & Validation
- Use Form Requests with rules + messages.

### Queues
- Use queued jobs for time-consuming tasks.

### Authentication & Authorization
- Use Laravel auth/authorization (policies, gates, etc.).

### Audit Logging
- New backend functionality must include audit logging with before/after when admin acts.

### URL Generation
- Prefer named routes and `route()`.

### Configuration
- Use `config()`; never call `env()` outside config files.

### Testing
- Use factories.
- PHPUnit classes only; convert any Pest usage.

### Vite Error
- If Vite manifest errors occur, run `npm run build` or `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12
- Use `search-docs` for version-specific guidance.
- Middleware registered in `bootstrap/app.php`.
- `bootstrap/providers.php` for service providers.
- No `app\Console\Kernel.php` in Laravel 12; use `routes/console.php` or `bootstrap/app.php`.
- Console commands in `app/Console/Commands` are auto-registered.

### Database
- When modifying columns, include all previous attributes.
- Laravel 12 supports limiting eager loads natively.

### Models
- Prefer `casts()` method if existing convention uses it.

=== tailwindcss/core rules ===

## Tailwind CSS
- Use Tailwind classes and follow project conventions.
- Use `search-docs` for exact docs when needed.
- Use `gap-*` utilities instead of margins for list spacing.
- Support dark mode where existing pages do.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4
- Use Tailwind v4 utilities only.
- Tailwind config is CSS-first via `@theme`.
- Import Tailwind via `@import "tailwindcss";`
- Use replacement utilities for deprecated classes.
</laravel-boost-guidelines>
