# Keturunan — Family Tree Management System

## Stack
- **Laravel 12** (PHP ^8.2), **Filament 3.3** admin at `/admin`
- DB: MySQL (prod), SQLite `:memory:` (tests)
- Auth: **Laravel Sanctum** (API bearer tokens) + Filament session auth
- RBAC: **Spatie laravel-permission** + **Filament Shield** (`php artisan shield:generate --all`). Shield is `require-dev` only — not in production.
- PDF: `barryvdh/laravel-dompdf`, **Spatie medialibrary**, **Spatie eloquent-sortable** (commented out on ParentChildRelation)
- Frontend: **Tailwind CSS v4**, **Vite**, vanilla JS
- Locale: `id` (`.env.example` has `APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`)

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

### App directories
- `app/Filament/Resources/` — PersonResource, BookResource, BookTemplateResource, CardTemplateResource, CardResource, ManagementUser/(UserResource, RoleResource), API/ApiAccountResource
- `app/Http/Controllers/Api/` — AuthController (login/me/logout), FamilyTreeController (tree CRUD), BookPdfController, FamilyRelationshipController
- `app/Http/Controllers/` — PersonCardController, CardController (card PDF preview/download)
- `app/Http/Middleware/` — OptionalAuth (public tree, reads Bearer if present), CheckTokenAbility (Sanctum scope check)
- `app/Models/` — Person, Marriage, ParentChildRelation, PersonHistory, PersonActivity, Book, BookSection, BookTemplate
- `app/Models/Card/` — CardTemplate, Card (uuid, FK root_person), CardContact, CardPeople (pivot with JSON `meta`)
- `app/Services/` — FamilyTreeService (tree builder), FamilyTreeStoreService, FamilyTreeUpdateService, FamilyTreeSearchService, FamilyTreeDeleteService, PersonCardService, PersonActivityService
- `app/Services/Card/` — CardPersonService (generate card PDF from card_people data), CardEmergencyService (emergency contacts from card for tree API)
- `app/Services/Report/` — GenealogyService, HistoryService

### Routes
- **Admin**: `/admin` (Filament)
- **API auth**: `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`, `POST /api/auth/logout-all`
- **API public**: `GET /api/people/{identifier}/tree` (UUID, `optional.auth`, optional `?card=uuid` for emergency contacts), `GET /api/people/search`, `POST /api/people/check-relationship`
- **API protected** (Sanctum): person CRUD at `/api/people/{id}`, marriage and child management under `/api/people/marriages/{id}`, `/api/people/{parentId}/children/{childId}`, plus spouse-option queries, person activities at `/api/people/{person}/activities`
- **Web protected** (session): `/person/{person}/card`, `/person/{person}/card/download`, `/books/{book}/preview`, `/books/{book}/download`, `/card/{card}`, `/card/{card}/download`
- **Deprecated**: `GET /api/buku/{book}/data` (test endpoint, unused)

### Key Models
- **Person** — `uuid` as route key (tree), auto `person_code` = `PRS%06d` (`creating` sets temp random, `created` updates via `updateQuietly()`), soft-deletes, `getEloquentQuery()` removes `SoftDeletingScope` in Filament
- **Marriage** — `husband_id`, `wife_id`, `marriage_date`, `divorce_date` (nullable = still married)
- **ParentChildRelation** — `parent_id`, `child_id`, `type` = biological/adopted/step. Bug: `$casts` uses `sort_order` but column is `sort`. `SortableTrait` is commented out.
- **PersonHistory** — belongs to Person, `sort` for ordering
- **PersonActivity** — belongs to Person, `description`, `can_parent_view`, `created_by` FK User
- **Book** — `root_person_id`, `template_id`, `status` = draft/published/archived, cover settings, `default_max_generation` (0 = all)
- **BookTemplate** — DB has `blade_view` column (stores view path). Bug: `getBladeViewAttribute()` accessor uses non-existent `$this->key`, so all templates resolve to `classic`. `getEloquentQuery()` does NOT override `SoftDeletingScope`.
- **BookSection** — belongs to Book, `type` = text/dynamic (model also checks image/page_break), `key`, `content`, `sort`
- **User** — `type` = user/service; `is_active` boolean; uses `HasRoles` + `HasApiTokens`
- **CardTemplate** — `view_path` maps to blade view for card PDF, `preview` stores sample image
- **Card** — `uuid` as route key, `card_template_id`, `root_person_id` (FK person), status = draft/published, contacts (hasMany with role/phone) + cardPeople (pivot with `photo_path` + `address`)

### API Conventions
- Public tree uses `OptionalAuth` — reads Bearer if present, does not fail without it
- Authenticated users get `can_add_*` action flags in tree response
- Tree max level: 2–5 (configurable via `?generations=`)
- Spouses: males may have multiple (polygamy), females only one active marriage
- Person routes use numeric `id` for mutations, UUID `identifier` for tree

### Filament Form Quirks
- `birth_date` stored as full date but form uses separate month/year select inputs + hidden field
- `person_code` disabled in form (auto-generated)
- Photo uploads go to `people-photos/` directory (medialibrary)

## Testing
- PHPUnit 11 (no Pest), SQLite `:memory:` + array cache/queue/session (`QUEUE_CONNECTION=sync`)
- Only `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` exist
- `composer test` runs `config:clear` first automatically
- Seed `super_admin` user via `php artisan db:seed` (email: `aldiwahyudi1223@gmail.com`)

## Gotchas
- `.env.example` has real production DB credentials and APP_URL — never commit `.env`
- `config:clear` before tests (`composer test` does this automatically)
- Shield discovery config has `discover_all_resources`, `discover_all_widgets`, `discover_all_pages` all `false` — must run `shield:generate --all` after creating new resources/widgets/pages
- `php artisan storage:link` needed for file uploads (not included in `composer setup`)
- `BookTemplate::getBladeViewAttribute()` uses `$this->key` but no `key` column exists — every template resolves to `classic` regardless of DB `blade_view` value
- `ParentChildRelation` casts `sort_order` but DB column is `sort` — useless cast, also `SortableTrait` is commented out
- Resource `getEloquentQuery()` overrides remove `SoftDeletingScope` (PersonResource, BookResource) but NOT BookTemplateResource
- `BookSection` model checks `isImage()` and `isPageBreak()` but DB type enum only has `text`/`dynamic`
