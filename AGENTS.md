# Keturunan — Family Tree Management System

## Stack
- **Laravel 12** (PHP ^8.2), **Filament 3.3** admin at `/admin`
- DB: MySQL (prod), SQLite `:memory:` (tests)
- Auth: **Laravel Sanctum** (API bearer tokens) + Filament session auth
- RBAC: **Spatie laravel-permission** + **Filament Shield** (`php artisan shield:generate --all`)
- PDF: `barryvdh/laravel-dompdf`, **Spatie medialibrary**, **Spatie eloquent-sortable**
- Frontend: **Tailwind CSS v4**, **Vite**, vanilla JS
- Locale: `id` (`APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`)

## Commands

| Purpose | Command |
|---|---|
| Full setup | `composer setup` (runs `migrate --force`, does NOT `storage:link`) |
| Dev servers | `composer dev` (serve + queue:listen + pail + vite concurrently) |
| Run tests | `composer test` (runs `config:clear` first) |
| Single test | `php artisan test tests/Path/To/Test.php` |
| Shield policies | `php artisan shield:generate --all` |
| Migrate | `php artisan migrate` |
| Build assets | `npm run build` |
| Dev assets | `npm run dev` |
| Lint | `./vendor/bin/pint` |

## Architecture

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── PersonResource.php          — CRUD person + histories + card view
│   │   ├── BookResource.php            — CRUD buku + cover settings
│   │   ├── BookTemplateResource.php
│   │   ├── ManagementUser/             — UserResource (type=user), RoleResource
│   │   └── API/                        — ApiAccountResource (type=service)
│   ├── Pages/GenerateServiceToken.php
│   └── Widgets/                        — StatsOverview, GenderChartWidget
├── Http/Controllers/
│   ├── PersonCardController.php        — download person card PDF
│   └── Api/
│       ├── Auth/AuthController.php     — login, me, logout, logout-all
│       ├── FamilyTreeController.php    — CRUD people, marriages, children, tree
│       ├── FamilyRelationshipController.php
│       ├── BookPdfController.php       — preview/download buku PDF
│       └── BookDataController.php      — unused test endpoint
├── Http/Middleware/
│   ├── OptionalAuth.php                — optional Sanctum auth (public tree)
│   └── CheckTokenAbility.php           — Sanctum token scope check
├── Models/                             — Person, Marriage, ParentChildRelation, Book, BookSection, BookTemplate, PersonHistory
└── Services/                           — FamilyTreeService, FamilyTreeStoreService, FamilyTreeUpdateService, etc.
```

### Routes
- **Admin**: `/admin` (Filament)
- **API auth**: `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`, `POST /api/auth/logout-all`
- **API public**: `GET /api/people/{identifier}/tree` (UUID, `optional.auth` middleware), `GET /api/people/search`, `POST /api/people/check-relationship`
- **API protected** (Sanctum): `POST /api/people`, `PUT /api/people/{id}`, `DELETE /api/people/{id}`, marriage & child management
- **Web protected** (session): `/person-card/{person}/download`, `/books/{book}/preview`, `/books/{book}/download`

### Key Models
- **Person** — `uuid` as route key, auto `person_code` = `PRS%06d`, soft-deletes, `gender` = male/female, `getEloquentQuery()` removes `SoftDeletingScope` in Filament
- **Marriage** — `husband_id`, `wife_id`, `marriage_date`, `divorce_date` (nullable = still married)
- **ParentChildRelation** — `parent_id`, `child_id`, `type` = biological/adopted/step, `sort` for ordering
- **PersonHistory** — belongs to Person, `sort` for ordering
- **Book** — `root_person_id`, `template_id`, `status` = draft/published, covers, `default_max_generation`
- **BookTemplate** — `key` maps via accessor to Blade view: `pdf.book.{classic,modern,minimal,premium}`
- **User** — `type` = user/service; `is_active` boolean; uses `HasRoles` + `HasApiTokens`

### API Conventions
- Public tree uses `OptionalAuth` — reads Bearer if present, does not fail without it
- Authenticated users get `can_add_*` action flags in tree response
- Tree max level: 2–5 (configurable via `?generations=`)
- Spouses: males may have multiple (polygamy), females only one active marriage

### Filament Form Quirks
- `birth_date` stored as full date but form uses **separate month/year select inputs** + hidden field
- `person_code` disabled in form (auto-generated via `PRS%06d` on `created` event)
- Photo uploads go to `people-photos/` directory (medialibrary)

## Testing
- PHPUnit 11 (no Pest), SQLite `:memory:` + array cache/queue/session
- Only `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` exist
- Seed `super_admin` user via `php artisan db:seed` (email: `aldiwahyudi1223@gmail.com`)

## Gotchas
- `.env.example` has **real production DB credentials** — never commit `.env`
- **`config:clear` before tests** (`composer test` does this automatically)
- Run `php artisan shield:generate --all` after creating new Filament resources/widgets/pages
- Resource `getEloquentQuery()` overrides remove `SoftDeletingScope` (PersonResource, BookResource)
- User model `type` field: `user` (filament normal) vs `service` (API integration accounts)
- `php artisan storage:link` needed for file uploads (not included in `composer setup`)
- API person routes use numeric `id` for spouse-options/children, UUID `identifier` for tree
