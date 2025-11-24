# Wowsome Microsite

A Laravel 10 application with dual-theme architecture using Bootstrap 5 for frontend and Tabler for admin panel.

## Features

- **Authentication**: Laravel Breeze with Blade templates
- **Authorization**: Spatie Laravel Permission for role-based access control
- **Frontend**: Bootstrap 5.3.8 with custom Sass architecture
- **Admin Panel**: Tabler Core 1.4.0 for admin interface
- **Asset Management**: Organized public assets with helper functions
- **Mobile Optimized**: iOS Safari support with svh units and safe areas
- **Clean Sass Architecture**: Modular structure with separated concerns

## Tech Stack

### Backend
- Laravel 10.49.1
- PHP 8.1
- Laravel Breeze 1.29.1 (Blade)
- Spatie Laravel Permission 6.23.0

### Frontend
- Bootstrap 5.3.8 (frontend theme)
- Tabler Core 1.4.0 (admin theme)
- Sass 1.94.2 (modern Dart Sass compiler with `@use` syntax)
- Vite 5.4.21
- @popperjs/core 2.11.8

### Removed Dependencies
- ❌ Tailwind CSS (completely removed)
- ❌ Alpine.js (completely removed)
- ❌ PostCSS
- ❌ Autoprefixer

## Quick Start

See [SETUP.md](SETUP.md) for detailed setup instructions.

## Sass Architecture

See [SASS-ARCHITECTURE.md](SASS-ARCHITECTURE.md) for the complete Sass structure and guidelines.

### Sass File Structure

```
resources/sass/
├── app.scss                    # Frontend entry point
├── admin.scss                  # Admin entry point
├── frontend/
│   ├── _variables.scss         # Bootstrap variable overrides
│   ├── _mixins.scss            # Reusable mixins
│   ├── _brand.scss             # Framework overrides ONLY (empty template)
│   ├── _main.scss              # All custom styles and utilities
│   └── components/
│       └── _components.scss    # Component styles
└── admin/
    ├── _variables.scss         # Tabler variable overrides
    ├── _mixins.scss            # Admin mixins
    ├── _brand.scss             # Framework overrides ONLY (empty template)
    ├── _main.scss              # All admin custom styles
    └── components/
        └── _components.scss    # Admin component styles
```

**Important**: `_brand.scss` files are reserved for framework overrides only and should remain clean templates. All custom styles belong in `_main.scss`.

## Default Users

After running seeders:

- **Admin**: admin@admin.com / password
- **User**: user@user.com / password

## Routes

### Public Routes
- `/` - Public homepage
- `/login` - Login page
- `/register` - Registration page

### Authenticated Routes
- `/dashboard` - User dashboard (requires authentication)

### Admin Routes (requires admin role)
- `/admin/dashboard` - Admin dashboard

## Middleware

- `auth` - Laravel authentication middleware
- `admin` - Custom middleware for admin role checking
- `verified` - Email verification middleware

## Asset Helpers

Helper functions for accessing public assets:

```php
// Images
asset_image('logo.png') // /assets/images/logo.png

// Icons
asset_icon('favicon.ico') // /assets/icons/favicon.ico

// Fonts
asset_font('custom-font.woff2') // /assets/fonts/custom-font.woff2

// Documents
asset_document('manual.pdf') // /assets/documents/manual.pdf

// Sounds
asset_sound('notification.mp3') // /assets/sounds/notification.mp3
```

## Public Assets Structure

```
public/assets/
├── images/      # Image files (logo, banners, etc.)
├── icons/       # Icons and favicons
├── fonts/       # Custom web fonts
├── documents/   # Downloadable PDFs, documents
└── sounds/      # Audio files (notifications, etc.)
```

## iOS Safari Optimization

The application includes special optimizations for iOS Safari:

- **SVH Units**: Small Viewport Height units with fallback
- **Safe Areas**: CSS environment variables for notch/home indicator
- **Viewport Meta**: `viewport-fit=cover` for full-screen support
- **PWA Ready**: Apple mobile web app capable meta tags

## Development Commands

```bash
# Install dependencies
composer install
npm install

# Run migrations and seeders
php artisan migrate --seed

# Development
npm run dev
php artisan serve

# Production build
npm run build
```

## Vite Configuration

The project uses Vite with:
- Modern Sass compiler API
- Silenced deprecation warnings for legacy code
- Separate entry points for frontend and admin

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
│   │   ├── frontend/                 # Frontend Sass modules
│   │   └── admin/                    # Admin Sass modules
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
└── .gitignore                        # Git ignore rules
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

### In Blade Templates
```blade
@role('admin')
    <p>This is visible to administrators only</p>
@endrole
```

## License

This project is open-sourced software.
