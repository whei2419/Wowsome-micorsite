# Laravel 10 Microsite

This is a Laravel 10 application with authentication, role-based access control, and dual styling systems.

## Features

- **Laravel 10** - Latest stable version
- **Laravel Breeze** - Authentication scaffolding
- **Spatie Laravel Permission** - Role and permission management
- **Sass** - For custom styling
- **Tabler** - Admin panel UI framework
- **Bootstrap 5** - Frontend UI framework

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Wowsome-micorsite
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   Edit `.env` file and set your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Build assets**
   ```bash
   npm run build
   ```
   
   For development with hot reload:
   ```bash
   npm run dev
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## Default Users

After running the seeder, you'll have two default users:

### Admin User
- **Email**: admin@admin.com
- **Password**: password
- **Role**: admin
- **Access**: Admin Dashboard (Tabler styling)

### Regular User
- **Email**: user@user.com
- **Password**: password
- **Role**: user
- **Access**: User Dashboard (Bootstrap 5 styling)

## Routes

- `/` - Welcome page
- `/login` - Login page
- `/register` - Registration page
- `/dashboard` - User dashboard (requires authentication)
- `/admin/dashboard` - Admin dashboard (requires admin role)

## Styling

### Admin Panel (Tabler)
- Located in `resources/sass/admin.scss` and `resources/js/admin.js`
- Uses Tabler framework for a modern admin interface
- Applied to all routes under `/admin` prefix

### Frontend (Bootstrap 5)
- Located in `resources/sass/app.scss` and `resources/js/frontend.js`
- Uses Bootstrap 5 for clean, responsive design
- Applied to all public and user-facing pages

## Middleware

- `auth` - Requires authentication
- `admin` - Requires admin role
- `role:<role>` - Requires specific role
- `permission:<permission>` - Requires specific permission

## Creating New Admin Routes

Add routes in `routes/web.php` under the admin middleware group:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    // Add more admin routes here
});
```

## Managing Roles and Permissions

### Assign Role to User
```php
$user->assignRole('admin');
```

### Check if User has Role
```php
if ($user->hasRole('admin')) {
    // User is admin
}
```

### Assign Permission to User
```php
$user->givePermissionTo('manage users');
```

### Check if User has Permission
```php
if ($user->hasPermissionTo('manage users')) {
    // User can manage users
}
```

## Development

### Compile assets for development
```bash
npm run dev
```

### Compile assets for production
```bash
npm run build
```

### Run tests
```bash
php artisan test
```

## License

This project is open-sourced software licensed under the MIT license.
