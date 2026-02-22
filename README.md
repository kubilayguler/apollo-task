

> **Note:** The Docker setup has not been fully tested yet due to insufficient internet connectivity during development. The application runs without issues in the local development environment. The `docker-compose.yml` and all related Docker configuration files are included and ready — end-to-end Docker testing will be completed and this section updated as soon as a stable connection is available.

## Quick Start (Docker)

```bash
# 1. Clone the repository
git clone https://github.com/your-org/todo-app.git
cd todo-app
```

Open `.env.docker` and set your **Gemini API key**:

```dotenv
GEMINI_API_KEY=your_gemini_api_key_here
```

```bash
# 2. Build and start all services (DB + App + Nginx) in the background
docker compose up --build -d

# 3. Open the app in your browser
#    http://localhost:8000
```

> On first start the container automatically runs all database migrations — the schema is ready immediately with no extra commands needed.

To stop:

```bash
docker compose down
```

To stop and wipe the database volume:

```bash
docker compose down -v
```

---

## What Runs Inside Docker

| Container | Image | Role |
|---|---|---|
| `todo_db` | `postgres:16-alpine` | Persistent PostgreSQL database |
| `todo_app` | Custom PHP 8.2-FPM | Laravel application |
| `todo_nginx` | `nginx:1.27-alpine` | HTTP server exposed on port **8000** |

Frontend assets (Vite + Tailwind CSS) are **compiled at image build time** using Node 20 — no separate Node service is needed at runtime.

---

## Local Development (without Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
composer run dev   # starts Laravel, Vite, queue listener, and log watcher concurrently
```

---

## Environment Variables

| Variable | Description |
|---|---|
| `APP_KEY` | Laravel encryption key — auto-generated on first Docker boot |
| `DB_DATABASE` | Database name (default: `todo_app`) |
| `DB_USERNAME` | Database user (default: `todo_user`) |
| `DB_PASSWORD` | Database password (default: `secret`) |
| `GEMINI_API_KEY` | **Required for AI features.** Get one at [aistudio.google.com](https://aistudio.google.com/) |
| `GEMINI_MODEL` | Model override (default: `gemini-2.5-flash`) |
| `APP_PORT` | Host port the app is exposed on (default: `8000`) |

---

## AI Integration

The AI assistant is built on the official **[Laravel AI SDK](https://github.com/laravel/ai)** (`laravel/ai`) which wraps the **Google Gemini** API.

### How it works

1. The user either types a free-form natural-language command in the AI input bar, or clicks the **AI Edit / Break Down** button on an existing task.
2. The request is sent to `TodoAiService`, which builds a strict **system prompt** that constrains Gemini to respond with pure JSON only.
3. Gemini returns a structured JSON object containing an `action`, a `parameters` block, and a short `feedback_message`.
4. `AiAssistantController` validates and executes the action against the database inside a transaction.

### Supported AI actions

| Action | What it does |
|---|---|
| `create_tasks` | Creates one or more tasks (with optional sub-tasks) in a project |
| `split_existing_task` | Breaks a single task into multiple sub-tasks |
| `auto_schedule` | Sets due dates across a list of tasks based on context |
| `optimize_description` | Rewrites a task's content to be clearer and more actionable |
| `update_task` | Edits the title, priority, status, or due date of an existing task |

---

## API Reference

All routes require an authenticated session. Unauthenticated requests are redirected to `/login`.

### Auth

| Method | Path | Description |
|---|---|---|
| `GET` | `/register` | Show registration form |
| `POST` | `/register` | Register a new user |
| `GET` | `/login` | Show login form |
| `POST` | `/login` | Authenticate user |
| `POST` | `/logout` | Log out current user |

### Dashboard

| Method | Path | Description |
|---|---|---|
| `GET` | `/dashboard` | Main dashboard — lists all projects and tasks |

### Projects

| Method | Path | Description |
|---|---|---|
| `POST` | `/projects` | Create a new project |
| `PUT` | `/projects/{project}` | Update project name, description, or icon |
| `DELETE` | `/projects/{id}` | Delete a project and all its tasks |
| `GET` | `/projects/{project}/settings` | Project settings page |

### Tasks

| Method | Path | Description |
|---|---|---|
| `POST` | `/projects/{project}/tasks` | Create a task inside a project |
| `POST` | `/tasks/{task}/toggle` | Toggle task status between `todo` and `completed` |
| `DELETE` | `/tasks/{task}` | Delete a task |

### AI Assistant

These routes live under the `/api` prefix and require a valid session (`web` + `auth` middleware).

| Method | Path | Description |
|---|---|---|
| `POST` | `/api/ai/text-command` | Natural-language command → creates or edits tasks |
| `POST` | `/api/ai/tasks/{task}/action` | Contextual AI action on an existing task |

#### POST `/api/ai/text-command`

**Request body:**
```json
{
  "command": "Add three tasks for onboarding a new developer by Friday",
  "project_id": 4
}
```

**Success response:**
```json
{
  "success": true,
  "action": "create_tasks",
  "message": "Created 3 onboarding tasks for you!",
  "data": { "..." }
}
```

#### POST `/api/ai/tasks/{task}/action`

**Request body:**
```json
{
  "action_type": "split_existing_task"
}
```

**Success response:**
```json
{
  "success": true,
  "action": "split_existing_task",
  "message": "Task split into 4 sub-tasks.",
  "data": { "..." }
}
```

---

## Database Schema

```
users     – id, name, email, password, timestamps
projects  – id, user_id (FK), name, description, slug, icon, is_archived, timestamps
tasks     – id, project_id (FK), parent_id (FK self-ref), title, content,
            priority (none|low|medium|high), status (todo|in_progress|completed),
            due_date, completed_at, order, timestamps
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Blade, Tailwind CSS v4, Alpine.js, FullCalendar, Flatpickr |
| Build tool | Vite 7 |
| AI | Google Gemini via `laravel/ai` |
| Database | PostgreSQL 16 (Docker) / SQLite (local dev) |
| Web server | Nginx 1.27 |
| Container | Docker + Docker Compose |
