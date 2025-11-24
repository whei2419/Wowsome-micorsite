# Setup Guide

## Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js (v18 or higher recommended)
- MySQL or PostgreSQL database
- Git (optional)

## Installation Steps

### 1. Clone or Download the Repository

```bash
git clone <repository-url>
cd Wowsome-micorsite
```

### 2. Install PHP Dependencies

```bash
composer install
```

This will install:
- Laravel 10.49.1
- Laravel Breeze 1.29.1
- Spatie Laravel Permission 6.23.0
- All required PHP packages

### 3. Install Node Dependencies

```bash
npm install
```

This will install:
- Vite 5.4.21
- Bootstrap 5.3.8
- Tabler Core 1.4.0
- Sass 1.94.2
- @popperjs/core 2.11.8
- All required build tools

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

APP_NAME="Wowsome Microsite"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 5. Database Setup

Create the database (if not exists):

```bash
mysql -u root -p
CREATE DATABASE your_database_name;
exit;
```

### 6. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will create:
- All Laravel default tables (users, migrations, etc.)
- Spatie permission tables (roles, permissions, model_has_roles, etc.)
- Admin and User roles
- Default admin user (admin@admin.com / password)
- Default regular user (user@user.com / password)

### 7. Build Assets

**Development (with hot reload):**
```bash
npm run dev
```

**Production (optimized build):**
```bash
npm run build
```

This compiles:
- `resources/sass/app.scss` → `public/build/assets/app-[hash].css`
- `resources/sass/admin.scss` → `public/build/assets/admin-[hash].css`
- `resources/js/app.js` → `public/build/assets/app-[hash].js`
- `resources/js/frontend.js` → `public/build/assets/frontend-[hash].js`
- `resources/js/admin.js` → `public/build/assets/admin-[hash].js`

### 8. Start Development Server

```bash
php artisan serve
```

Visit: http://localhost:8000

## Testing the Application

### Login as Admin
1. Go to http://localhost:8000/login
2. Email: `admin@admin.com`
3. Password: `password`
4. You'll be redirected to `/admin/dashboard` (Tabler theme)

### Login as User
1. Go to http://localhost:8000/login
2. Email: `user@user.com`
3. Password: `password`
4. You'll be redirected to `/dashboard` (Bootstrap theme)

### Register New User
1. Go to http://localhost:8000/register
2. Fill in the form
3. New users get the "user" role by default
4. Redirected to `/dashboard`

## Default Roles and Permissions

### Roles Created by Seeder
- **admin**: Full access to admin panel (/admin/*)
- **user**: Access to user dashboard (/dashboard)

### Middleware Protection
- `/dashboard` → requires `auth` middleware
- `/admin/dashboard` → requires `auth` + `admin` middleware (checks for admin role)

## File Structure Overview

```
Wowsome-micorsite/
├── app/
│   ├── Helpers/
│   │   └── AssetHelper.php          # Asset helper functions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── DashboardController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php   # Admin role checking
│   └── Models/
│       └── User.php                  # HasRoles trait added
├── database/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RoleSeeder.php            # Creates roles and users
├── public/
│   └── assets/
│       ├── images/
│       ├── icons/
│       ├── fonts/
│       ├── documents/
│       └── sounds/
├── resources/
│   ├── js/
│   │   ├── app.js                    # Core JS
│   │   ├── frontend.js               # Bootstrap JS
│   │   └── admin.js                  # Tabler JS
│   ├── sass/
│   │   ├── app.scss                  # Frontend entry
│   │   ├── admin.scss                # Admin entry
│   │   ├── frontend/
│   │   │   ├── _variables.scss       # Bootstrap overrides
│   │   │   ├── _mixins.scss          # Reusable mixins
│   │   │   ├── _brand.scss           # Framework overrides (empty)
│   │   │   ├── _main.scss            # All custom styles
│   │   │   └── components/
│   │   └── admin/
│   │       ├── _variables.scss       # Tabler overrides
│   │       ├── _mixins.scss          # Admin mixins
│   │       ├── _brand.scss           # Framework overrides (empty)
│   │       ├── _main.scss            # All admin styles
│   │       └── components/
│   └── views/
│       ├── layouts/
│       │   ├── admin.blade.php       # Tabler layout
│       │   ├── frontend.blade.php    # Bootstrap layout
│       │   ├── app.blade.php         # Updated for Bootstrap
│       │   ├── guest.blade.php       # Login/register layout
│       │   └── navigation.blade.php  # Bootstrap navbar
│       ├── admin/
│       │   └── dashboard.blade.php
│       └── dashboard.blade.php
├── routes/
│   └── web.php                       # All routes defined
├── vite.config.js                    # Vite + Sass config
├── composer.json                     # PHP dependencies
├── package.json                      # Node dependencies
└── .gitignore                        # Git ignore rules
```

## Troubleshooting

### Sass Compilation Errors

If you see deprecation warnings, verify your `vite.config.js` has:

```javascript
css: {
    preprocessorOptions: {
        scss: {
            api: 'modern-compiler',
            silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions'],
        },
    },
}
```

### Permission Issues (Linux/Mac)

Make sure storage and cache directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues

- Verify database credentials in `.env`
- Ensure database server is running
- Check if database exists: `SHOW DATABASES;`
- Test connection: `php artisan migrate:status`

### Asset Not Found (404)

If CSS/JS files return 404:

1. Run `npm run build` to generate assets
2. Check `public/build` directory exists
3. Verify `@vite` directives in blade templates
4. Clear cache: `php artisan cache:clear`

### Middleware Not Working

If admin middleware doesn't redirect:

1. Check `app/Http/Kernel.php` has middleware alias
2. Verify user has admin role: `User::find(1)->roles`
3. Clear config cache: `php artisan config:clear`

### Composer Autoload Issues

If helper functions not found:

```bash
composer dump-autoload
```

## Next Steps

### Customization

1. **Frontend Theme**:
   - Update Bootstrap variables: `resources/sass/frontend/_variables.scss`
   - Add custom styles: `resources/sass/frontend/_main.scss`
   - Framework overrides: `resources/sass/frontend/_brand.scss` (keep clean)
   - Components: `resources/sass/frontend/components/_components.scss`

2. **Admin Theme**:
   - Update Tabler variables: `resources/sass/admin/_variables.scss`
   - Add custom styles: `resources/sass/admin/_main.scss`
   - Framework overrides: `resources/sass/admin/_brand.scss` (keep clean)
   - Components: `resources/sass/admin/components/_components.scss`

3. **Layouts**:
   - Frontend: `resources/views/layouts/frontend.blade.php`
   - Admin: `resources/views/layouts/admin.blade.php`
   - Navigation: `resources/views/layouts/navigation.blade.php`

4. **Assets**:
   - Add images to `public/assets/images/`
   - Add icons to `public/assets/icons/`
   - Use helpers: `asset_image('logo.png')`

### Production Deployment

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache

# Update .env
APP_ENV=production
APP_DEBUG=false
```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs/10.x)
- [Laravel Breeze](https://laravel.com/docs/10.x/starter-kits#breeze)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v6)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)
- [Tabler](https://tabler.io/)
- [SASS-ARCHITECTURE.md](SASS-ARCHITECTURE.md) - Sass guidelines
