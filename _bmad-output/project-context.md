---
project_name: personal-finance
user_name: Martinfou
date: '2026-05-17'
sections_completed:
  - technology_stack
  - language_rules
  - framework_rules
  - testing_rules
  - quality_rules
  - workflow_rules
  - anti_patterns
status: complete
rule_count: 42
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

| Layer | Technology | Notes |
|-------|------------|-------|
| Backend | PHP ^8.3, Laravel ^13.8 | Breeze auth scaffolding |
| API / pages | Inertia Laravel ^2.0 | No separate REST API for app UI |
| Frontend | Vue 3.4+, Inertia Vue3 ^2.0 | SFCs under `resources/js/` |
| Build | Vite ^8, laravel-vite-plugin ^3.1 | `npm run dev` / `npm run build` |
| CSS | Tailwind 3.2 + `@tailwindcss/forms` | Custom tokens in `tailwind.config.js` |
| Charts | Chart.js 4.5 + vue-chartjs 5.3 | Dashboard doughnut only |
| Routing (JS) | Ziggy ^2.0 | Use `route()` in Vue templates |
| DB | SQLite default (Laravel) | Eloquent models, user-scoped data |
| Tests | PHPUnit ^12.5 | `php artisan test` |
| Formatting | Laravel Pint ^1.27 | PHP style only; no ESLint in repo |

**Product context:** `PRODUCT.md` (vision, French UI, Canadian banks). **Design context:** `DESIGN.md` + `resources/css/app.css` (ink/brand tokens, component classes).

**BMad planning artifacts:** `_bmad-output/planning-artifacts/` is empty — no PRD/architecture/epics yet. Prefer extending existing patterns over greenfield scaffolding.

---

## Critical Implementation Rules

### Language-Specific Rules

**PHP / Laravel**

- Scope all financial data by `$request->user()->id` (or `auth()->id()`). Never query `Transaction`, `Category`, `Budget`, etc. without user filter.
- Use `$fillable` + `casts()` on models (see `Transaction`); prefer `decimal:2` for money, `date` for transaction dates.
- Validation lives in controllers (`$request->validate([...])`) or Form Requests (`app/Http/Requests/`). Reuse rules: `type` ∈ `income,expense`, `amount` numeric min 0.01.
- Dashboard is invokable controller: `DashboardController::__invoke`, not resource.
- New bank CSV formats: add entry to `CsvImportService::BANKS` constant and unit test in `tests/Unit/CsvImportServiceTest.php`. Use `auto_detect_columns` when headers vary (see `cibc_visa`).
- Do not commit `.env`, credentials, or user CSV uploads (`storage/app/csv-imports`).

**Vue / JavaScript**

- Pages: `resources/js/Pages/{Feature}/Index.vue` (and nested). Layouts: `GuestLayout` (auth), `AuthenticatedLayout` (app shell).
- Shared UI: `resources/js/Components/` — reuse `btn-primary`, `card`, `input`, `label`, `CategoryBadge`, `MonthNavigator`, `TypeToggle`, `StatCard`, `EmptyState`, `AppIcon`.
- Do not use `class` as a Vue prop name (reserved); use `wrapperClass`, `iconClass`, etc.
- Money display: import `formatMoney` from `@/utils/money.js`. Backend stores positive amounts; type distinguishes income vs expense.
- Avoid emoji in UI; use `AppIcon` + category color dots (`CategoryBadge`).
- Inertia: `Inertia::render('Path/To/Page', [...])` matches Vue path under `Pages/`.

### Framework-Specific Rules

**Inertia + Vue**

- Authenticated routes use middleware `['auth', 'verified']` in `routes/web.php`.
- Page titles: `<Head title="..." />` in French for user-facing pages.
- Header slot on `AuthenticatedLayout`: `<template #header>` for page title row; main content in default slot (layout already applies `max-w-7xl` padding).
- Forms: Inertia `useForm` for POST/PATCH; `router.get` with `preserveState` for month navigation on dashboard/budgets.
- Ziggy: `route('transactions.index')`, `route().current('transactions*')` for active nav.

**Tailwind / design system**

- Use semantic colors: `ink-*`, `brand-*`, `surface-*` — not raw `gray-*` / `indigo-*` on new UI.
- Typography: `font-display` (Fraunces) for headings/stats; `font-sans` (Plus Jakarta Sans) for body.
- Prefer component classes from `app.css` over one-off Tailwind for buttons, cards, inputs.
- Nav: solid `bg-surface-50`, no `backdrop-blur` on new work.

**Domain model**

- `Category`: per-user, has `type` (income/expense), `name`, `icon`, `color`.
- `Transaction`: belongs to user + category; `type` income|expense.
- `Budget`: keyed by `month` string `YYYY-MM`, `limit_amount`, category.
- `SavingGoal` (route name `goals`): progress computed in controller, not only in DB.
- `RecurringTransaction`: `recurring.process` route creates due transactions.

### Testing Rules

- PHPUnit only; run `php artisan test` or `composer test`.
- CSV import logic: unit test `CsvImportService` with `UploadedFile::fake()->createWithContent()`.
- Feature tests exist under `tests/Feature/` (Breeze auth/profile); add feature tests when changing HTTP contracts.
- No frontend test runner configured — manual or browser check for Vue changes.

### Code Quality & Style Rules

- PHP: run `./vendor/bin/pint` on changed PHP files before finishing.
- Keep diffs focused; no drive-by refactors unrelated to the task.
- French copy for all user-visible strings (Martin is primary user, `communication_language` English for agent chat only).
- Controllers return Inertia responses or redirects with flash (`with('success', ...)`); some legacy English flash messages may remain — translate when touching those lines.

### Development Workflow Rules

- Do not create git commits unless the user explicitly asks.
- Do not edit `DESIGN.md` / `PRODUCT.md` unless asked; they inform Impeccable and agents.
- Dev stack: `composer dev` runs server, queue, logs, and Vite concurrently.
- New features without BMad stories: align with existing routes and naming; consider adding story later via `bmad-create-epics-and-stories`.

### Critical Don't-Miss Rules

- **User isolation:** Every new query on user-owned models must filter by `user_id`. Categories used in forms must belong to the same user (`exists:categories,id` is not enough without scoping in controller).
- **CSV import:** Register new banks in `CsvImportService::BANKS` and `CsvImportController` validation (`in:` bank keys). Credit column = income (payments/refunds); debit column = expense (purchases).
- **Month navigation:** Dashboard and budgets use `year` + `month` query params; preserve pattern when adding date-filtered views.
- **Category icons in DB:** May still be emoji strings; UI should show `CategoryBadge` (color dot), not raw emoji, on new/changed views.
- **PrimaryButton:** No default `w-full` — add `w-full` on auth forms only where needed.
- **Don't reintroduce:** Laravel default double header (`bg-white shadow`), emoji nav, indigo Breeze buttons, identical six-card marketing grids, `border-l-4` mobile nav stripes.
- **Amount signs:** Store amounts as positive; never negative amounts in DB. Comparison/delta logic belongs in dashboard props or `StatCard` / `formatDelta`.

---

## Usage Guidelines

**For AI Agents**

- Read this file before implementing any code.
- Follow ALL rules exactly as documented.
- When in doubt, prefer the more restrictive option (user scoping, design tokens, French UI).
- Update this file when new patterns become standard (e.g. after formal architecture doc).

**For Humans**

- Keep this file lean; remove rules that become obvious.
- Update when stack versions or conventions change.
- Canonical copy also referenced from `_bmad-output/`; optional mirror under `docs/` if you set `project_knowledge` there.

**Last Updated:** 2026-05-17
