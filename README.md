# PitStop Manager

Plataforma web para gestionar campeonatos de karting amateur: circuitos, inscripciones, resultados, suscripciones y panel de administración.

**Repositorio:** [github.com/alex0rc/PitStopManager-Intermodular](https://github.com/alex0rc/PitStopManager-Intermodular)

## Características

- **Tres roles:** administrador, organizador y piloto
- **SPA pública** (Angular): campeonatos, circuitos, inscripciones, clasificaciones y meteorología
- **Panel admin** (Laravel Blade): gestión completa del sistema en `/admin`
- **API REST** con Laravel Sanctum (Bearer token)
- **Pagos** con Stripe Checkout y webhooks
- **Emails** transaccionales (registro, inscripciones, suscripciones, recordatorios)
- **Despliegue** con Docker + Nginx

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Angular 21 (standalone) |
| Base de datos | MySQL / MariaDB |
| Auth API | Laravel Sanctum |
| UI | Bootstrap 5, SCSS |
| Pagos | Stripe |
| PDF | DomPDF |
| Mapas / ubicación | Leaflet, geocodificación |

## Requisitos

- PHP 8.2+ (`pdo_mysql`, `mbstring`, `gd`, `zip`, `bcmath`)
- Composer 2.x
- Node.js 18+
- MySQL o MariaDB
- (Opcional) Docker y Docker Compose

## Inicio rápido (local)

### 1. Clonar e instalar

```bash
git clone https://github.com/alex0rc/PitStopManager-Intermodular.git
cd PitStopManager-Intermodular
```

### 2. Backend

```bash
cd backend
composer install
cp .env.example .env   # Windows: copy .env.example .env
php artisan key:generate
```

Crea la base de datos `pitstop_manager` y configura `backend/.env`:

```env
DB_DATABASE=pitstop_manager
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200
SANCTUM_STATEFUL_DOMAINS=localhost:4200,localhost:8000
```

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

→ API: http://localhost:8000  
→ Admin: http://localhost:8000/admin/login

### 3. Frontend

```bash
cd frontend
npm install
npm start
```

→ App: http://localhost:4200

## Variables de entorno (opcionales)

| Variable | Uso |
|----------|-----|
| `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` | Suscripciones y pagos |
| `OPENWEATHERMAP_API_KEY` | Tiempo en circuitos/carreras |
| `MAIL_*` | SMTP (p. ej. [Mailtrap](https://mailtrap.io) en desarrollo) |

Sin Stripe configurado, `POST /api/subscriptions` responde 503. Sin OpenWeather, `GET /api/weather` responde 503.

## Docker

```bash
cd frontend && npm ci && npm run build && cd ..
docker compose up -d --build
docker compose exec -u root php composer install --no-dev
docker compose exec -u root php php artisan key:generate --force
docker compose exec -u root php php artisan migrate --seed --force
docker compose exec -u root php php artisan storage:link
```

Aplicación en http://localhost (puerto 80). MariaDB expuesta en el puerto **3307**.

## Usuarios de prueba

Contraseña para todos: `password`

| Email | Rol |
|-------|-----|
| admin@pitstop.com | Admin |
| carlos@pitstop.com | Organizador |
| maria@pitstop.com | Organizador |
| piloto1@pitstop.com … piloto5@pitstop.com | Piloto |

El registro público (`POST /api/register`) siempre crea cuentas **piloto**. Un piloto puede pasar a organizador con suscripción activa (Stripe) o por cambio de rol desde el admin.

## Estructura

```
PitStopManager/
├── backend/          # API Laravel + panel admin Blade
├── frontend/         # SPA Angular
├── docker/           # Nginx y PHP
└── docker-compose.yml
```

## Ramas

| Rama | Uso |
|------|-----|
| `main` | Versión estable |
| `develop` | Desarrollo |

## Licencia

Proyecto académico — DAW.
