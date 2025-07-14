# Task Management System

A full-stack task management system built with Laravel 10+ and Vue.js 3, featuring user management, task assignment, and email notifications.

## Features

### 🧑 Admin Features

-   Create, edit, and delete users
-   Assign tasks to users with title, description, status, and deadline
-   Automatic email notifications when tasks are assigned
-   View all tasks and users

### 👤 User Features

-   Login and view assigned tasks
-   Update task status (Pending, In Progress, Completed)
-   See only their own tasks

### 📫 Email Notifications

-   Automatic email notifications when tasks are assigned
-   Uses Laravel's mailing system
-   Configurable SMTP settings

## Tech Stack

-   **Backend**: Laravel 10+ with API support
-   **Frontend**: Vue.js 3 with Vite
-   **Styling**: Tailwind CSS
-   **Authentication**: Laravel Breeze with Sanctum
-   **Database**: PostgreSQL
-   **Email**: Laravel Mail with SMTP support

## Prerequisites

-   PHP 8.1+
-   Composer
-   Node.js 16+
-   PostgreSQL
-   SMTP server (for email notifications)

## Installation

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd task-manager
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node.js dependencies**

    ```bash
    npm install
    ```

4. **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Configure database**
   Update your `.env` file with PostgreSQL credentials:

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=task_manager
    DB_USERNAME=postgres
    DB_PASSWORD=your_password
    ```

6. **Configure mail settings**
   Update your `.env` file with SMTP credentials:

    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=your_smtp_host
    MAIL_PORT=587
    MAIL_USERNAME=your_username
    MAIL_PASSWORD=your_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="noreply@example.com"
    MAIL_FROM_NAME="${APP_NAME}"
    ```

7. **Run migrations and seeders**

    ```bash
    php artisan migrate
    php artisan db:seed --class=AdminUserSeeder
    ```

8. **Build frontend assets**
    ```bash
    npm run build
    ```

## Running the Application

1. **Start the Laravel development server**

    ```bash
    php artisan serve
    ```

2. **Start the Vite development server (for development)**

    ```bash
    npm run dev
    ```

3. **Access the application**
    - Open your browser and go to `http://localhost:8000`
    - Login with the default admin account:
        - Email: `admin@example.com`
        - Password: `password`

## Default Users

The seeder creates the following users:

-   **Admin User**

    -   Email: `admin@example.com`
    -   Password: `password`
    -   Role: Admin

-   **Regular Users**
    -   Email: `john@example.com`, `jane@example.com`, `bob@example.com`
    -   Password: `password`
    -   Role: User

## API Endpoints

### Authentication

-   `POST /login` - User login
-   `POST /logout` - User logout
-   `GET /api/user` - Get current user

### Users (Admin only)

-   `GET /api/users` - List all users
-   `POST /api/users` - Create user
-   `GET /api/users/{id}` - Get user
-   `PUT /api/users/{id}` - Update user
-   `DELETE /api/users/{id}` - Delete user

### Tasks

-   `GET /api/tasks` - List tasks (all for admin, own for users)
-   `GET /api/tasks/my` - Get user's own tasks
-   `POST /api/tasks` - Create task (admin only)
-   `GET /api/tasks/{id}` - Get task
-   `PUT /api/tasks/{id}` - Update task
-   `DELETE /api/tasks/{id}` - Delete task (admin only)

## Development

### Frontend Development

```bash
npm run dev
```

### Backend Development

```bash
php artisan serve
```

### Database Migrations

```bash
php artisan migrate
php artisan migrate:rollback
```

### Database Seeders

```bash
php artisan db:seed --class=AdminUserSeeder
```

## Production Deployment

1. **Set environment to production**

    ```env
    APP_ENV=production
    APP_DEBUG=false
    ```

2. **Optimize for production**

    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    npm run build
    ```

3. **Set up queue worker for email notifications**
    ```bash
    php artisan queue:work
    ```

## Database Schema

### Users Table

-   `id` - Primary key
-   `name` - User's full name
-   `email` - Unique email address
-   `password` - Hashed password
-   `is_admin` - Boolean admin flag
-   `email_verified_at` - Email verification timestamp
-   `remember_token` - Remember me token
-   `created_at`, `updated_at` - Timestamps

### Tasks Table

-   `id` - Primary key
-   `title` - Task title
-   `description` - Task description
-   `status` - Enum: pending, in_progress, completed
-   `deadline` - Task deadline (nullable)
-   `user_id` - Foreign key to users table
-   `created_at`, `updated_at` - Timestamps

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request
