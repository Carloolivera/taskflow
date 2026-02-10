# CLAUDE.md - TaskFlow

## Session Init

Al inicio de cada sesion automaticamente:
1. Lee el ultimo session log en `C:\DEV\.aido-system\context\active\`
2. Lee este CLAUDE.md completo
3. Reporta: cantidad de tests, coverage, y ultima feature completada
4. Pregunta en que quiero trabajar hoy

## Proyecto

Gestor de tareas/proyectos personal. Demuestra todas las herramientas del sistema AIDO.
Repo: https://github.com/Carloolivera/taskflow

## Stack

- Laravel 12.50 + PHP 8.2+
- Livewire 3.7 (downgraded por Breeze v2.x)
- Livewire Volt 1.7 (componentes funcionales en auth)
- Laravel Breeze 2.3 (auth: login, register, password reset, profile)
- Laravel Sanctum 4.3 (API auth con tokens)
- Tailwind CSS 4 (ya incluido en Laravel 12, no necesita tailwind.config.js)
- Alpine.js (incluido con Livewire)
- SQLite (desarrollo) / PostgreSQL (produccion)
- Pest 3.8.5 + PHPUnit 11.5.3
- Vite 7.3

## Sistema AIDO

El sistema AIDO esta en `C:\DEV\.aido-system\` con esta estructura:
- `skills/` - Guias paso a paso reutilizables (CRUD, auth, API, deploy, testing)
- `workflows/` - Procedimientos multi-paso (nuevo proyecto, migracion DB, handoff IA)
- `agents/` - Roles especializados (laravel-expert, database-architect, devops, frontend)
- `templates/` - .env, Dockerfile, docker-compose
- `context/active/` - Session logs de la sesion actual
- `context/archive/` - Historial de sesiones anteriores

Siempre leer el session log activo en `C:\DEV\.aido-system\context\active\` al inicio de cada sesion.

## Convenciones

- Modelos: singular PascalCase (`Project`, `Task`, `Tag`)
- Tablas: plural snake_case (`projects`, `tasks`, `tags`)
- Componentes Livewire: PascalCase (`ProjectManager`, `TaskManager`, `TagManager`, `Dashboard`)
- Services: `XyzService` para logica compleja
- Form Requests para validaciones
- Factories + Seeders para datos de prueba
- Pivots: orden alfabetico (`tag_task`)

## Lecciones importantes

- Breeze hizo downgrade de Livewire 4 -> 3 (Breeze v2.x requiere Livewire 3)
- Layout esta en `resources/views/layouts/app.blade.php` (Breeze standard)
- Componentes Livewire full-page usan `#[Layout('layouts.app')]` attribute
- Vistas Livewire usan `<x-slot name="header">` para el header de Breeze
- Vistas deben soportar dark mode (clases `dark:` de Tailwind)
- Tailwind CSS 4 usa `@import 'tailwindcss'` en app.css
- SQLite viene preconfigurado en Laravel 12
- Pest v4 requiere PHP 8.3+; usar Pest 3.8.5 con `-W` flag en PHP 8.2
- Timezone: `America/Argentina/Buenos_Aires`

## Comandos

```bash
php artisan serve          # Servidor dev (puerto 8000)
npm run dev                # Vite watch
php artisan migrate:fresh --seed  # Reset DB con datos
php artisan test           # Tests (165)
php artisan test --coverage # Con cobertura (requiere Xdebug)
```

## Estado actual

- Auth completa (Breeze + Livewire): login, register, password reset, profile, logout
- Roles y permisos: admin/member con middleware `EnsureUserIsAdmin`
- Dashboard dinamico (Livewire): stats de proyectos/tareas, actividad reciente, acciones rapidas (rol-based)
- Project CRUD completo (Livewire): crear, editar, eliminar, buscar, filtrar por estado, paginacion
- Task CRUD completo (Livewire): crear con tags, editar, eliminar, filtros (status, priority, tag, search)
- Tag CRUD completo (Livewire, admin only): crear con color picker, editar, eliminar, buscar
- API REST (Sanctum): /api/projects (scoped to user), /api/projects/{id}/tasks (nested), /api/tags (read: all, write: admin)
- API Auth: /api/register, /api/login, /api/logout, /api/user
- Navegacion: Dashboard, Proyectos, Etiquetas (admin), badge Admin en dropdown
- Rutas Web: `/` (welcome), `/dashboard` (auth), `/projects` (auth), `/projects/{project}/tasks` (auth), `/tags` (admin), `/profile` (auth)
- DB: users (con role), projects, tasks, tags, tag_task, cache, jobs, sessions
- Tests: 165 pasando (Pest + PHPUnit)
- Cobertura de codigo: 99.8% (100% en todo el codigo custom)

## Roles

- `admin`: acceso completo (tags, API write)
- `member`: acceso a dashboard, proyectos propios, tareas propias
- Middleware: `admin` alias -> `EnsureUserIsAdmin`
- Helper: `$user->isAdmin()` en el modelo User
- Seeders: admin@example.com (admin), test@example.com (member)

## Tests

```bash
php artisan test                                    # Todos los tests (165)
php artisan test --filter="DashboardTest"           # Tests de Dashboard (21)
php artisan test --filter="RoleMiddlewareTest"      # Tests de Roles (8)
php artisan test --filter="ProjectManagerTest"      # Tests de ProjectManager (16)
php artisan test --filter="ProjectApiTest"          # Tests de API Projects (13)
php artisan test --filter="AuthApiTest"             # Tests de API Auth (20)
php artisan test --filter="TagManagerTest"          # Tests de TagManager (17)
php artisan test --filter="TaskManagerTest"         # Tests de TaskManager (23)
php artisan test --filter="TaskApiTest"             # Tests de API Tasks (18)
php artisan test --coverage                         # Con cobertura (requiere Xdebug)
```

## Modelos y relaciones

- **User**: role (admin/member), hasMany Project, hasMany Task
- **Project**: name, description, status (active/completed/archived), user_id. belongsTo User, hasMany Task. Scopes: active(), completed(), archived()
- **Task**: title, description, status (pending/in_progress/completed), priority (low/medium/high/urgent), due_date, project_id, user_id. belongsTo Project/User, belongsToMany Tag. Scopes: pending(), inProgress(), completed(), overdue(), byPriority()
- **Tag**: name (unique), color (hex). belongsToMany Task

## Extensiones PHP instaladas

- Xdebug 3.3.1 (modo coverage) - `C:\xampp\php\ext\php_xdebug.dll`

## Skills AIDO disponibles

- **CRUD**: `C:\DEV\.aido-system\skills\laravel-crud-generator.md`
- **Auth**: `C:\DEV\.aido-system\skills\laravel-auth-setup.md`
- **API REST**: `C:\DEV\.aido-system\skills\laravel-api-rest.md`
- **Testing**: `C:\DEV\.aido-system\skills\laravel-testing.md`
- **Deploy Hostinger**: `C:\DEV\.aido-system\skills\laravel-deploy-hostinger.md`
- **Export CSV/Excel**: `C:\DEV\.aido-system\skills\laravel-export-csv-excel.md`
- **Roles/Permisos**: `C:\DEV\.aido-system\skills\laravel-roles-permissions.md`

## Slash Commands

- `/crud [Modelo campo1:tipo campo2:tipo]` - Genera CRUD completo siguiendo skill AIDO
- `/test-coverage` - Analiza coverage y sugiere tests faltantes
- `/handoff` - Genera documento de handoff para cambiar de IA
- `/deploy-check` - Checklist pre-deploy para Hostinger

## Proximos pasos sugeridos

- [ ] Export CSV/Excel para proyectos y tareas
- [ ] Tests E2E con Laravel Dusk
- [ ] Notificaciones (email on due date)
- [ ] Dashboard charts con Chart.js
- [ ] Deploy check para Hostinger
