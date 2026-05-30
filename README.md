# aresCMS — Laravel Application

A modern news portal built with Laravel featuring a dark Material Design theme.

## Features

- **User Authentication**: Login, registration, password reset, email verification
- **News System**: Create, edit, delete, and publish news articles
- **Comments**: Users can comment on news articles
- **Admin Panel**: Full admin dashboard for managing news and users
- **User Management**: Ban/unban users, promote to admin
- **Dark Material Design**: Beautiful dark theme with Material Design principles

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Node.js and npm (for Vite)

## Installation

**Webspace / Shared Hosting (Deutsch):** **[docs/INSTALLATION_WEBSPACE.md](docs/INSTALLATION_WEBSPACE.md)** — optional **Weboberfläche** `public/install.php` (nach Setup wieder löschen).

### Lokal (Entwicklung)

1. Clone the repository:
```bash
git clone <repository-url>
cd news-portal
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_portal
DB_USERNAME=root
DB_PASSWORD=
```

7. Run database migrations:
```bash
php artisan migrate
```

8. Seed the database with admin user:
```bash
php artisan db:seed --class=AdminSeeder
```

9. Create storage link:
```bash
php artisan storage:link
```

10. Build assets:
```bash
npm run build
```

11. Start the development server:
```bash
php artisan serve
```

## Default Admin Account

After running the seeder, you can login with:
- **Email**: admin@example.com
- **Password**: password

## Usage

### Public Features
- View published news articles
- Read full news articles
- View comments on articles
- Register and login to comment

### User Features
- Comment on news articles
- Delete own comments

### Admin Features
- Create, edit, and delete news articles
- Publish/unpublish news articles
- Upload images for news articles
- View all users
- Edit user details
- Ban/unban users
- Promote users to admin
- Demote admins to users

## Routes

### Public Routes
- `GET /` - Home page with news listing
- `GET /login` - Login page
- `POST /login` - Login action
- `GET /register` - Registration page
- `POST /register` - Registration action
- `GET /password/reset` - Password reset request
- `POST /password/email` - Send password reset email
- `GET /password/reset/{token}` - Password reset form
- `POST /password/reset` - Password reset action
- `GET /news/{news}` - View news article

### Authenticated Routes
- `POST /logout` - Logout
- `POST /news/{news}/comments` - Add comment
- `DELETE /news/{news}/comments/{comment}` - Delete comment

### Admin Routes
- `GET /admin` - Admin dashboard
- `GET /admin/news` - List all news
- `GET /admin/news/create` - Create news form
- `POST /admin/news` - Store news
- `GET /admin/news/{news}` - View news
- `GET /admin/news/{news}/edit` - Edit news form
- `PUT /admin/news/{news}` - Update news
- `DELETE /admin/news/{news}` - Delete news
- `GET /admin/users` - List all users
- `GET /admin/users/{user}` - View user
- `GET /admin/users/{user}/edit` - Edit user form
- `PUT /admin/users/{user}` - Update user
- `DELETE /admin/users/{user}` - Delete user
- `PATCH /admin/users/{user}/toggle-admin` - Toggle admin status
- `PATCH /admin/users/{user}/toggle-ban` - Toggle ban status

## Database Structure

### Users Table
- `id` - Primary key
- `name` - User's name
- `email` - User's email (unique)
- `email_verified_at` - Email verification timestamp
- `password` - Hashed password
- `is_admin` - Admin status (boolean)
- `is_banned` - Ban status (boolean)
- `remember_token` - Remember me token
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### News Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `title` - News title
- `content` - News content
- `image` - Image path (nullable)
- `published` - Published status (boolean)
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### Comments Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `news_id` - Foreign key to news table
- `content` - Comment content
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

## Operations (Backups & maintenance)

Multi-site **CMS updates** (manifest + ZIP from your server, `.env` / `config/` preserved): **[docs/UPDATES.md](docs/UPDATES.md)**.

See **[docs/BETRIEB.md](docs/BETRIEB.md)** (German) for:

- **Admin → Operations** (`/admin/operations`): run backups and toggle maintenance mode from the browser
- `php artisan backup:application` (DB + `storage/app/public` in a ZIP)
- Cron / Windows Task Scheduler for `php artisan schedule:run`
- Maintenance mode: `php artisan down` / `php artisan up` (custom page: `resources/views/errors/maintenance.blade.php`, rendered on each request)

## License

This project is open-sourced software licensed under the MIT license.
