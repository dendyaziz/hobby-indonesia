# Hobby Indonesia - Admin Panel

## About The App

---

This application designed to allow administrators to seamlessly maintain products and marketing assets showcased on the Hobby Indonesia website. 


## Technology Stack

---

- **Framework**: [Laravel 13](https://laravel.com) (PHP 8.4+)
- **Administrative Panel**: [Filament PHP v5](https://filamentphp.com) (The TALL Stack: Livewire, Alpine.js, Tailwind CSS)
- **Database**: MySQL 9+
- **Testing Suite**: [Pest PHP](https://pestphp.com)
- **AI-Assisted Development**: [Laravel Boost](https://laravel.com/docs/13.x/ai) (configured with custom workspace-specific skills under `.agents/skills/`)

---

## Local Development

Follow these steps to get the administrative panel running on your local machine:

### 1. Clone Project
Clone from the remote repository:
```bash
git clone git@github.com:upbanx/hobby-indonesia.git
```

### 2. Configure Environment
Copy the example environment file:
```bash
cp .env.example .env
```
Copy the `.env.example` into `.env` and configure your local MySQL database connection. For instance:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hobby_indonesia
DB_USERNAME=hobby
DB_PASSWORD=password
```

### 3. Install Dependencies
Install PHP packages via Composer and Node assets via NPM:
```bash
composer install
npm install
```

### 4. Database Setup & Migrations
Run the migrations to create the base table structure:
```bash
php artisan migrate
```

### 5. Start Development Server
Build front-end assets dynamically and start the Laravel server:
```bash
npm run dev
```
In another terminal, serve the application:
```bash
php artisan serve
```
The administrative panel will be available at [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin).

### 6. Verify with Tests
Verify that your local environment is correctly set up by running the test suite:
```bash
php artisan test
```

---

## Register a New User

To access the `/admin` dashboard, you need to register an administrative user. You can do this quickly using the following Artisan command:

```bash
php artisan make:filament-user
```

You will be prompted to enter the user's name, email address, and password.

### Non-Interactive Creation (Automation / Seeders)
Alternatively, you can run the command non-interactively by passing options:
```bash
php artisan make:filament-user --name="Admin" --email="admin@hobbyindonesia.com" --password="password" --no-interaction
```

---

## Production Deployment

When deploying Hobby Indonesia to production, follow the industry-standard best practices for Laravel and Filament:

### 1. Optimize Configuration & Routes
Cache the settings to avoid reading files on every HTTP request:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

### 2. Production Database Migrations
Always run migrations with the `--force` flag during deployment scripts:
```bash
php artisan migrate --force
```

### 3. Compile Production Assets
Build the optimized static front-end assets:
```bash
npm run build
```

### 4. Manage Background Tasks
Ensure a persistent queue worker runs to handle jobs (such as email dispatching, report exporting, etc.):
```bash
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
```

### 5. Secure Session Cookies
Ensure HTTPS is active and configure session secure settings in `.env`:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
```
