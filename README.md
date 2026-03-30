# Task Manager - CMSC129 Lab 2

A full-stack web application demonstrating the **MVC (Model-View-Controller)** architectural pattern using **Laravel framework**, **PostgreSQL**, and **Blade templating engine**.

## 📸 Screenshots

[Add your screenshots here]

---

## 🎯 Project Overview

**Task Manager** is a web application that allows users to:
- ✅ Create, read, update, and delete tasks
- 🏷️ Organize tasks by categories
- 🔍 Search and filter tasks
- 📊 Track task status (Pending, In Progress, Done)
- ⭐ Set task priority levels (Low, Medium, High)
- 🗑️ Soft-delete and restore tasks
- 📅 Set due dates for tasks

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP Framework)
- **Database**: PostgreSQL
- **Templating**: Blade (Laravel's templating engine)
- **Frontend**: Tailwind CSS
- **ORM**: Eloquent
- **Dependency Manager**: Composer

---

## 📋 Features Implemented

### ✅ Minimum Requirements

1. **CRUD Operations** - Full Create, Read, Update, Delete functionality for Tasks
2. **MVC Architecture** - Proper separation of concerns:
   - **Models**: `Task`, `Category` (Eloquent models)
   - **Views**: Blade templates in `resources/views/`
   - **Controllers**: `TaskController` for business logic
3. **PostgreSQL Database** - Using Eloquent ORM for all queries
4. **Blade Templating** - Master layout with reusable components
5. **Form Validation** - `StoreTaskRequest` and `UpdateTaskRequest` form classes

### ➕ Expanded Requirements

1. **Soft Delete with Restore** ✅
   - Implemented SoftDeletes trait in Task model
   - Dedicated trash view with restore/permanent delete options
   - Soft-deleted records moved to trash instead of permanently deleted

2. **Search & Filter** ✅
   - Search by task title and description
   - Filter by status (Pending, In Progress, Done)
   - Filter by category
   - Works with pagination

3. **Database Relationships** ✅
   - Task `belongsTo` Category (Many-to-One)
   - Category `hasMany` Tasks
   - Eloquent relationship properly implemented

4. **Database Seeding** ✅
   - `CategorySeeder` - Creates 5 sample categories
   - `TaskSeeder` - Creates 15 sample tasks using Faker
   - Run with `php artisan db:seed`

---

## 📁 Project Structure (MVC Architecture)

```
CMSC129-Lab2-YoungFE/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── TaskController.php          # Business logic (CRUD, search, filter)
│   │   └── Requests/
│   │       ├── StoreTaskRequest.php        # Validation rules for creating tasks
│   │       └── UpdateTaskRequest.php       # Validation rules for updating tasks
│   ├── Models/
│   │   ├── Task.php                        # Task model (Eloquent ORM)
│   │   └── Category.php                    # Category model with relationships
│   └── Providers/
│       └── AppServiceProvider.php
│
├── database/
│   ├── migrations/
│   │   ├── 2026_03_30_124241_create_tasks_table.php         # Tasks table
│   │   └── 2026_03_30_125000_create_categories_table.php    # Categories table
│   ├── factories/
│   │   └── TaskFactory.php                 # Faker factory for tasks
│   └── seeders/
│       ├── DatabaseSeeder.php              # Main seeder
│       ├── CategorySeeder.php              # Seed categories
│       └── TaskSeeder.php                  # Seed tasks
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php               # Master layout (all pages extend this)
│   │   │   └── navbar.blade.php            # Navigation component
│   │   ├── tasks/
│   │   │   ├── index.blade.php             # List view with search/filter
│   │   │   ├── create.blade.php            # Create form
│   │   │   ├── edit.blade.php              # Edit form
│   │   │   ├── show.blade.php              # Detail view
│   │   │   └── trash.blade.php             # Soft-deleted tasks
│   │   └── components/
│   │       ├── alert.blade.php             # Alert/notification component
│   │       └── navbar.blade.php            # Navbar component
│   ├── css/
│   │   └── app.css                         # Tailwind CSS
│   └── js/
│       └── app.js
│
├── routes/
│   └── web.php                             # Route definitions (resource route)
│
├── .env.example                            # Environment variables template
├── .gitignore                              # Git ignore file
├── composer.json                           # PHP dependencies
├── package.json                            # Node.js dependencies
└── README.md                               # This file

```

### **MVC Architecture Explanation**

#### **Models** (`app/Models/`)
- **Task.php**: Represents a task record in the database
  - Attributes: id, title, description, due_date, status, priority, category_id, timestamps, deleted_at
  - Relationships: `belongsTo(Category)`
  - Scopes: `search()`, `byStatus()`, `byCategory()` for filtering

- **Category.php**: Represents a task category
  - Relationships: `hasMany(Task)`

#### **Views** (`resources/views/`)
- **layouts/app.blade.php**: Master layout template
  - Uses `@yield()` and `@section()` for content blocks
  - Includes navbar and flash messages
  - Consistent styling across all pages

- **tasks/index.blade.php**: Displays all tasks with search/filter form
- **tasks/create.blade.php**: Form to create new task
- **tasks/edit.blade.php**: Form to edit existing task
- **tasks/show.blade.php**: Display single task details
- **tasks/trash.blade.php**: Display soft-deleted tasks with restore option

#### **Controllers** (`app/Http/Controllers/`)
- **TaskController.php**: Handles all business logic
  - `index()`: List tasks with search/filter
  - `create()`: Show create form
  - `store()`: Save new task (uses StoreTaskRequest)
  - `show()`: Display task details
  - `edit()`: Show edit form
  - `update()`: Save task changes (uses UpdateTaskRequest)
  - `destroy()`: Soft-delete task
  - `trash()`: Show soft-deleted tasks
  - `restore()`: Restore soft-deleted task
  - `forceDelete()`: Permanently delete task

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2+
- PostgreSQL
- Composer
- Node.js & npm

### Step 1: Clone Repository
```bash
git clone https://github.com/YourgithubUsername/CMSC129-Lab2-YoungFE.git
cd CMSC129-Lab2-YoungFE
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Database Configuration
Edit `.env` file with your PostgreSQL credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cmsc129_lab2
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Step 5: Create PostgreSQL Database
```bash
# Using psql
psql -U postgres
CREATE DATABASE cmsc129_lab2;
\q
```

### Step 6: Run Migrations
```bash
# Create tables
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### Step 7: Build Assets
```bash
npm run build
# or for development with watch mode
npm run dev
```

### Step 8: Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Create Sample Data (Fresh)
```bash
php artisan migrate:fresh --seed
```

---

## 📚 Key Routes

| Method | Route | Action |
|--------|-------|--------|
| GET | `/tasks` | List all tasks |
| GET | `/tasks/create` | Show create form |
| POST | `/tasks` | Store new task |
| GET | `/tasks/{id}` | Show task details |
| GET | `/tasks/{id}/edit` | Show edit form |
| PUT | `/tasks/{id}` | Update task |
| DELETE | `/tasks/{id}` | Soft delete task |
| GET | `/tasks-trash/trash` | Show trashed tasks |
| GET | `/tasks-trash/{id}/restore` | Restore trashed task |
| DELETE | `/tasks-trash/{id}/force-delete` | Permanently delete |

---

## 🎓 Learning Outcomes

✅ Understanding **MVC architectural pattern**
✅ Working with **Laravel framework** (routing, controllers, models)
✅ Using **Eloquent ORM** for database operations
✅ Implementing **Blade templating** for views
✅ Form **validation** with custom Form Request classes
✅ **Database relationships** (hasMany, belongsTo)
✅ **Soft deletes** for data recovery
✅ **Search and filtering** functionality
✅ Proper **error handling** and user feedback

---

## 📝 Notes

- Application uses **PostgreSQL** as primary database
- All sensitive credentials stored in `.env` (not committed to GitHub)
- Follows **Laravel conventions** and best practices
- Implements **RESTful resource routing**
- Uses **Eloquent ORM** instead of raw SQL
- Proper **separation of concerns** (Models, Views, Controllers)

---

## 🤝 Contributing

This is a lab assignment. Modifications should be documented.

---

## 📄 License

MIT License - See LICENSE file for details.

---

## 👤 Author

Young FE - CMSC129 Lab 2

---

## 📞 Support

For issues or questions, refer to [Laravel Documentation](https://laravel.com/docs)

