# Quick Setup Guide

Follow these steps to get your Laravel application up and running:

## Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL/MariaDB (XAMPP already includes this)

## Database Setup

1. **Start XAMPP** (Apache & MySQL)

2. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create a new database (e.g., `wowsome_microsite`)

3. **Update .env file**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=wowsome_microsite
   DB_USERNAME=root
   DB_PASSWORD=
   ```

## Run Migrations & Seeders

```bash
cd C:\xampp8.1\htdocs\Wowsome-micorsite
php artisan migrate --seed
```

This will:
- Create all necessary database tables
- Create admin and user roles
- Create permissions
- Create two test users

## Start Development Server

Option 1 - Laravel Built-in Server:
```bash
php artisan serve
```
Access at: http://localhost:8000

Option 2 - XAMPP (already configured):
Access at: http://localhost/Wowsome-micorsite/public

## Development Assets

For development with hot reload:
```bash
npm run dev
```

Keep this running while developing.

## Login Credentials

**Admin User:**
- Email: admin@admin.com
- Password: password
- Dashboard: http://localhost:8000/admin/dashboard

**Regular User:**
- Email: user@user.com
- Password: password
- Dashboard: http://localhost:8000/dashboard

## Project Structure

```
Wowsome-micorsite/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── DashboardController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       └── User.php (with HasRoles trait)
├── resources/
│   ├── js/
│   │   ├── admin.js (Tabler)
│   │   └── frontend.js (Bootstrap 5)
│   ├── sass/
│   │   ├── admin.scss (Tabler styles)
│   │   └── app.scss (Bootstrap 5 styles)
│   └── views/
│       ├── admin/
│       │   └── dashboard.blade.php
│       └── layouts/
│           ├── admin.blade.php (Tabler layout)
│           └── frontend.blade.php (Bootstrap 5 layout)
├── routes/
│   └── web.php
└── database/
    └── seeders/
        ├── RoleSeeder.php
        └── DatabaseSeeder.php
```

## Next Steps

1. Customize the admin panel in `resources/views/admin/`
2. Add more admin routes in `routes/web.php`
3. Create additional controllers for your features
4. Customize styles in `resources/sass/admin.scss` and `resources/sass/app.scss`

## Troubleshooting

**Issue: Assets not loading**
Solution: Run `npm run build` or `npm run dev`

**Issue: Database connection error**
Solution: Verify MySQL is running in XAMPP and .env credentials are correct

**Issue: Permission denied errors**
Solution: Run `php artisan cache:clear` and `php artisan config:clear`

**Issue: Admin middleware error**
Solution: Make sure you've run migrations and seeders to create roles
