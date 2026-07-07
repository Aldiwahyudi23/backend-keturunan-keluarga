# Keturunan — Family Tree Management System

## Stack
- **Laravel 12** (PHP ^8.2), **Filament 3.3** admin panel at `/admin`
- DB: MySQL (prod), SQLite `:memory:` (tests)
- Auth: **Laravel Sanctum** (API bearer tokens) + Filament session auth
- RBAC: **Spatie laravel-permission** + **Filament Shield** (`php artisan shield:generate --all`)
- PDF: `barryvdh/laravel-dompdf`, **Spatie medialibrary**, **Spatie eloquent-sortable**
- Frontend: **Tailwind CSS v4**, **Vite**, vanilla JS
- UI locale: `id` (`APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`)

## Commands

| Purpose | Command |
|---|---|
| Full setup | `composer setup` |
| Dev servers (all at once) | `composer dev` |
| Run tests | `composer test` (runs `config:clear` first) |
| Single test | `php artisan test tests/Path/To/Test.php` |
| Generate Filament Shield policies | `php artisan shield:generate --all` |
| Migrate | `php artisan migrate` |
| Build assets | `npm run build` |
| Dev assets | `npm run dev` |
| Lint | `./vendor/bin/pint` |

`composer dev` runs concurrently: `php artisan serve`, `queue:listen`, `pail` (logs), `npm run dev`.

## Architecture

```
app/
├── Filament/Resources/
│   ├── PersonResource.php          — CRUD person + histories + card view
│   ├── BookResource.php            — CRUD buku + cover settings
│   ├── BookTemplateResource.php
│   ├── ManagementUser/             — UserResource (type=user), RoleResource
│   └── API/                        — ApiAccountResource (type=service)
├── Http/Controllers/Api/
│   ├── Auth/AuthController.php     — login, me, logout, logout-all
│   ├── FamilyTreeController.php    — CRUD people, marriages, children, tree
│   ├── BookPdfController.php       — preview/download buku PDF
│   └── FamilyRelationshipController.php
├── Middleware/OptionalAuth.php     — optional Sanctum auth for public tree
├── Models/                         — Person, Marriage, ParentChildRelation, Book, BookSection, BookTemplate, PersonHistory
└── Services/                       — FamilyTreeService, FamilyTreeStoreService, FamilyTreeUpdateService, etc.
```

### Routes
- **Admin**: `/admin` (Filament)
- **API auth**: `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`, `POST /api/auth/logout-all`
- **API public**: `GET /api/people/{identifier}/tree` (`identifier` = UUID), `GET /api/people/search`, `POST /api/people/check-relationship`
- **API protected** (Sanctum): `POST /api/people`, `PUT /api/people/{id}`, `DELETE /api/people/{id}`, marriage & child management
- **Web protected** (session): `/person-card/{person}/download`, `/books/{book}/preview`, `/books/{book}/download`

### Key Models
- **Person** — `uuid` as route key, auto `person_code` = `PRS%06d`, soft-deletes, `gender` = male/female, `getEloquentQuery()` removes `SoftDeletingScope` in Filament resources
- **Marriage** — `husband_id`, `wife_id`, `marriage_date`, `divorce_date` (nullable = still married)
- **ParentChildRelation** — `parent_id`, `child_id`, `type` = biological/adopted/step, `sort` for ordering
- **PersonHistory** — belongs to Person, `sort` column for ordering
- **Book** — `root_person_id`, `template_id`, `status` = draft/published, covers, `default_max_generation`
- **BookTemplate** — `key` maps via accessor to Blade view: `pdf.book.{classic,modern,minimal,premium}`

### API Conventions
- Public tree endpoint uses `OptionalAuth` middleware — reads Bearer token if present but does not fail without it
- Authenticated users get `can_add_*` action flags in tree response
- Family tree max level: 2–5 (configurable via `?generations=` query param)
- Spouses: males may have multiple (polygamy), females only one active

### Filament Form Quirks
- `birth_date` is stored as full date but form uses **separate month/year select inputs** + hidden field
- `person_code` is disabled in form (auto-generated via `PRS%06d` on `created` event)
- Photo uploads go to `people-photos/` directory

## Testing
- Tests = PHPUnit 11, no Pest
- `phpunit.xml` forces in-memory SQLite + array cache/queue/session
- Existing: `tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`
- Seeder: `AdminUserSeeder` creates `super_admin` user (`aldiwahyudi1223@gmail.com`)

## Seeding
```bash
php artisan db:seed
```
Runs `AdminUserSeeder` which creates `super_admin` role + admin user. Always resets Spatie permission cache first.

## Gotchas
- `.env.example` has **real production credentials** — never commit `.env`
- **Run `config:clear` before tests** (`composer test` does this automatically)
- `php artisan shield:generate --all` is needed after creating new Filament resources/widgets/pages
- Resource `getEloquentQuery()` overrides disable `SoftDeletingScope` globally (PersonResource, BookResource)
- `User` model uses `type` field: `user` (normal) vs `service` (API integration accounts)
- File uploads need `php artisan storage:link`
- API person routes use numeric `id` for spouse-options/children endpoints, UUID `identifier` for tree
- `AuthController` is under `Http/Controllers/Api/Auth/` subdirectory
