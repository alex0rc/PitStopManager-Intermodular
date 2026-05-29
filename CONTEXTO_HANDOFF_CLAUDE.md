# PitStop Manager — Contexto completo para continuar (handoff Claude / nuevo chat)

> **Propósito:** Documento extenso con todo lo hecho en el proyecto y el estado actual del despliegue Docker local. Copia este archivo al inicio de un chat nuevo con Claude (o otro asistente).
>
> **Última actualización:** mayo 2026  
> **Workspace local:** `c:\xampp\htdocs\LARAVEL\PitStopManager`  
> **Repo GitHub:** https://github.com/alex0rc/PitStopManager-Intermodular (ramas `main`, `develop`)

---

## Índice

1. [Qué es el proyecto](#1-qué-es-el-proyecto)
2. [Arquitectura técnica](#2-arquitectura-técnica)
3. [Estructura de carpetas](#3-estructura-de-carpetas)
4. [Qué se hizo en esta serie de trabajo (resumen cronológico)](#4-qué-se-hizo-en-esta-serie-de-trabajo-resumen-cronológico)
5. [Estado actual: qué estás haciendo tú](#5-estado-actual-qué-estás-haciendo-tú)
6. [Docker — configuración actual](#6-docker--configuración-actual)
7. [HTTPS y certificados](#7-https-y-certificados)
8. [DNS `pitstop.local` y archivo hosts](#8-dns-pitstoplocal-y-archivo-hosts)
9. [Variables `.env` (Laravel)](#9-variables-env-laravel)
10. [URLs y credenciales de prueba](#10-urls-y-credenciales-de-prueba)
11. [Problemas encontrados y soluciones aplicadas](#11-problemas-encontrados-y-soluciones-aplicadas)
12. [Comandos que ya ejecutaste / referencia rápida](#12-comandos-que-ya-ejecutaste--referencia-rápida)
13. [Archivos clave modificados](#13-archivos-clave-modificados)
14. [Documentación local (no en GitHub)](#14-documentación-local-no-en-github)
15. [Pendiente / no hacer sin pedirlo](#15-pendiente--no-hacer-sin-pedirlo)
16. [Prompt sugerido para nuevo chat](#16-prompt-sugerido-para-nuevo-chat)

---

## 1. Qué es el proyecto

**PitStop Manager** — plataforma para gestionar campeonatos de karting amateur.

| Rol | Acceso |
|-----|--------|
| **Admin** | Panel Laravel Blade en `/admin` |
| **Organizador** | SPA Angular + API |
| **Piloto** | SPA Angular + API |

**Stack:** Laravel 12 (PHP 8.2) + Angular 21 + MariaDB 10.11 + Nginx + Sanctum + Stripe + Mailtrap (dev).

**Importante:** El despliegue oficial acordado es **100 % Docker** (no XAMPP). En el PC solo hacen falta Docker Desktop, Git y Node (para `npm run build`).

---

## 2. Arquitectura técnica

### Un solo origen (mismo dominio)

```
Navegador
    │
    ▼
nginx (contenedor) :80 → redirige a :443
    │
    ├── HTTPS :443
    │       ├── /              → Angular (frontend/dist/frontend/browser)
    │       ├── /css, /js      → Laravel public/ (estilos panel admin)
    │       ├── /api           → Laravel API (PHP-FPM)
    │       ├── /admin         → Laravel Blade admin (PHP-FPM)
    │       ├── /sanctum       → CSRF cookies SPA
    │       └── /storage       → archivos subidos
    │
    ├── php (PHP-FPM 8.2) — sin puerto público
    ├── db (MariaDB) — host :3307 → contenedor :3306
    ├── phpmyadmin — :8080
    └── bind9 (opcional, perfil `dns`) — :53 → pitstop.local
```

### Servicio `certs` (Docker)

Antes del arranque de nginx, un contenedor one-shot genera certificados autofirmados en `docker/certs/` si no existen (`fullchain.pem`, `privkey.pem`).

### Frontend Angular

- Build de producción: `apiUrl: '/api'`, `adminUrl: '/admin'` (mismo host).
- Tras login **admin** en la SPA → redirección a `{origen}/admin/login` (ya no `localhost:8000`).

### Backend Laravel

- API: Sanctum (Bearer + cookies para SPA).
- Admin: sesión web + CSRF en formularios Blade.
- `AppServiceProvider`: `trustProxies`, URLs de assets según host real, ajuste de sesión en `localhost`.

---

## 3. Estructura de carpetas

```
PitStopManager/
├── backend/                 # Laravel 12
│   ├── app/
│   ├── public/              # index.php, css/admin.css, js/admin-*.js
│   ├── resources/views/admin/
│   ├── routes/api.php, admin.php, web.php
│   ├── .env                 # LOCAL — no en git
│   ├── .env.docker          # plantilla localhost HTTPS
│   ├── .env.docker.dns.example
│   └── .env.docker.vm.example
├── frontend/                # Angular 21
│   ├── src/
│   ├── dist/frontend/browser/   # build servido por nginx (gitignore)
│   └── proxy.conf.json      # ng serve → https://localhost
├── docker/
│   ├── nginx/
│   │   ├── default.conf
│   │   └── snippets/pitstop-app.conf
│   ├── php/Dockerfile
│   ├── certs/               # SSL generados (gitignore)
│   └── bind9/               # DNS opcional
├── docker-compose.yml
├── README.md                # en GitHub
├── GUIA_DOCKER_LOCAL.md     # guía local — gitignore
├── GUIA_VM_DESPLIEGUE.md
├── GUIA_VM_DOCKER_DESPLIEGUE.md
├── MEMORIA_Y_DESPLIEGUE.md
└── CONTEXTO_HANDOFF_CLAUDE.md   # este archivo
```

---

## 4. Qué se hizo en esta serie de trabajo (resumen cronológico)

### UI / UX (admin y frontend)

- Paginación admin Bootstrap 5 (`Paginator::useBootstrapFive`, vista `admin.partials.pagination`).
- Login/register SPA: layout pantalla completa (`main-layout` oculta navbar/footer en rutas auth).
- Logo integrado (`logo.png` en frontend/public y backend/public, componente `brand-logo`).
- Comentarios “tipo IA” sustituidos por secciones breves en español.

### Git / GitHub

- Repo: https://github.com/alex0rc/PitStopManager-Intermodular
- Guías largas en `.gitignore` (solo local); `README.md` orientado a GitHub.
- Restauración de `.md` borrados desde commit anterior cuando el usuario lo pidió.

### Documentación creada (local, gitignore)

| Archivo | Contenido |
|---------|-----------|
| `GUIA_DOCKER_LOCAL.md` | Docker Desktop Windows, HTTPS, BIND9 opcional, hosts, troubleshooting |
| `GUIA_VM_DESPLIEGUE.md` | VM Ubuntu, VirtualBox NAT 8443→443, Let's Encrypt copia manual |
| `GUIA_VM_DOCKER_DESPLIEGUE.md` | VirtualBox paso a paso + capturas TFG |
| `MEMORIA_Y_DESPLIEGUE.md` | Memoria técnica TFG |
| `SETUP.md`, `PROJECT_GUIDE.md`, `CHANGES.md` | restaurados |

### Docker / infraestructura

- **Sin XAMPP:** toda la pila en contenedores.
- **HTTPS:** puertos 80+443, redirección HTTP→HTTPS, servicio `certs` automático.
- **nginx** `pitstop-app.conf`:
  - `/api`, `/admin`, `/sanctum` → PHP `index.php` directo (fix “Error servidor 200” por HTML del SPA).
  - `/css`, `/js`, `/favicon.svg` → `backend/public/`.
  - `/logo.png` → frontend build.
  - `/` → Angular SPA.
- Eliminados: scripts `generate-dev.ps1`, certbot en compose, `host-reverse-proxy.example.conf`.
- **BIND9** opcional (`docker compose --profile dns`); en Windows **hosts** es el método recomendado.

### Frontend

- `environment.ts` / `development`: `adminUrl: '/admin'`.
- `environment.prod.ts`: `adminUrl: '/admin'`.
- `angular.json`: `fileReplacements` para producción.
- Utilidad `admin-panel-url.ts` para redirección admin con mismo origen.
- `login.component.ts` / `register.component.ts` usan `adminPanelLoginUrl()`.

### Backend

- `bootstrap/app.php`: `trustProxies(at: '*')`.
- `AppServiceProvider`: `URL::forceRootUrl` según request; sesión adaptable en `localhost`.
- Plantillas `.env.docker` completas (no recortes que pierdan MAIL_*, APP_KEY, etc.).

---

## 5. Estado actual: qué estás haciendo tú

Estás **levantando el proyecto en local con Docker Desktop (Windows)** para desarrollo y pruebas, usando:

- Dominio **`pitstop.local`** (entrada en `C:\Windows\System32\drivers\etc\hosts` → `127.0.0.1`).
- **HTTPS** (`https://pitstop.local`).
- Perfil Docker con **BIND9** levantado (`bind9` running); `nslookup pitstop.local 127.0.0.1` funciona, pero el navegador necesita **hosts** (no solo BIND9).
- Contenedores vistos en terminal: `nginx`, `php`, `db`, `phpmyadmin`, `bind9`, `certs` (exited after create).
- Comandos Laravel en contenedor: `composer install`, `key:generate`, `migrate --seed`, `storage:link`, `config:clear`.

**Últimos problemas que estabas resolviendo:**

1. ~~DNS NXDOMAIN en Chrome~~ → solución: archivo **hosts**.
2. ~~“Error del servidor (200)” en SPA~~ → fix nginx API.
3. ~~Redirect admin a `localhost:8000`~~ → fix `adminUrl` + rebuild frontend.
4. ~~Admin sin CSS + 419 Page Expired~~ → fix nginx `/css`/`/js`, `SESSION_DOMAIN` vacío, usar **https://pitstop.local/admin/login** (no `http://localhost`).

**URL correcta para admin ahora:** `https://pitstop.local/admin/login`  
**URL SPA:** `https://pitstop.local`

---

## 6. Docker — configuración actual

### `docker-compose.yml` (servicios)

| Servicio | Puerto host | Notas |
|----------|-------------|--------|
| `certs` | — | Crea SSL en `docker/certs/` la 1ª vez |
| `nginx` | 80, 443 | Entrada principal |
| `php` | (interno 9000) | Build `docker/php/Dockerfile` |
| `db` | 3307→3306 | user/pass `pitstop`/`pitstop`, root `secret` |
| `phpmyadmin` | 8080 | root / `secret` |
| `bind9` | 53 | perfil `dns` — opcional |

### Arranque típico

```powershell
cd c:\xampp\htdocs\LARAVEL\PitStopManager
cd frontend
npm ci
npm run build
cd ..
cd backend
copy .env.docker.dns.example .env
# Editar APP_KEY, Stripe, Mail si hace falta; pegar claves de .env.backup
cd ..
docker compose --profile dns up -d --build
docker compose exec -u root php composer install --no-dev --optimize-autoloader
docker compose exec -u root php php artisan key:generate --force
docker compose exec -u root php php artisan migrate --seed --force
docker compose exec -u root php php artisan storage:link
docker compose exec -u root php chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
docker compose exec -u root php php artisan config:clear
```

Sin BIND9: `docker compose up -d --build` (sin `--profile dns`).

---

## 7. HTTPS y certificados

- Certificados en `docker/certs/fullchain.pem` y `privkey.pem` (autofirmados, 825 días).
- Navegador mostrará aviso de seguridad → **Continuar** (normal en local).
- `docker/nginx/default.conf`: server 80 → 301 a HTTPS; server 443 con SSL.
- **No** hace falta ejecutar scripts manuales; `docker compose up` basta.

Regenerar certs: borrar `docker/certs/*.pem` y `docker compose up` de nuevo.

---

## 8. DNS `pitstop.local` y archivo hosts

### Obligatorio en Windows (para el navegador)

Editar como administrador `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1 pitstop.local
127.0.0.1 www.pitstop.local
```

Comprobar: `ping pitstop.local` → debe responder 127.0.0.1.

### BIND9 (opcional)

- `nslookup pitstop.local 127.0.0.1` puede funcionar con contenedor `bind9`.
- **Chrome no usa 127.0.0.1 como DNS** por defecto → por eso fallaba con `DNS_PROBE_FINISHED_NXDOMAIN`.
- Puedes parar BIND9 si usas hosts: `docker compose --profile dns stop bind9`

---

## 9. Variables `.env` (Laravel)

Archivo activo: `backend/.env` (local, no en git).

**Valores recomendados para Docker + `pitstop.local` + HTTPS:**

```env
APP_URL=https://pitstop.local
FRONTEND_URL=https://pitstop.local

DB_HOST=db
DB_PORT=3306
DB_DATABASE=pitstop_manager
DB_USERNAME=pitstop
DB_PASSWORD=pitstop

SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=pitstop.local,localhost,localhost:443

APP_KEY=base64:...   # generado con artisan key:generate
```

**Plantillas en repo:**

| Archivo | Uso |
|---------|-----|
| `backend/.env.docker` | `https://localhost` |
| `backend/.env.docker.dns.example` | `https://pitstop.local` (completo) |
| `backend/.env.docker.vm.example` | producción VM con dominio real |

**Errores si mezclas hosts:**

| Acceso | Problema |
|--------|----------|
| `http://localhost` + `SESSION_DOMAIN=pitstop.local` | 419 CSRF, cookies no válidas |
| `http://localhost` + `SESSION_SECURE_COOKIE=true` | cookies no se envían |
| `APP_URL=https://pitstop.local` pero abres `http://localhost` | assets/CSS en otro host |

---

## 10. URLs y credenciales de prueba

| Recurso | URL |
|---------|-----|
| SPA | https://pitstop.local |
| Login SPA | https://pitstop.local/login |
| Admin Blade | https://pitstop.local/admin/login |
| API ejemplo | https://pitstop.local/api/championships |
| phpMyAdmin | http://localhost:8080 (HTTP, puerto aparte) |
| MariaDB desde PC | `127.0.0.1:3307`, user `pitstop`, pass `pitstop` |

### Usuarios seed

| Email | Password | Rol |
|-------|----------|-----|
| admin@pitstop.com | password | Admin |
| carlos@pitstop.com | password | Organizador |
| piloto1@pitstop.com | password | Piloto |

**Nota:** Login en la SPA (API/Sanctum) y login en `/admin` (sesión Blade) son **sesiones distintas**. Tras login admin en SPA te redirige al panel; puede pedir login otra vez en el formulario Blade.

---

## 11. Problemas encontrados y soluciones aplicadas

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Paginación admin fea | Laravel usaba Tailwind por defecto | `Paginator::useBootstrapFive()` + vista custom |
| Login SPA con hueco blanco | Footer/navbar en rutas auth | `isAuthRoute` en `main-layout` |
| `DNS_PROBE_FINISHED_NXDOMAIN` | Browser no usa BIND9 | Archivo **hosts** |
| Toast “Error del servidor (200)” | `/api` devolvía HTML del SPA | nginx: fastcgi directo a `index.php` para `/api` |
| Redirect a `localhost:8000` | `environment.ts` antiguo | `adminUrl: '/admin'` + rebuild |
| Admin sin estilos | `/css/admin.css` servido por Angular | nginx: `location ^~ /(css|js)/` → Laravel public |
| **419 Page Expired** | `http://localhost` + cookies HTTPS/domain | Usar `https://pitstop.local`, `SESSION_DOMAIN` vacío |
| `.env.docker.dns` incompleto | Plantilla corta | Plantillas completas + guía backup |

---

## 12. Comandos que ya ejecutaste / referencia rápida

```powershell
# Estado
docker compose ps
docker compose logs -f nginx
docker compose logs -f php

# Reiniciar tras cambios
docker compose restart nginx php
docker compose exec -u root php php artisan config:clear

# Rebuild frontend tras cambios Angular
cd frontend
npm run build
cd ..
docker compose restart nginx

# BD desde cero (¡borra datos!)
docker compose exec -u root php php artisan migrate:fresh --seed --force
```

### Stripe local (opcional)

```powershell
stripe listen --forward-to https://pitstop.local/api/webhooks/stripe --skip-verify
```

---

## 13. Archivos clave modificados

### Docker / nginx

- `docker-compose.yml` — servicios `certs`, `nginx` 443, `bind9` profile
- `docker/nginx/default.conf` — HTTP→HTTPS
- `docker/nginx/snippets/pitstop-app.conf` — rutas SPA, API, admin, estáticos
- `docker/certs/README.md`
- `docker/bind9/named.conf`, `docker/bind9/zones/db.pitstop.local`

### Backend

- `backend/bootstrap/app.php` — trustProxies
- `backend/app/Providers/AppServiceProvider.php` — URL/sesión dinámica
- `backend/.env.docker`, `.env.docker.dns.example`, `.env.docker.vm.example`
- Vistas admin, paginación, emails, etc. (sesión anterior)

### Frontend

- `frontend/src/environments/environment.ts`
- `frontend/src/environments/environment.prod.ts`
- `frontend/src/app/core/utils/admin-panel-url.ts`
- `frontend/src/app/features/auth/login.component.ts`
- `frontend/src/app/features/auth/register.component.ts`
- `frontend/src/app/layouts/main-layout.component.ts/html/scss`
- `frontend/angular.json` — fileReplacements production
- `frontend/proxy.conf.json` — proxy a https://localhost

### Guías (gitignore, solo local)

- `GUIA_DOCKER_LOCAL.md`
- `GUIA_VM_DESPLIEGUE.md`
- `GUIA_VM_DOCKER_DESPLIEGUE.md`

---

## 14. Documentación local (no en GitHub)

Están en `.gitignore` — existen en tu disco pero no se suben al repo:

- `GUIA_DOCKER_LOCAL.md` — **guía principal** para tu situación actual
- `GUIA_VM_DESPLIEGUE.md` — VM producción
- `GUIA_VM_DOCKER_DESPLIEGUE.md` — VirtualBox + capturas TFG
- `MEMORIA_Y_DESPLIEGUE.md` — memoria técnica
- `SETUP.md`, `PROJECT_GUIDE.md`, `CHANGES.md`

En GitHub solo está el `README.md` resumido.

---

## 15. Pendiente / no hacer sin pedirlo

- **No** volver a XAMPP / `php artisan serve` / puerto 8000 para este flujo Docker.
- **No** commitear `.env`, `docker/certs/*.pem`, `vendor`, `node_modules`, `dist` (ya en gitignore).
- **No** commitear guías locales salvo que el usuario pida actualizar solo `README.md`.
- VM producción: copiar certs Let's Encrypt a `docker/certs/` (ver `docker/certs/README.md`).
- VirtualBox: reenvío NAT **8443→443** si accedes desde Windows a la VM.
- Regenerar/commit de logo si falta en `backend/public/logo.png` (comprobar en disco; puede estar gitignored).

---

## 16. Prompt sugerido para nuevo chat

Copia y pega esto en Claude:

```
Estoy trabajando en PitStop Manager (Laravel 12 + Angular 21) en Windows con Docker Desktop.

Contexto completo: lee el archivo CONTEXTO_HANDOFF_CLAUDE.md del proyecto (o el contenido que te adjunto).

Resumen rápido:
- Despliegue 100% Docker (nginx, php, mariadb, phpmyadmin, bind9 opcional).
- Acceso local: https://pitstop.local (hosts: 127.0.0.1 pitstop.local).
- HTTPS con certs autofirmados en docker/certs/ (servicio certs en compose).
- SPA en /, API en /api, admin Blade en /admin.
- Últimos fixes: nginx API→PHP, adminUrl /admin, CSS admin en /css, SESSION_DOMAIN vacío, 419 evitado usando https://pitstop.local.

Mi .env usa DB_HOST=db, APP_URL=https://pitstop.local, SESSION_DOMAIN vacío.

[N describe aquí tu problema o tarea concreta]
```

---

## Checklist rápido “¿está todo bien?”

- [ ] `hosts` tiene `pitstop.local` → 127.0.0.1
- [ ] `docker compose ps` → nginx, php, db running
- [ ] `frontend/dist/frontend/browser/index.html` existe
- [ ] `backend/.env` con `DB_HOST=db`, `APP_URL=https://pitstop.local`
- [ ] https://pitstop.local carga la SPA
- [ ] https://pitstop.local/admin/login tiene estilos y login sin 419
- [ ] https://pitstop.local/api/championships devuelve JSON (no HTML)

---

*Documento generado para continuidad entre chats. Actualízalo cuando cambies infraestructura o resuelvas nuevos bloqueos.*
