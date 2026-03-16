# TaskFlow

Gestor de tareas y proyectos personales con API REST, roles, tags y 165 tests (99.8% coverage).

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Framework | Laravel 12 |
| Components | Livewire 3 + Volt (SFCs) |
| CSS | Tailwind CSS 4 |
| Auth | Breeze + Sanctum |
| Testing | Pest (165 tests, 99.8% coverage) |
| DB | SQLite (dev) / MySQL |

## Features

- **Proyectos** — CRUD con asignación a usuario
- **Tareas** — estados (pending, in_progress, completed), prioridades (low, medium, high, urgent), due dates
- **Tags** — etiquetas con relación N:N a tareas
- **API REST** — endpoints para tareas, proyectos y tags con Sanctum auth
- **Roles** — admin y user con middleware
- **Búsqueda y filtros** — por status, prioridad, texto
- **Paginación** — 15 items por página
- **165 tests** — 99.8% coverage con Pest

## API Endpoints

```
Auth:
  POST /api/login          → obtener token Sanctum
  POST /api/logout         → revocar token

Tasks:
  GET    /api/tasks        → listar (filtros: search, status, priority)
  POST   /api/tasks        → crear tarea
  PUT    /api/tasks/{id}   → actualizar
  DELETE /api/tasks/{id}   → eliminar

Projects:
  GET    /api/projects     → listar
  POST   /api/projects     → crear
  PUT    /api/projects/{id}
  DELETE /api/projects/{id}

Tags:
  GET    /api/tags
  POST   /api/tags
  PUT    /api/tags/{id}
  DELETE /api/tags/{id}
```

## Quick Start

```bash
git clone https://github.com/Carloolivera/taskflow.git
cd taskflow
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

```bash
composer run dev    # Server + Vite + Queue
```

## Testing

```bash
php artisan test           # 165 tests
./vendor/bin/pest           # Con Pest directamente
./vendor/bin/pest --coverage # Con coverage report
```

## Estructura

```
app/
├── Http/Controllers/Api/  → TaskController, ProjectController, TagController, AuthController
├── Http/Middleware/        → EnsureUserIsAdmin (RBAC)
├── Models/                 → User, Task, Project, Tag
database/
├── factories/              → TaskFactory, ProjectFactory, TagFactory
├── migrations/             → users, projects, tasks, tags, tag_task
├── seeders/                → DatabaseSeeder con datos demo
resources/livewire/
├── pages/tasks/            → TaskManager (Volt SFC)
├── pages/projects/         → ProjectManager (Volt SFC)
├── pages/tags/             → TagManager (Volt SFC)
tests/
├── Feature/                → API tests, Auth tests, CRUD tests
├── Unit/                   → Model tests
```

---

Desarrollado por [AIDO Digital Agency](https://aidoagencia.com) · Chascomús, Buenos Aires
