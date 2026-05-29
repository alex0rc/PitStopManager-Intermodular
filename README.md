# PitStop Manager

Plataforma web para la gestión de campeonatos de karting amateur. Proyecto final de DAW.

> **Configuración desde cero:** **[SETUP.md](SETUP.md)** — checklist MySQL, Stripe, Mailtrap, servidores.  
> **Documentación de arquitectura:** **[PROJECT_GUIDE.md](PROJECT_GUIDE.md)** (ideal también como contexto para IA).

## Tecnologías

- **Backend**: Laravel 12 (PHP 8.2) - API REST
- **Frontend**: Angular 21 - SPA standalone components
- **Base de datos**: MySQL / MariaDB
- **Autenticación**: Laravel Sanctum (tokens)
- **Pagos**: Stripe Checkout
- **PDF**: DomPDF (barryvdh/laravel-dompdf)
- **Meteorología**: OpenWeatherMap API
- **Estilos**: Bootstrap 5 + SCSS
- **Despliegue**: Docker + Nginx

## Roles del sistema

| Rol | Descripción |
|-----|-------------|
| **Admin** | Panel **Laravel Blade** en `/admin` (no Angular): usuarios, categorías, planes, suscripciones, pagos, **campeonatos, circuitos, carreras, inscripciones y resultados**. |
| **Organizador** | Crea campeonatos, circuitos, carreras. Gestiona inscripciones y resultados. Requiere suscripción activa para crear campeonatos. |
| **Piloto** | Consulta campeonatos, se inscribe, consulta resultados y clasificaciones. |

> **Registro público**: el endpoint `POST /api/register` siempre crea cuentas con rol `pilot` independientemente del payload. Las cuentas Admin se crean por seeder.

> **Panel de administración**: `http://localhost:8000/admin/login` (sesión Laravel, independiente del token Sanctum del SPA). Tras iniciar sesión como admin, gestiona todo el sistema desde el sidebar. En el navbar del SPA, «Panel Admin» abre esa URL en una pestaña nueva.

> **Cómo un piloto se convierte en Organizador**: el piloto puede contratar una suscripción desde su dashboard (`/pilot/upgrade`). Cuando Stripe envía el webhook `checkout.session.completed`, el backend activa la suscripción y **automáticamente promociona al usuario a `organizer`**. Alternativamente, un Admin puede cambiar el rol manualmente desde `http://localhost:8000/admin/users`.

> **Cuota por plan**: un organizador no puede tener más campeonatos activos (no cancelados ni finalizados) que `subscription_plans.max_championships`. El backend devuelve 403 con mensaje claro si se intenta superar el límite o si no hay suscripción activa.

> **Rate limiting**: los endpoints `POST /api/login` y `POST /api/register` están limitados a 10 peticiones por minuto por IP (middleware `throttle:10,1`).

> **Emails**: registro, activación de suscripción y cambios de estado de inscripción envían un email transaccional. En desarrollo usa **Mailtrap** (`MAIL_MAILER=smtp` + credenciales del sandbox); los mensajes se ven en la web de Mailtrap. Con `MAIL_MAILER=log` solo se escriben en `backend/storage/logs/laravel.log`.

## Requisitos previos

- PHP >= 8.2 con extensiones: pdo_mysql, mbstring, gd, zip, bcmath
- Composer
- Node.js >= 18
- npm
- MySQL o MariaDB
- (Opcional) Docker y Docker Compose

## Instalación local (desarrollo)

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd PitStopManager
```

### 2. Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Editar `.env` con los datos de tu base de datos:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pitstop_manager
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base de datos y ejecutar migraciones con datos de prueba:

```bash
mysql -u root -e "CREATE DATABASE pitstop_manager"
php artisan migrate --seed
php artisan storage:link
```

Iniciar el servidor de desarrollo:

```bash
php artisan serve
```

El backend estará disponible en `http://localhost:8000`.

### 3. Frontend (Angular)

```bash
cd frontend
npm install
npx ng serve
```

El frontend estará disponible en `http://localhost:4200`.

### 4. Configuración de servicios externos (opcional)

En `backend/.env`:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
OPENWEATHERMAP_API_KEY=tu_api_key
```

#### Email con Mailtrap (recomendado en desarrollo)

1. Crea cuenta en [Mailtrap](https://mailtrap.io) (plan gratuito basta).
2. Ve a **Email Testing** → **Email Sandbox** → tu inbox → pestaña **SMTP**.
3. Copia **Host**, **Port**, **Username** y **Password** en `backend/.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_FROM_ADDRESS=noreply@pitstopmanager.com
MAIL_FROM_NAME="PitStop Manager"
```

4. Limpia caché de config y reinicia Laravel:

```bash
php artisan config:clear
php artisan serve
```

5. Los correos (registro, suscripción activada, inscripción) aparecen en el inbox de Mailtrap, no en bandeja real.

**Probar envío:**

```bash
php artisan tinker
>>> Mail::raw('Test PitStop', fn ($m) => $m->to('piloto1@pitstop.com')->subject('Test'));
```

Notas:

- Si falta `OPENWEATHERMAP_API_KEY`, `GET /api/weather` responde `503` con mensaje claro (no rompe la app).
- Si falta `STRIPE_SECRET`, el endpoint `POST /api/subscriptions` responde `503`.
- Si falta `STRIPE_WEBHOOK_SECRET`, el webhook responde con `{"status":"error","message":"Webhook secret not configured"}` y no procesa el evento.
- El Checkout de Stripe usa `payment_method_types: ['card']`, envía `customer_email` y `metadata` con `subscription_id`/`payment_id` para que el webhook localice los registros de forma determinista.
- URLs de retorno de Stripe (compartidas entre piloto y organizador):
  - éxito: `${FRONTEND_URL}/subscription/success?session_id={CHECKOUT_SESSION_ID}`
  - cancelación: `${FRONTEND_URL}/subscription/cancel`
- Cuando el webhook `checkout.session.completed` activa la suscripción de un usuario con rol `pilot`, el backend lo promociona automáticamente a `organizer`. La página de éxito refresca el usuario en frontend (`GET /api/user`) y redirige a `/organizer/subscription`.

### Re-ejecutar las migraciones y seeders

Los seeders son **idempotentes** (usan `updateOrCreate` y resuelven referencias por email/slug/name en lugar de IDs fijos), así que se pueden re-ejecutar sin errores:

```bash
php artisan db:seed                  # vuelve a aplicar los seeders sobre datos existentes
php artisan migrate:fresh --seed     # destruye y reconstruye toda la BD
```

## Instalación con Docker

Guía detallada con VirtualBox y capturas para la memoria: **[GUIA_VM_DOCKER_DESPLIEGUE.md](GUIA_VM_DOCKER_DESPLIEGUE.md)**.

```bash
cd frontend
npm ci && npm run build
cd ..
docker compose up -d --build
docker compose exec -u root php composer install --no-dev
docker compose exec -u root php php artisan key:generate --force
docker compose exec -u root php php artisan migrate --seed --force
docker compose exec -u root php php artisan storage:link
```

La aplicación estará disponible en `http://localhost` (puerto 80 del contenedor nginx).

## Usuarios de prueba

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@pitstop.com | password | Admin |
| carlos@pitstop.com | password | Organizador |
| maria@pitstop.com | password | Organizador |
| piloto1@pitstop.com | password | Piloto |
| piloto2@pitstop.com | password | Piloto |
| piloto3@pitstop.com | password | Piloto |
| piloto4@pitstop.com | password | Piloto |
| piloto5@pitstop.com | password | Piloto |

## Estructura del proyecto

```
PitStopManager/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # Auth, Admin, Domain controllers
│   │   │   ├── Middleware/     # RoleMiddleware
│   │   │   ├── Requests/      # Form Request validation
│   │   │   └── Resources/     # API Resources (JSON responses)
│   │   ├── Models/             # Eloquent models (11)
│   │   ├── Policies/          # Authorization policies
│   │   └── Services/          # Weather, PDF, Stripe services
│   ├── database/
│   │   ├── migrations/        # 14 migrations
│   │   └── seeders/           # 9 seeders con datos de prueba
│   └── routes/
│       └── api.php            # 64 API endpoints
├── frontend/                   # Angular SPA
│   └── src/app/
│       ├── core/              # Guards, interceptors, services, models
│       ├── features/          # Auth, Admin, Organizer, Pilot, Public
│       ├── shared/            # Components reutilizables, pipes
│       └── layouts/           # Main layout, Admin layout
├── docker/                    # Docker configs
│   ├── nginx/default.conf
│   └── php/Dockerfile
├── docker-compose.yml
└── README.md
```

## API Endpoints (resumen)

| Grupo | Endpoints | Autenticación |
|-------|-----------|---------------|
| Auth | 4 (register, login, logout, user) | Público/Auth |
| Admin Users | 5 | Admin |
| Categories | 5 | Público lectura, Admin escritura |
| Circuits | 6 | Público lectura, Organizador escritura |
| Championships | 7 | Público lectura, Organizador escritura |
| Races | 5 | Público lectura, Organizador escritura |
| Inscriptions | 5 | Auth variable por rol |
| Results | 5 | Público lectura, Organizador escritura |
| Subscription Plans | 4 | Público lectura, Admin escritura |
| Subscriptions/Payments | 7 | Organizador/Admin |
| Weather | 1 | Público |
| Webhook Stripe | 1 | Sin auth |
| **Total** | **~55** | |

## Checklist de funcionalidades

- [x] Login, registro, logout con Sanctum
- [x] 3 roles: admin, organizador, piloto
- [x] CRUD usuarios (admin)
- [x] CRUD categorías (admin)
- [x] CRUD circuitos con subida de imagen
- [x] CRUD campeonatos con flujo de estados
- [x] CRUD carreras anidadas en campeonatos
- [x] Inscripciones de pilotos (crear, aprobar, rechazar, retirar)
- [x] Resultados por carrera con clasificación
- [x] Planes de suscripción (admin)
- [x] Integración Stripe Checkout
- [x] Webhook de Stripe
- [x] Generación PDF de comprobante
- [x] API meteorológica (OpenWeatherMap)
- [x] Panel admin con tablas, filtros, paginación
- [x] SPA Angular con guards, interceptors, reactive forms
- [x] Despliegue Docker + Nginx

## Convenciones importantes (alineación backend ↔ frontend)

| Tema | Valor canónico |
|---|---|
| Inscripción status | `pending` / `confirmed` / `rejected` / `withdrawn` |
| Championship status | `draft` / `published` / `in_progress` / `finished` / `cancelled` |
| Race status | `scheduled` / `in_progress` / `completed` / `cancelled` |
| Subscription status | `pending` / `active` / `expired` / `cancelled` |
| Payment status | `pending` / `succeeded` / `failed` / `refunded` |
| Forma de response Laravel | `JsonResource::withoutWrapping()` activado. Colecciones no paginadas devuelven arrays/objetos en bruto. Sólo las paginadas envuelven en `{data, meta, links}`. `/api/my/subscription` siempre devuelve `{data: <obj|null>}`. |
| Auth | Bearer token (Sanctum). No se usan cookies. |
| Retiro de inscripción | El piloto puede llamar `DELETE /api/inscriptions/{id}`: el backend marca `status='withdrawn'` y devuelve la fila. Los Admin con la misma llamada hacen hard-delete (204). |

## Licencia

Proyecto académico - DAW 2025.
