# PitStop Manager — Fixes applied

Date: 2026-05-19. The project compiled and seeded before this audit, but had ~12 blocker-grade defects (runtime 500s, broken Stripe flow, enum drift between DB and code, missing authorization wiring) plus ~22 major defects. Below is a full record of every change made.

The audit cross-referenced patterns from the [Stride365](https://github.com/sergiics9/Stride365) repo (cloned into `./reference/`, **read-only**) because it uses the same Laravel 12 + Angular + Stripe stack. No file was copied from Stride365 — its layout differs and its domain (sports/fitness) is unrelated to karting championships.

---

## Backend (`backend/`)

### BLOCKERs — code that would 500 at runtime

1. **`app/Http/Controllers/Controller.php`** — was an empty abstract class. Added `AuthorizesRequests` and `ValidatesRequests` traits. Every `$this->authorize(...)` call in `ChampionshipController`, `CircuitController`, `ResultController`, and `InscriptionController` would otherwise have thrown `Call to undefined method`.

2. **`app/Http/Controllers/ChampionshipController.php`** — `updateStatus` validated `in:draft,published,in_progress,completed,cancelled` but the DB enum is `finished`, not `completed`. Sending `completed` produced **HTTP 500** (`SQLSTATE[01000] data truncated for column 'status'`). Replaced with `finished`.

3. **`app/Http/Controllers/InscriptionController.php`** — `updateStatus` validated `in:pending,accepted,rejected` but the DB enum is `pending,confirmed,rejected,withdrawn`. Sending `accepted` produced **HTTP 500**. Replaced. Also fixed `index` to expose confirmed inscriptions (plus own) to non-owners only, and switched `store` to use `StoreInscriptionRequest` so `car_number` is actually validated and persisted.

4. **`app/Http/Controllers/ResultController.php`** — `store` enforced inscription status `accepted` (never matches; nobody could ever record a result). Changed to `confirmed`. Added `$this->authorize('create', [Result::class, $race])`.

5. **`app/Policies/ResultPolicy.php`** — `create($user, Result $result)` had a `Result` second argument, but a result doesn't exist at creation time. Changed to `create($user, Race $race)` to be authorize-able from the store controller.

6. **`app/Http/Controllers/SubscriptionController.php`** — fully rewritten:
   - Was using `$plan->duration_months` (column doesn't exist; only `duration_days` does) — fixed.
   - Was mass-assigning `stripe_session_id` to both `Subscription` and `Payment` — neither model has that column or fillable — fixed.
   - Was inlining Stripe SDK calls duplicating `StripeService` — now delegates to `StripeService::createCheckoutForSubscription()`.
   - `downloadPdf` was a stub returning JSON `{ "message": "PDF generation pending" }` — now uses `PdfService::generatePaymentReceipt()` and returns a real downloadable PDF.

7. **`app/Services/StripeService.php`** — rewritten:
   - Now creates Subscription + Payment + Checkout Session **inside a DB transaction**.
   - Pushes `subscription_id` and `payment_id` into Stripe `metadata` so the webhook can target them deterministically (was previously fragile lookup by `user_id + plan_id + status=pending`).
   - Adds `customer_email` to the Checkout Session.
   - Webhook handler now recalculates `starts_at`/`ends_at` from `plan->duration_days` on success (was previously written prematurely at session creation).
   - Idempotent: if subscription is already `active` and payment is already `succeeded`, the webhook acknowledges without re-processing.
   - Also handles `checkout.session.expired` by marking the pending payment as `failed` and pending subscription as `cancelled`.

8. **`app/Http/Controllers/Webhook/StripeWebhookController.php`** — previously **only logged** events. Now delegates to `StripeService::handleWebhookEvent()` with proper exception handling for invalid payloads (400), signature mismatches (400), and any other error (500). Stripe webhook URL is still `POST /api/webhooks/stripe`, still unauthenticated.

### MAJORs

9. **`app/Http/Middleware/RoleMiddleware.php`** — returned **403** for both "no user" and "wrong role". Now returns **401** for unauthenticated and **403** only for the wrong role.

10. **`app/Http/Controllers/WeatherController.php`** — previously cached and returned raw OpenWeatherMap error JSON with HTTP 200 if the API key was missing. Now delegates to `WeatherService` and returns **503** with `"Weather service is not configured."` when the key is empty, **502** if the upstream provider is unavailable.

11. **`app/Http/Controllers/CircuitController.php`** — `store` was missing `$this->authorize('create', Circuit::class)`. Added.

12. **`app/Http/Controllers/ChampionshipController.php`** — added plan quota enforcement on `store`: an organizer cannot create more than `plan.max_championships` active (non-cancelled, non-finished) championships. Returns 403 with a clear message ("An active subscription is required to create championships." or "Your active plan allows N championship(s). Upgrade your plan to create more.").

13. **`app/Http/Requests/Auth/RegisterRequest.php`** — `prepareForValidation()` now **forces `role: pilot`** for all public signups regardless of payload. Organizers must be created by an admin or upgraded via subscription.

14. **`app/Http/Resources/ChampionshipResource.php`** — `show` eager-loaded `races.circuit` but the resource never exposed them. Added `races` field using `whenLoaded`.

### Files touched (backend)

- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/ChampionshipController.php`
- `app/Http/Controllers/CircuitController.php`
- `app/Http/Controllers/InscriptionController.php`
- `app/Http/Controllers/ResultController.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/WeatherController.php`
- `app/Http/Controllers/Webhook/StripeWebhookController.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Resources/ChampionshipResource.php`
- `app/Policies/ResultPolicy.php`
- `app/Services/StripeService.php`

---

## Frontend (`frontend/`)

### BLOCKERs

1. **`src/app/app.routes.ts`** — Stripe success/cancel return URLs are `/organizer/subscription/success?session_id=…` and `/organizer/subscription/cancel`, but no such child routes existed. The redirect from Stripe would have hit the `**` wildcard and bounced to home. Added both child routes pointing to the same `SubscriptionPageComponent`.

2. **`src/app/features/organizer/subscription/subscription-page/subscription-page.component.ts`** — was reading `?success=true` / `?cancelled=true` query params; the new (and only) param sent by the corrected backend is `session_id`. Rewrote `handleQueryParams()` to detect success/cancel from the current URL path AND/OR the `session_id` query.

3. **`src/app/features/organizer/championships/championship-races/championship-races.component.ts`** — read route param `'id'`, but the route registered the param as `:championshipId`. The component therefore got `NaN` and the page silently failed to load races. Fixed to `paramMap.get('championshipId')`.

### MAJORs

4. **`src/app/core/services/subscription.service.ts`** — multiple response-shape mismatches with Laravel:
   - `getPlans()` was typed `Observable<SubscriptionPlan[]>` but Laravel returns `{ data: [...] }`. Now unwraps `.data`.
   - `getMySubscription()` was typed as raw `Subscription`; backend returns `{ data: Subscription | null }`. Now returns `Observable<Subscription | null>`.
   - `getMyPayments()` was typed `Observable<Payment[]>` but backend returns a paginated `PaginatedResponse<Payment>`. Now unwraps `.data`.
   - Added `CheckoutSessionResponse` type so `subscribe()` returns `{ checkout_url, session_id }` properly.

5. **Five list templates were linking to `/create` paths that don't exist** (all routes are `/new`). Fixed:
   - `features/admin/subscription-plans/plan-list/plan-list.component.html`
   - `features/admin/categories/category-list/category-list.component.html`
   - `features/organizer/circuits/circuit-list/circuit-list.component.html`
   - `features/organizer/championships/championship-list/championship-list.component.html`
   - `features/organizer/results/result-list/result-list.component.html`
   - `features/organizer/championships/championship-races/championship-races.component.html`

6. **`src/app/features/auth/register/register.component.{ts,html}`** — backend now forces `role: pilot` server-side. Removed the misleading "Organizer / Pilot" role selector from the UI, the corresponding form control, and the required validator. Added an explanatory note: "Las cuentas se crean como Piloto. Para acceso de Organizador, contrata una suscripción desde tu perfil tras registrarte."

7. **`src/app/layouts/admin-layout/admin-layout.component.{ts,html}`** — the admin layout had no logout control (it uses a separate layout component from the main one). Wired up `AuthService.logout()` the same way `MainLayoutComponent` does it, added a "Cerrar sesión" button in the sidebar nav, plus the current user's name under the panel title. Also added `[routerLinkActiveOptions]="{ exact: true }"` to the `/admin` Dashboard link so it doesn't stay highlighted on every sub-route.

8. **`src/app/core/interceptors/error.interceptor.ts`** — only logged to console and redirected on 401. Now extracts a single ready-to-display message:
   - 422 → flattens Laravel's `{ errors: { field: [strings] } }` into one string.
   - Falls back to `body.message` or `statusText`.
   - Attaches the result as `error.displayMessage` so any component can use it without re-parsing.

9. **`src/app/features/public/championship-detail/championship-detail.component.html`** — had a dead `routerLink="['/races', race.id, 'results']"` for finished races (no such public route exists). Replaced with a button that switches the active tab to `'standings'`, which already shows championship standings.

### MINOR polish

10. **`src/app/features/organizer/inscriptions/inscription-list/inscription-list.component.{ts,html}`** — was rendering raw API status string `'confirmed'`. Added `getStatusLabel()` returning Spanish labels (Pendiente / Confirmada / Rechazada / Retirada), like the pilot view already had.

### Files touched (frontend)

- `src/app/app.routes.ts`
- `src/app/core/services/subscription.service.ts`
- `src/app/core/interceptors/error.interceptor.ts`
- `src/app/features/auth/register/register.component.ts`
- `src/app/features/auth/register/register.component.html`
- `src/app/features/organizer/subscription/subscription-page/subscription-page.component.ts`
- `src/app/features/organizer/championships/championship-races/championship-races.component.ts`
- `src/app/features/organizer/championships/championship-races/championship-races.component.html`
- `src/app/features/organizer/championships/championship-list/championship-list.component.html`
- `src/app/features/organizer/circuits/circuit-list/circuit-list.component.html`
- `src/app/features/organizer/results/result-list/result-list.component.html`
- `src/app/features/organizer/inscriptions/inscription-list/inscription-list.component.ts`
- `src/app/features/organizer/inscriptions/inscription-list/inscription-list.component.html`
- `src/app/features/admin/subscription-plans/plan-list/plan-list.component.html`
- `src/app/features/admin/categories/category-list/category-list.component.html`
- `src/app/features/public/championship-detail/championship-detail.component.html`
- `src/app/layouts/admin-layout/admin-layout.component.ts`
- `src/app/layouts/admin-layout/admin-layout.component.html`

---

## Verification performed

- `php artisan migrate:status` — 14/14 migrations OK.
- `php artisan route:list --json` — 64 endpoints register cleanly.
- `php -l` syntax check on every modified PHP file — no syntax errors.
- `npx ng build --configuration=development` — succeeds with zero errors after every change.
- Smoke tests against the running API:
  - Login admin / organizer / pilot — all 200.
  - `PATCH /api/championships/1/status` with `finished` → 200; with `completed` → 422 (was 500).
  - `PATCH /api/inscriptions/1/status` with `confirmed` → 200; with `accepted` → 422 (was 500).
  - `POST /api/championships` unauthenticated → 401 (was 403).
  - `POST /api/championships` as pilot → 403 (correct).
  - `POST /api/championships` as organizer without subscription → 403 with clear message.
  - `POST /api/register` with `{role: organizer}` → user is created with `role: pilot` (backend ignores client-supplied role).
  - `GET /api/weather` without `OPENWEATHERMAP_API_KEY` → 503 (was 200 with junk).
  - `POST /api/webhooks/stripe` without `STRIPE_WEBHOOK_SECRET` → 200 with `{"status":"error","message":"Webhook secret not configured"}` (was 500 inside controller).

---

## What was NOT touched (and why)

- **Eloquent models, migrations, DB schema** — were correct. The drift was in the controllers, not the schema. Touching the schema would have invalidated the seeded data.
- **Seeder idempotency** — flagged as MAJOR by the audit (re-running the seeders hits unique-constraint errors). Not changed because the documented workflow is `php artisan migrate:fresh --seed`, not repeated `db:seed`. Easy to add `firstOrCreate` patterns later if you actually want it idempotent.
- **`config/sanctum.php` / `config/cors.php`** — current configuration works for both stateful and bearer-token flows. Only flagged as nits.
- **Reference repo `./reference/Stride365`** — cloned shallow (read-only) so you can browse it; nothing copied from it.

---

# Second pass (same day) — remaining MAJOR/MINOR items

After the first pass landed, a second sweep cleaned up everything still flagged by the two audits.

## Backend

1. **All 9 seeders rewritten to be idempotent.** Every seed now uses `updateOrCreate` and resolves references by email/slug/name instead of hard-coded ids. `php artisan db:seed` can be run any number of times without errors. **Verified** by running `db:seed --force` twice in a row and observing zero errors.
   - `UserSeeder.php`, `CategorySeeder.php`, `SubscriptionPlanSeeder.php`, `CircuitSeeder.php`, `ChampionshipSeeder.php`, `RaceSeeder.php`, `InscriptionSeeder.php`, `ResultSeeder.php`, `SubscriptionSeeder.php`

2. **Pilot self-withdrawal semantics.** `InscriptionController@destroy` now distinguishes:
   - **pilot owner** → flips the row to `status='withdrawn'` and returns the updated `InscriptionResource` (HTTP 200) so the audit trail is preserved.
   - **admin** → hard delete (HTTP 204).

3. **InscriptionController@updateStatus** now does `loadMissing('championship')` before authorize — the policy reads `$inscription->championship->user_id` and would otherwise re-query per call (N+1).

4. **`Admin\PaymentController@index`** now eager-loads `subscription.plan` (was `subscription` only, so the nested plan field in the resource produced an N+1) and orders by `latest()`.

5. **Stripe Checkout modernization.** Replaced the legacy `payment_method_types: ['card']` parameter with the recommended `automatic_payment_methods: ['enabled' => true]`. Lets Stripe pick the right payment method (Card / Apple Pay / Google Pay / SEPA / iDEAL …) based on the buyer's region and account configuration. See [Stripe automatic payment methods documentation](https://docs.stripe.com/payments/payment-methods/integration-options#choose-which-payment-methods-to-accept).

6. **`SubscriptionController@mySubscription` response shape normalized.** Previously returned `{data: {...}}` when subscribed and `{data: null}` when not — but via `(new Resource(...))->response()` it actually came out as a **raw object** when subscribed (because `JsonResource::withoutWrapping()` is enabled in `AppServiceProvider`). Inconsistent shape was breaking the frontend. Now ALWAYS returns `{data: <obj|null>}` so the frontend can safely `res.data ?? null` without branching.

7. **`ChampionshipController@store`** quota error message split into two clear strings: "An active subscription is required to create championships." vs. "Your active plan allows N championship(s). Upgrade your plan to create more." — previously both cases shared a misleading "you reached the limit" wording.

## Frontend

1. **`role.guard.ts`** — wrong-role redirect now sends the user to **their own role's home** (`/admin`, `/organizer/championships`, `/pilot/championships`) instead of `/`, and `/login` if not authenticated. Avoids the confusing landing-page bounce.

2. **`auth.interceptor.ts`** — removed `withCredentials: true`. The app uses bearer tokens; cookies aren't needed and the flag was making every request CORS-credentialed for no reason.

3. **Response-shape consistency in `subscription.service.ts` and `inscription.service.ts`.** Because the backend has `JsonResource::withoutWrapping()` enabled, non-paginated collections and single resources return RAW shapes (not `{data: ...}`). Only paginated collections still wrap. The services now correctly read each endpoint:
   - `getPlans()` → raw array
   - `getMySubscription()` → unwraps `{data: <obj|null>}` (now consistent thanks to backend fix above)
   - `getMyPayments()` → unwraps paginated `{data, meta, links}`
   - `getByChampionship()`, `getMyInscriptions()`, `updateStatus()` → raw shapes
   - new `withdraw(id)` method on the inscription service for the pilot's self-withdrawal flow.

4. **`pilot-inscription-list.component.ts`** — `withdraw()` now updates the row in place (status → `withdrawn`) instead of filtering it out. The pilot still sees their record with the new status.

5. **`error.interceptor.ts` `displayMessage` wired in 15 components.** Every `error: (err) => this.error = err.error?.message || '...'` was changed to `err.displayMessage || err.error?.message || '...'`. Result: 422 validation errors (Laravel field errors) are now surfaced to the user as a single human-readable string instead of a generic fallback.

## Final verification

- `php -l` on every changed PHP file — no syntax errors.
- `php artisan db:seed --force` twice in a row — no errors (idempotent).
- `npx ng build --configuration=development` — clean, zero TS errors.
- `ReadLints` on every changed file — no linter errors.
- Smoke tests against running API:
  - Pilot self-withdrawal preserves row with `status='withdrawn'`.
  - `/api/my/subscription` always returns `{data: <obj|null>}`.
  - `/api/admin/payments` includes `subscription.plan` without extra queries.
  - `/api/weather` 503 with no key.
  - `/api/webhooks/stripe` 200 with informative body when secret missing.

## Files touched in this second pass

### Backend
- `database/seeders/UserSeeder.php`
- `database/seeders/CategorySeeder.php`
- `database/seeders/SubscriptionPlanSeeder.php`
- `database/seeders/CircuitSeeder.php`
- `database/seeders/ChampionshipSeeder.php`
- `database/seeders/RaceSeeder.php`
- `database/seeders/InscriptionSeeder.php`
- `database/seeders/ResultSeeder.php`
- `database/seeders/SubscriptionSeeder.php`
- `app/Http/Controllers/InscriptionController.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/Admin/PaymentController.php`
- `app/Http/Controllers/ChampionshipController.php`
- `app/Services/StripeService.php`

### Frontend
- `src/app/core/services/subscription.service.ts`
- `src/app/core/services/inscription.service.ts`
- `src/app/core/guards/role.guard.ts`
- `src/app/core/interceptors/auth.interceptor.ts`
- `src/app/features/pilot/inscriptions/pilot-inscription-list/pilot-inscription-list.component.ts`
- `src/app/features/organizer/inscriptions/inscription-list/inscription-list.component.ts`
- `src/app/features/organizer/championships/championship-races/championship-races.component.ts`
- `src/app/features/organizer/championships/championship-form/championship-form.component.ts`
- `src/app/features/organizer/championships/championship-list/championship-list.component.ts`
- `src/app/features/organizer/results/result-form/result-form.component.ts`
- `src/app/features/organizer/results/result-list/result-list.component.ts`
- `src/app/features/organizer/races/race-form/race-form.component.ts`
- `src/app/features/organizer/circuits/circuit-form/circuit-form.component.ts`
- `src/app/features/organizer/circuits/circuit-list/circuit-list.component.ts`
- `src/app/features/admin/subscription-plans/plan-form/plan-form.component.ts`
- `src/app/features/admin/categories/category-form/category-form.component.ts`
- `src/app/features/admin/users/user-edit/user-edit.component.ts`

### Docs
- `README.md` — updated roles section, added "Cuota por plan" note, Stripe URLs, idempotent seeder note, and a "Convenciones importantes" table aligning backend ↔ frontend.

---

# Third pass — runtime change-detection bug (Zone.js missing)

User reported: "every page loads a loading spinner that stays, even though for a split second the data is loaded".

## Root cause

Angular **21.2.11** is installed but the project was missing Zone.js entirely:

- `zone.js` not in `package.json` dependencies
- No `"polyfills"` array in `angular.json` (so no `polyfills.js` chunk was produced)
- No `provideZoneChangeDetection()` or `provideZonelessChangeDetection()` in `app.config.ts`

Angular 21 no longer auto-loads Zone.js. Without it AND without zoneless config, change detection simply never runs after async events (HTTP responses, timers, microtasks). The components correctly call `this.loading = false` on HTTP success, but Angular doesn't re-render, so the spinner stays.

## Fix (3 files)

1. **`frontend/package.json`** — `npm install zone.js --save`.
2. **`frontend/angular.json`** — added `"polyfills": ["zone.js"]` under `architect.build.options`. Build now produces a `polyfills.js` chunk (~93 kB) which was previously absent.
3. **`frontend/src/app/app.config.ts`** — added `provideZoneChangeDetection({ eventCoalescing: true })`.

After this, change detection works for every component using classic property-based state (no signals required), which is what all 30+ existing components use.

**User must restart `ng serve` once** to pick up the `angular.json` change — the dev server reads polyfills config at startup, not on file change.

---

# Fourth pass — UI polish + deferred features

User asked: "add the features that u said before and make the page look better".
Two tracks delivered in parallel: a sweeping visual refresh and a batch of deferred features that work without external API keys.

## Visual refresh — design system

### `frontend/src/styles.scss` — full rewrite
- Bootstrap variable overrides (primary `#e11d2e`, slate secondary, Inter font, rounded corners 0.625rem default, 1rem cards, 0.625rem inputs).
- CSS variables (`--ps-*`) for surfaces, borders, shadows, radii, transitions, status colors. Components can opt in without dragging Bootstrap mixins around.
- Component primitives styled globally: cards, buttons, forms, tables, badges, status pills, page headers, empty states, skeleton loaders, avatars, scrollbar.
- Animation helpers: `.fade-in-up`, `pop-in` keyframes, `.hover-lift`.
- Print guard: `.no-print`.

### `frontend/src/index.html`
- Title, description, theme-color meta.
- Preconnect + Inter font from Google Fonts.

### `bootstrap-icons` — installed
- Was used in templates via `bi bi-*` classes but never bundled. Installed `bootstrap-icons` and imported in `styles.scss`, so icons now actually render.

### `frontend/angular.json`
- Bumped budgets to accommodate the new design system + icon font (initial 1MB warning, 1.5MB error; component styles 10kB warning, 16kB error).

## Layouts

### `main-layout` (public + pilot + organizer shell)
- New sticky glass navbar with backdrop blur over `#0f172a`.
- Brand mark with gradient, hover icon links with active state.
- User pill button with avatar + dropdown menu (profile, role-specific shortcuts, logout). Closes on outside click.
- For pilots, dropdown has a prominent **Conviértete en organizador** link.
- Footer with brand + copyright year.

### `admin-layout`
- Sidebar redesigned: gradient dark background, brand mark, user card with role-tinted avatar, section heading "Gestión", red accent active state, logout button at bottom.
- Mobile: slide-in drawer with backdrop.

## Pages

### Landing (`features/public/landing/`)
- Hero with grid pattern, glow accents, eyebrow chip, gradient-text title, stats row.
- Decorative hero card with podium rows (1/2/3 medals + tabular times) and weather footer chip.
- Features section (4 cards with tinted icons).
- Roles section (3 cards, organizer featured).
- CTA card with overlay and decorative blob.

### Login & Register (`features/auth/login/`, `features/auth/register/`)
- Split-screen layout: dark branded aside (eyebrow chip, headline, role highlights/features, copyright) and white form card.
- Inputs with leading icons (envelope, lock, person, shield-check), reveal-password eye toggle, friendly error/info banners.
- Toast notifications on success: `Bienvenido de vuelta, X.` and `¡Cuenta creada, X! Bienvenido a la parrilla.`

### Pilot Dashboard — new at `/pilot`
- File: `features/pilot/dashboard/pilot-dashboard.component.{ts,html,scss}`.
- Welcome header with first name greeting.
- Prominent **upgrade-to-organizer banner**.
- Four stat cards (inscripciones, confirmadas, pendientes, abiertos) with skeleton loaders.
- Recent inscriptions table + open championships shortlist with empty states.

### Pilot Upgrade — new at `/pilot/upgrade`
- File: `features/pilot/upgrade/pilot-upgrade.component.{ts,html,scss}`.
- Benefits row (3 chips), plan cards with "more popular" badge on middle plan.
- Each card: price + per-month indicator, feature list, subscribe CTA. Stripe redirect on subscribe.

### Subscription Result — new at `/subscription/success` and `/subscription/cancel`
- File: `features/subscription-result/subscription-result.component.{ts,html,scss}`.
- Role-agnostic: replaces the old organizer-only `/organizer/subscription/success` redirect target (still routed for backward compat).
- On success: refreshes user via `/api/user`, when role flips to `organizer` it redirects automatically to `/organizer/subscription`.
- On cancel: friendly card, "Volver a los planes" + "Ir al dashboard".

### Public championship list & detail
- List: search card with leading icon, skeleton loaders, polished championship cards with status pills overlaying images, fallback gradient placeholder, empty state card.
- Detail: hero with cover image + dark gradient overlay, badges row, pill tab bar (Carreras / Clasificación) instead of nav-tabs, weather chip, standings table with gold/silver/bronze medals.

## Toast notifications

### `core/services/notification.service.ts` — new
- Signal-based store with `show/success/error/warning/info/dismiss/clear` methods.
- Toasts auto-dismiss (4.5s default, 6.5s for errors).

### `shared/toast-host/toast-host.component.{ts,html,scss}` — new
- Top-right stack with slide-in animation, color-coded variants (success/error/warning/info), icon + optional title + close button. Mobile-aware (full-width on small screens).

### `app.html` / `app.ts`
- Mounts `<app-toast-host />` once globally.

### `core/interceptors/error.interceptor.ts`
- Now fires `notifications.error()` for 0 (network), 403, 5xx errors with friendly copy.
- Skips 401 (already handled by redirect), 404 (renderable in-place), 422 (forms show inline), and auth endpoints (own inline UX).

### Wired success toasts
- `features/auth/login/login.component.ts` and `features/auth/register/register.component.ts`.

## Backend — features

### Auto-promote pilot → organizer
- `routes/api.php`: `POST /subscriptions` now allows `role:pilot,organizer` (was organizer-only).
- `services/StripeService.php`:
  - `success_url` and `cancel_url` now use role-agnostic `/subscription/{success,cancel}` paths.
  - `handleCheckoutCompleted` now eager-loads the user, and after activating the subscription, sets `role = 'organizer'` if the user was a pilot. Logged via `Log::info`.

### Mailables + Blade templates
- `resources/views/emails/layout.blade.php` — shared email layout (gradient header, info-card, pills, footer) using inline-friendly CSS.
- `resources/views/emails/welcome.blade.php` — registration welcome (role pill, CTA to explore championships).
- `resources/views/emails/subscription-activated.blade.php` — sent when checkout completes (plan info, end date, CTA to create championship).
- `resources/views/emails/inscription-status.blade.php` — sent when an organizer confirms/rejects an inscription.
- `app/Mail/WelcomeMail.php`, `app/Mail/SubscriptionActivatedMail.php`, `app/Mail/InscriptionStatusMail.php` — Mailable classes implementing `ShouldQueue`. With `QUEUE_CONNECTION=sync` (Laravel default) they run inline; with `MAIL_MAILER=log` they write to `storage/logs/laravel.log` so devs can verify the templates without SMTP.
- Wired into:
  - `AuthController::register` → `WelcomeMail`
  - `StripeService::handleCheckoutCompleted` → `SubscriptionActivatedMail`
  - `InscriptionController::updateStatus` → `InscriptionStatusMail` only when status transitions to `confirmed` or `rejected`
- Every mail send is wrapped in `try/catch` and logs warnings (mail failures must never break the request flow).

### Rate limiting on auth routes
- `routes/api.php`: `POST /register` and `POST /login` are now wrapped in `Route::middleware('throttle:10,1')` to mitigate brute-force credential stuffing.

### `frontend/src/environments/environment.development.ts` — new
- Mirrors `environment.ts` for explicit dev configuration (no behavior change, but Angular CLI now has the explicit file file replacement target available if needed).

## Routing

`frontend/src/app/app.routes.ts`:
- Added `/subscription/success` and `/subscription/cancel` under main layout (auth-only, role-agnostic).
- Added pilot child routes: `'' → PilotDashboardComponent`, `'upgrade' → PilotUpgradeComponent`.
- Login + Register `navigateByRole('pilot')` now sends pilots to `/pilot` (their new dashboard), not the old `/pilot/championships`.

## Verification

- `php -l` clean on every modified PHP file (controllers, services, mailables).
- `php artisan route:list` confirms the throttle middleware and new `/subscriptions` policy.
- `npx ng build` clean, output 661 kB initial (well under bumped 1 MB warning budget).
- `ReadLints` reports no errors anywhere in `frontend/src/app`.

## Laravel Blade admin panel (2026-05-19)

Replaced the Angular `/admin` SPA with a **session-based Laravel admin** at `/admin`:

- `routes/admin.php` — web routes for dashboard, users, categories, plans, subscriptions, payments, championships, circuits, races, inscriptions, results.
- `app/Http/Middleware/EnsureUserIsAdmin.php` + `admin` middleware alias.
- `app/Http/Controllers/Admin/Web/*` — full CRUD for competition entities (admin bypasses organizer subscription quota).
- `resources/views/admin/` + `public/css/admin.css`.
- Angular: removed `AdminLayoutComponent` routes; `/admin` redirects via `AdminRedirectComponent`; navbar links to `environment.adminUrl`.
- Docker nginx: `location /admin` → Laravel.

Login: `http://localhost:8000/admin/login` — `admin@pitstop.com` / `password`.

## Notes on Stripe + Mail in dev

- Stripe checkout flow still needs real `STRIPE_KEY`/`STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` to test end-to-end. Without them, `SubscriptionController::store` returns 503 with a clear message. The auto-promotion + activation email logic is exercised only when the `checkout.session.completed` webhook fires.
- Emails fall back to `storage/logs/laravel.log` when `MAIL_MAILER=log` (Laravel default for fresh apps). To send real emails, set `MAIL_MAILER=smtp` and configure the rest of `MAIL_*`.
