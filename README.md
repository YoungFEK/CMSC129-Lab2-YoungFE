# TodoList - CMSC129 Lab 2

A Laravel 12 web application for managing tasks using the **MVC architecture**, **PostgreSQL**, **Blade templating**, and **Eloquent ORM**.

---

## 📌 Project Description

**TodoList** is a task management system built for the CMSC129 Lab 2 requirements. It allows users to:

- create, view, update, and delete tasks
- assign tasks to categories
- search and filter tasks
- mark tasks as `Pending`, `In Progress`, or `Done`
- manage task priority (`Low`, `Medium`, `High`)
- soft-delete tasks and restore them from trash

The homepage redirects to the main task list at `/tasks`.

---

## ✅ Requirement Checklist

### Core Requirements
- **CRUD operations** for tasks ✅
- **MVC architecture** using Laravel ✅
- **PostgreSQL database integration** ✅
- **Blade templating engine** ✅
- **Form validation** using Form Request classes ✅

### Additional / Expanded Features
- **Soft delete with restore and permanent delete** ✅
- **Search and filter** by title, description, status, and category ✅
- **Database relationships** ✅
- **Database seeding with Faker** ✅

> This project implements **both 3a (Database Relationships)** and **3b (Database Seeding with Faker)**.

---

## 🧱 MVC Implementation

### Models
Located in `app/Models/`

- `Task.php`
  - uses `SoftDeletes`
  - stores title, description, due date, status, priority, and category
  - defines query scopes for search and filtering
- `Category.php`
  - groups related tasks

### Views
Located in `resources/views/`

- `layouts/app.blade.php` – shared master layout
- `tasks/index.blade.php` – task list with search/filter UI
- `tasks/create.blade.php` – create task form
- `tasks/edit.blade.php` – edit task form
- `tasks/show.blade.php` – task details page
- `tasks/trash.blade.php` – soft-deleted tasks page

### Controller
Located in `app/Http/Controllers/`

- `TaskController.php`
  - `index()` – list tasks
  - `create()` / `store()` – create tasks
  - `show()` – display one task
  - `edit()` / `update()` – edit tasks
  - `destroy()` – soft delete
  - `trash()` – show deleted tasks
  - `restore()` – restore deleted tasks
  - `forceDelete()` – permanently remove tasks

---

## 🗃️ Database Design

### Tables
- `tasks`
- `categories`

### Relationship
- A `Task` **belongs to** one `Category`
- A `Category` **has many** `Task` records

This relationship is implemented using Eloquent:
- `Task -> belongsTo(Category::class)`
- `Category -> hasMany(Task::class)`

---

## 🌱 Database Seeding

Sample data is generated using **Faker**.

### Included seeders/factory
- `database/factories/TaskFactory.php`
- `database/seeders/CategorySeeder.php`
- `database/seeders/TaskSeeder.php`
- `database/seeders/DatabaseSeeder.php`

### Seeded sample data
- **5 categories**
- **15 tasks**

Run:
```bash
php artisan migrate:fresh --seed
```

---

## 🔍 Features

### Task Management
- add new tasks
- edit existing tasks
- view task details
- soft-delete tasks
- restore or permanently delete from trash

### Search and Filtering
- search by task title or description
- filter by:
  - status
  - category

### UI Features
- reusable Blade layout
- action icons for task controls
- modal confirmation for delete actions
- responsive layout using Tailwind CSS

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** PostgreSQL
- **Templating:** Blade
- **ORM:** Eloquent
- **Styling:** Tailwind CSS
- **Frontend Build Tool:** Vite
- **Testing:** PHPUnit

---

## 🚀 Installation and Setup

### 1. Install dependencies
```bash
composer install
npm install
```

### 2. Configure environment
Copy `.env.example` to `.env`, then update the database settings:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cmsc129_lab2
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Generate the app key:
```bash
php artisan key:generate
```

### 3. Run migrations and seeders
```bash
php artisan migrate:fresh --seed
```

### 4. Build frontend assets
```bash
npm run build
```

For development, you may also use:
```bash
npm run dev
```

### 5. Start the application
```bash
php artisan serve
```

Open:
```text
http://127.0.0.1:8000/tasks
```

---

## 🧪 Testing

Run the test suite with:

```bash
php artisan test
```

---

## 📍 Important Routes

| Method | Route | Purpose |
|---|---|---|
| `GET` | `/` | Redirect to task list |
| `GET` | `/tasks` | Display all tasks |
| `GET` | `/tasks/create` | Show create form |
| `POST` | `/tasks` | Save new task |
| `GET` | `/tasks/{task}` | View task details |
| `GET` | `/tasks/{task}/edit` | Show edit form |
| `PUT/PATCH` | `/tasks/{task}` | Update task |
| `DELETE` | `/tasks/{task}` | Soft delete task |
| `GET` | `/tasks-trash/trash` | Show trash |
| `GET` | `/tasks-trash/{id}/restore` | Restore task |
| `DELETE` | `/tasks-trash/{id}/force-delete` | Permanently delete task |

---

## 📁 Key Files

```text
app/Http/Controllers/TaskController.php
app/Http/Requests/StoreTaskRequest.php
app/Http/Requests/UpdateTaskRequest.php
app/Models/Task.php
app/Models/Category.php
database/factories/TaskFactory.php
database/seeders/CategorySeeder.php
database/seeders/TaskSeeder.php
resources/views/tasks/index.blade.php
resources/views/tasks/create.blade.php
resources/views/tasks/edit.blade.php
resources/views/tasks/show.blade.php
resources/views/tasks/trash.blade.php
routes/web.php
```

---

## 👨‍💻 Author

**Francis E. Young**  
CMSC129 - Lab 2


---

## 📄 License

MIT License - See LICENSE file for details.

---

## 👤 Author

Young FE - CMSC129 Lab 2

---

## 📞 Support

For issues or questions, refer to [Laravel Documentation](https://laravel.com/docs)

