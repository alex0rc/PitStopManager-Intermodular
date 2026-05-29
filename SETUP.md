# PitStop Manager — Configuración desde cero

Guía paso a paso para dejar el proyecto funcionando en local (Windows + XAMPP).

---

## 1. Requisitos

| Herramienta | Versión mínima |
|-------------|----------------|
| PHP | 8.2+ (extensiones: pdo_mysql, mbstring, openssl, gd, zip) |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL | XAMPP o MariaDB |
| (Opcional) Stripe CLI | Para webhooks en local |

---

## 2. Base de datos

1. Inicia **MySQL** en XAMPP.
2. Crea la base de datos `pitstop_manager` (phpMyAdmin o consola).
3. En `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pitstop_manager
DB_USERNAME=root
DB_PASSWORD=
```

4. Migra y siembra datos de prueba:

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Tras el seed (con **MAIL** configurado, p. ej. Mailtrap), se envían automáticamente (con ~1 s entre cada uno para no superar el límite de Mailtrap):

| Destinatario | Email |
|--------------|--------|
| carlos@pitstop.com | Suscripción activada |
| maria@pitstop.com | Caduca en 7 días |
| piloto2@pitstop.com | Caduca en 1 día (pasa a organizador en el seed) |
| piloto1…piloto5@pitstop.com | Recordatorio carrera «GP Campillos (mañana)» |

**Importante:** guarda `.env` con **Ctrl+S** cada vez que lo edites.

---

## 3. URLs y Laravel

En `backend/.env`:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200
SANCTUM_STATEFUL_DOMAINS=localhost:4200,localhost:8000
```

```bash
php artisan config:clear
php artisan serve
```

→ API y admin: http://localhost:8000  
→ Admin: http://localhost:8000/admin/login

---

## 4. Frontend Angular

```bash
cd frontend
npm install
npx ng serve
```

→ http://localhost:4200 (el proxy envía `/api` al puerto 8000)

---

## 5. Stripe (pagos y upgrade piloto → organizador)

### 5.1 Claves API

1. [Stripe Dashboard](https://dashboard.stripe.com) → modo **Test**.
2. Developers → API keys.
3. En `.env` (**guardar archivo**):

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

```bash
php artisan config:clear
```

### 5.2 Probar checkout (sin webhook)

1. Login: `piloto1@pitstop.com` / `password`
2. `/pilot/upgrade` → elegir plan → pagar con `4242 4242 4242 4242`
3. Tras el pago, la página `/subscription/success` **confirma** la sesión y cambia el rol a **organizer** (no hace falta webhook para eso).

### 5.3 Webhook en local

#### Opción A — Sin webhook (la más simple)

No necesitas Stripe CLI. Tras pagar en Stripe, la app vuelve a `/subscription/success` y llama a `POST /api/subscriptions/confirm` con el `session_id`. Eso activa la suscripción y el rol organizador.

Solo necesitas `STRIPE_KEY` y `STRIPE_SECRET` en `.env`.

#### Opción B — Stripe CLI (`stripe login` no funciona)

Si `stripe login` falla (firewall, PATH, Windows):

1. Instala la CLI desde [github.com/stripe/stripe-cli/releases](https://github.com/stripe/stripe-cli/releases) (ZIP para Windows, no hace falta `stripe login` si usas la opción C).
2. O con Chocolatey: `choco install stripe-cli`
3. Abre **PowerShell como usuario normal** (no admin) y prueba: `stripe --version`
4. `stripe login` abre el navegador; si no abre, copia el enlace que muestra la terminal y pégalo en el navegador manualmente.
5. Luego:

```bash
stripe listen --forward-to http://127.0.0.1:8000/api/webhooks/stripe
```

Copia el `whsec_...` que imprime al arrancar (empieza por `whsec_`).

#### Opción C — Webhook en el Dashboard + túnel (sin `stripe login`)

1. Instala [ngrok](https://ngrok.com/) o usa [localtunnel](https://localtunnel.github.io/www/).
2. Con Laravel en marcha (`php artisan serve`):

```bash
ngrok http 8000
```

3. En [Stripe Dashboard](https://dashboard.stripe.com/test/webhooks) → **Añadir endpoint**:
   - URL: `https://TU-SUBDOMINIO.ngrok-free.app/api/webhooks/stripe`
   - Eventos: `checkout.session.completed`, `checkout.session.expired`
4. Tras crear el endpoint, abre **Signing secret** → copia `whsec_...` al `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

5. `php artisan config:clear` y reinicia `php artisan serve`.

Cada vez que reinicies ngrok cambia la URL: debes actualizar el endpoint en Stripe o usar un dominio fijo de ngrok de pago.

#### Variables finales

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

Reinicia `php artisan serve` tras guardar `.env`.

---

## 6. Mailtrap (emails)

1. [mailtrap.io](https://mailtrap.io) → Email Testing → Inbox → **SMTP**.
2. En `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS=noreply@pitstopmanager.com
MAIL_FROM_NAME="PitStop Manager"
```

Prueba: `php artisan tinker` → `Mail::raw('Test', fn($m) => $m->to('piloto1@pitstop.com')->subject('Test'));`  
→ revisa el inbox en Mailtrap.

**Emails transaccionales (envío inmediato, sin cola):**

| Evento | Plantilla |
|--------|-----------|
| Registro de usuario | `emails.welcome` |
| Suscripción activada (Stripe) | `emails.subscription-activated` |
| Comprobante de pago (Stripe) | `emails.payment-receipt` |
| Suscripción caduca en 7 días | `emails.subscription-expiring` |
| Suscripción caduca en 1 día | `emails.subscription-expiring` |
| Solicitud de inscripción (piloto) | `emails.inscription-submitted` |
| Nueva inscripción pendiente (organizador) | `emails.new-inscription-organizer` |
| Resumen diario inscripciones pendientes | `emails.pending-inscriptions-digest` |
| Inscripción confirmada/rechazada | `emails.inscription-status` |
| Campeonato publicado | `emails.championship-published` |
| Circuito aprobado/rechazado (admin) | `emails.circuit-status` |
| Recordatorio 1 día antes de carrera | `emails.race-reminder` (carrera «mañana» en seed) |

Los recordatorios de carrera se envían a pilotos con inscripción **confirmada** en el campeonato de esa carrera. El comando `races:send-reminders` busca carreras con fecha **mañana** (zona horaria de la app).

Prueba recordatorio (carrera mañana en BD):

```bash
php artisan races:send-reminders
# o simular “hoy” como ayer para una carrera del día siguiente:
php artisan races:send-reminders --date=2026-05-18
```

Usa `QUEUE_CONNECTION=sync` en `.env` en desarrollo, o ejecuta `php artisan queue:work` si usas `database`.

---

## 7. OpenWeatherMap (opcional)

```env
OPENWEATHERMAP_API_KEY=tu_api_key   # openweathermap.org → API keys (puede tardar hasta 2h en activarse)
```

Sin clave: el tiempo en detalle de campeonato devuelve 503; el resto funciona.

---

## 8. Checklist rápido

| Paso | Comando / URL | ¿Hecho? |
|------|----------------|---------|
| MySQL + migrate --seed | `php artisan migrate --seed` | ☐ |
| APP_KEY generada | `php artisan key:generate` | ☐ |
| `.env` guardado | Ctrl+S | ☐ |
| `php artisan serve` | :8000 | ☐ |
| `npx ng serve` | :4200 | ☐ |
| STRIPE_SECRET en .env | config:clear | ☐ |
| Mailtrap (opcional) | MAIL_USERNAME/PASSWORD | ☐ |
| Admin login | admin@pitstop.com / password | ☐ |

---

## 9. Usuarios de prueba

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@pitstop.com | password | Admin |
| carlos@pitstop.com | password | Organizador (plan Profesional, 90 días) |
| maria@pitstop.com | password | Organizador Valencia (caduca en 7 días — email recordatorio) |
| pedro@pitstop.com | password | Organizador Alicante / Costa Blanca |
| javier.org@pitstop.com | password | Organizador Murcia |
| piloto1@pitstop.com … piloto10@pitstop.com | password | Pilotos (Levante) |
| piloto2@pitstop.com | password | Tras seed puede quedar organizador (suscripción demo) |

Panel admin: http://localhost:8000/admin  
Sitio público: http://localhost:4200

---

## 10. Expiración automática de suscripciones

Cuando `ends_at` pasa, el sistema marca la suscripción como `expired` y, si el usuario no tiene otra suscripción activa, cambia su rol de **organizador** a **piloto**.

**En local (desarrollo):** en otra terminal, deja corriendo el planificador:

```bash
cd backend
php artisan schedule:work
```

**Probar manualmente:**

```bash
php artisan subscriptions:expire
php artisan subscriptions:send-expiry-reminders
php artisan races:send-reminders
php artisan inscriptions:send-pending-digest
```

| Comando | Qué hace |
|---------|----------|
| `subscriptions:expire` | Marca suscripciones vencidas y degrada organizador → piloto |
| `subscriptions:send-expiry-reminders` | Email 7 días y 1 día antes de `ends_at` |
| `races:send-reminders` | Email a pilotos con carrera al día siguiente (08:00) |
| `inscriptions:send-pending-digest` | Resumen diario a organizadores con inscripciones pendientes (09:30) |

### Planes y límites (seed)

| Plan | Precio | Días | Campeonatos activos máx. |
|------|--------|------|---------------------------|
| Básico | 29,99 € | 30 | 1 |
| Profesional | 59,99 € | 90 | 3 |
| Premium | 99,99 € | 365 | 10 |

Cuentan como “activos” los campeonatos en estado `draft`, `published` o `in_progress`. La API devuelve el cupo en `GET /api/my/subscription` → campo `quota`.

**En producción:** programa una tarea del sistema que ejecute cada minuto:

```bash
php artisan schedule:run
```

---

## 11. Terminales en el día a día

```text
1) cd backend  → php artisan serve
2) cd frontend → npx ng serve
3) (opcional) stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
4) (opcional) php artisan schedule:work  → expira suscripciones, recordatorios suscripción (09:00), carreras (08:00)
```

---

## 12. Problemas frecuentes

| Síntoma | Solución |
|---------|----------|
| Stripe no configurado | Guarda `.env`, `php artisan config:clear`, reinicia serve |
| Rol no cambia tras pago | Abre de nuevo la URL de success con `?session_id=...` o edita rol en admin → Usuarios |
| Emails no llegan | Revisa Mailtrap; con `MAIL_MAILER=log` miran `storage/logs/laravel.log` |
| Spinner infinito en Angular | Reinicia `ng serve` tras cambios en `angular.json` |
