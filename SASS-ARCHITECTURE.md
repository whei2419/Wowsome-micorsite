# Sass Architecture Documentation

This project uses a modular Sass architecture with separate styling for frontend (Bootstrap 5) and admin (Tabler) interfaces.

## Directory Structure

```
resources/sass/
├── app.scss                          # Frontend entry point
├── admin.scss                        # Admin entry point
├── frontend/
│   ├── _variables.scss               # Bootstrap variable overrides
│   ├── _mixins.scss                  # Reusable frontend mixins
│   ├── _brand.scss                   # Brand-specific overrides
│   └── components/
│       └── _components.scss          # Frontend component styles
└── admin/
    ├── _variables.scss               # Tabler variable overrides
    ├── _mixins.scss                  # Reusable admin mixins
    ├── _brand.scss                   # Admin brand-specific overrides
    └── components/
        └── _components.scss          # Admin component styles
```

## Import Order

### Frontend (app.scss)
1. **Variables** - Override Bootstrap defaults
2. **Mixins** - Reusable Sass functions
3. **Bootstrap Core** - Import Bootstrap framework
4. **Components** - Custom component styles
5. **Brand** - Final brand-specific overrides

### Admin (admin.scss)
1. **Variables** - Override Tabler defaults
2. **Mixins** - Reusable Sass functions
3. **Tabler Core** - Import Tabler framework
4. **Components** - Custom admin component styles
5. **Brand** - Final admin brand-specific overrides

## Usage Examples

### Using Frontend Variables
Edit `frontend/_variables.scss` to customize Bootstrap colors:
```scss
$primary: #0d6efd;
$secondary: #6c757d;
```

### Using Frontend Mixins
In `frontend/components/_components.scss`:
```scss
@use '../mixins' as *;

.my-button {
    @include button-variant(#3490dc);
}
```

### Using Admin Mixins
In `admin/components/_components.scss`:
```scss
@use '../mixins' as *;

.my-admin-card {
    @include admin-card;
}
```

### Creating New Components

#### Frontend Component
Create in `frontend/components/_components.scss`:
```scss
.my-custom-component {
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
}
```

#### Admin Component
Create in `admin/components/_components.scss`:
```scss
.my-admin-widget {
    @include admin-card;
    padding: 1.5rem;
}
```

## Available Mixins

### Frontend Mixins
- `button-variant($bg, $color)` - Create custom button variants
- `card-shadow` - Apply card shadow
- `responsive-spacing($property, $mobile, $desktop)` - Responsive spacing

### Admin Mixins
- `admin-card` - Apply admin card styling
- `status-badge($bg-color)` - Create status badges
- `table-hover-row` - Add hover effect to table rows
- `truncate-text` - Truncate text with ellipsis

## Brand Customization

### Frontend Brand Styles
Edit `frontend/_brand.scss` for brand-specific overrides:
- Hero sections
- Feature boxes
- Custom utilities
- Footer styles

### Admin Brand Styles
Edit `admin/_brand.scss` for admin-specific customization:
- Dashboard widgets
- Custom scrollbars
- Page wrapper styles
- Card hover effects

## Development Workflow

1. **Edit Variables** - Start with `_variables.scss` files
2. **Add Mixins** - Create reusable patterns in `_mixins.scss`
3. **Build Components** - Add component styles in `components/_components.scss`
4. **Apply Brand** - Final touches in `_brand.scss`
5. **Compile** - Run `npm run dev` or `npm run build`

## Best Practices

1. ✅ Always use variables for colors and spacing
2. ✅ Create mixins for repeated patterns
3. ✅ Keep components modular and reusable
4. ✅ Use the modern `@use` syntax instead of `@import`
5. ✅ Put brand-specific overrides in `_brand.scss` files
6. ✅ Namespace mixins when importing: `@use '../mixins' as *;`

## Compilation

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

## Notes

- The architecture uses Dart Sass modern compiler
- Bootstrap 5 variables can be overridden in `frontend/_variables.scss`
- Tabler variables can be overridden in `admin/_variables.scss`
- All deprecation warnings are silenced in `vite.config.js`
