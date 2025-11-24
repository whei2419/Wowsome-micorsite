# Sass Architecture Documentation

This project uses a modular Sass architecture with separated concerns, modern `@use` syntax, and separate entry points for frontend (Bootstrap 5) and admin (Tabler) themes.

## File Structure

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
    ├── _mixins.scss            # Admin-specific mixins
    ├── _brand.scss             # Framework overrides ONLY (empty template)
    ├── _main.scss              # All admin custom styles
    └── components/
        └── _components.scss    # Admin component styles
```

## Entry Points

### Frontend Entry (`app.scss`)

```scss
// 1. Variables (override Bootstrap defaults BEFORE importing framework)
@use 'frontend/variables' as *;

// 2. Mixins (define reusable functions)
@use 'frontend/mixins' as *;

// 3. Bootstrap framework
@import "bootstrap/scss/bootstrap";

// 4. Components (custom component styles)
@use 'frontend/components/components';

// 5. Brand (framework overrides - keep clean)
@use 'frontend/brand';

// 6. Main (all custom styles and utilities)
@use 'frontend/main';
```

### Admin Entry (`admin.scss`)

```scss
// 1. Variables (override Tabler defaults BEFORE importing framework)
@use 'admin/variables' as *;

// 2. Mixins (define reusable functions)
@use 'admin/mixins' as *;

// 3. Tabler framework
@import "@tabler/core/src/scss/tabler";

// 4. Components (custom component styles)
@use 'admin/components/components';

// 5. Brand (framework overrides - keep clean)
@use 'admin/brand';

// 6. Main (all admin custom styles)
@use 'admin/main';
```

## Import Order (CRITICAL)

**Always maintain this exact order:**

1. **Variables** - Override framework defaults BEFORE importing the framework
2. **Mixins** - Define reusable functions before use
3. **Framework** - Import Bootstrap or Tabler
4. **Components** - Custom component styles
5. **Brand** - Framework overrides ONLY (should remain clean)
6. **Main** - All custom styles, utilities, and application-specific code

## File Responsibilities

### `_variables.scss`
**Purpose**: Override framework variables BEFORE framework import

```scss
// Frontend example
$primary: #007bff;
$secondary: #6c757d;
$font-family-base: 'Inter', sans-serif;
$border-radius: 0.5rem;
```

### `_mixins.scss`
**Purpose**: Reusable Sass mixins and functions

```scss
@mixin respond-to($breakpoint) {
  @if $breakpoint == 'mobile' {
    @media (max-width: 768px) { @content; }
  }
}

@mixin full-viewport {
  min-height: 100vh;
  min-height: 100svh;
}
```

### `_brand.scss` ⚠️ IMPORTANT
**Purpose**: Framework-specific overrides ONLY (should remain empty/clean)

**Rules**:
- Should contain ONLY overrides to Bootstrap/Tabler framework classes
- Should NOT contain custom utilities, layouts, or application styles
- Keep this file as clean as possible (empty template)
- All custom styles belong in `_main.scss`

```scss
// ✅ Good - Framework override
.navbar {
  // Override Bootstrap's navbar
}

.btn-primary {
  // Override Bootstrap's button
}

// ❌ Bad - Custom application code (belongs in _main.scss)
.hero-section {
  padding: 4rem 0;
}

.custom-card {
  border-radius: 1rem;
}
```

### `_main.scss` ✅ PRIMARY FILE
**Purpose**: All custom styles, utilities, and application-specific code

**Contains**:
- Custom typography styles
- Viewport utilities
- Safe area handling (iOS)
- Brand-specific utilities
- Layout styles (hero, features, footer, etc.)
- Application-specific overrides
- All custom CSS that isn't a component

```scss
// Typography
h1, h2, h3, h4, h5, h6 {
  font-weight: 600;
}

// Viewport utilities
.full-viewport {
  @include full-viewport;
}

// iOS Safe areas
.safe-top {
  padding-top: env(safe-area-inset-top);
}

// Custom sections
.hero-section {
  background: linear-gradient(135deg, $primary, $secondary);
  padding: 4rem 0;
}

// Feature boxes
.feature-box {
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

### `components/_components.scss`
**Purpose**: Import all component-specific styles

```scss
@use 'buttons';
@use 'cards';
@use 'forms';
@use 'modals';
```

## Modern Sass Syntax

This project uses **modern `@use` syntax** (not deprecated `@import`):

### Old vs New

```scss
// ❌ Old way (deprecated)
@import 'variables';
@import 'mixins';

// ✅ New way (modern)
@use 'variables' as *;
@use 'mixins' as *;
```

### Benefits of `@use`
- Better namespacing and encapsulation
- Clearer dependencies
- Better performance (files loaded once)
- Forward compatibility
- No global namespace pollution

### Exception: Framework Imports

Framework imports still use `@import` because they're not modules:

```scss
@import "bootstrap/scss/bootstrap";  // ✅ Correct
@import "@tabler/core/src/scss/tabler";  // ✅ Correct
```

## Variables

### Frontend Variables (`frontend/_variables.scss`)

Override Bootstrap variables BEFORE framework import:

```scss
// Brand Colors
$primary: #007bff;
$secondary: #6c757d;
$success: #28a745;
$danger: #dc3545;

// Typography
$font-family-base: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
$font-size-base: 1rem;
$line-height-base: 1.6;
$headings-font-weight: 600;

// Spacing
$spacer: 1rem;

// Border
$border-radius: 0.5rem;
$border-radius-lg: 1rem;

// Breakpoints (if overriding)
$grid-breakpoints: (
  xs: 0,
  sm: 576px,
  md: 768px,
  lg: 992px,
  xl: 1200px,
  xxl: 1400px
);
```

### Admin Variables (`admin/_variables.scss`)

Override Tabler variables:

```scss
// Theme Colors
$primary: #206bc4;
$secondary: #6c757d;

// Layout
$sidebar-width: 15rem;
$navbar-height: 3.5rem;

// Typography
$font-family-sans-serif: 'Inter', sans-serif;
$font-size-base: 0.875rem;

// Card
$card-border-radius: 0.5rem;
$card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
```

## Mixins

Create reusable mixins in `_mixins.scss` files:

### Frontend Mixins (`frontend/_mixins.scss`)

```scss
// Responsive breakpoint mixin
@mixin respond-to($breakpoint) {
  @if $breakpoint == 'mobile' {
    @media (max-width: 768px) { @content; }
  } @else if $breakpoint == 'tablet' {
    @media (min-width: 769px) and (max-width: 1024px) { @content; }
  } @else if $breakpoint == 'desktop' {
    @media (min-width: 1025px) { @content; }
  }
}

// Full viewport height with iOS support
@mixin full-viewport {
  min-height: 100vh;
  min-height: 100svh;
}

// Card shadow
@mixin card-shadow {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

// Usage
.element {
  @include respond-to('mobile') {
    font-size: 14px;
  }
  @include full-viewport;
  @include card-shadow;
}
```

### Admin Mixins (`admin/_mixins.scss`)

```scss
// Admin-specific mixins
@mixin admin-card {
  background: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

@mixin dashboard-widget {
  @include admin-card;
  min-height: 200px;
}
```

## Components

Organize component styles in the `components/` directory:

### Component Index (`components/_components.scss`)

```scss
// Import all component partials
@use 'buttons';
@use 'cards';
@use 'forms';
@use 'modals';
@use 'navigation';
```

### Individual Component (`components/_buttons.scss`)

```scss
.btn-custom {
  background: linear-gradient(135deg, $primary, $secondary);
  border: none;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 2rem;
  transition: transform 0.2s;

  &:hover {
    transform: translateY(-2px);
  }
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}
```

## iOS Safari Support

The project includes comprehensive iOS Safari viewport handling:

### SVH Units (Small Viewport Height)

```scss
// Always provide fallback for older browsers
.full-height {
  min-height: 100vh;  // Fallback
  min-height: 100svh; // iOS Safari - accounts for browser UI
}

.section {
  height: 100vh;  // Fallback
  height: 100svh; // iOS Safari
}
```

### Safe Area Insets

```scss
// Handle iPhone notch and home indicator
.navbar {
  padding-top: env(safe-area-inset-top);
  padding-left: env(safe-area-inset-left);
  padding-right: env(safe-area-inset-right);
}

.footer {
  padding-bottom: env(safe-area-inset-bottom);
}

// Utility classes (in _main.scss)
.safe-top { padding-top: env(safe-area-inset-top); }
.safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
.safe-left { padding-left: env(safe-area-inset-left); }
.safe-right { padding-right: env(safe-area-inset-right); }
```

### Meta Tags (in Blade layouts)

```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
```

## Best Practices

### 1. Always Use Variables

```scss
// ❌ Bad - Hard-coded values
.button {
  color: #007bff;
  font-family: 'Arial';
  padding: 12px 24px;
}

// ✅ Good - Using variables
.button {
  color: $primary;
  font-family: $font-family-base;
  padding: $btn-padding-y $btn-padding-x;
}
```

### 2. Avoid Deep Nesting

```scss
// ❌ Bad - Too deep (> 3 levels)
.header {
  .nav {
    .item {
      .link {
        .icon {
          color: blue;
        }
      }
    }
  }
}

// ✅ Good - Flat structure with BEM
.header-nav-link-icon {
  color: $primary;
}

// ✅ Also good - Moderate nesting
.header-nav {
  .link {
    color: $primary;
    
    .icon {
      margin-right: 0.5rem;
    }
  }
}
```

### 3. Use Mixins for Reusability

```scss
// Define once in _mixins.scss
@mixin card-shadow {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

// Use everywhere
.card { @include card-shadow; }
.modal { @include card-shadow; }
.dropdown { @include card-shadow; }
```

### 4. Mobile-First Approach

```scss
// ✅ Mobile first (recommended)
.element {
  font-size: 14px;  // Mobile (default)
  padding: 1rem;
  
  @media (min-width: 768px) {
    font-size: 16px;  // Tablet+
    padding: 2rem;
  }
  
  @media (min-width: 1200px) {
    font-size: 18px;  // Desktop+
    padding: 3rem;
  }
}

// ❌ Desktop first (avoid)
.element {
  font-size: 18px;
  
  @media (max-width: 1199px) {
    font-size: 16px;
  }
  
  @media (max-width: 767px) {
    font-size: 14px;
  }
}
```

### 5. Keep _brand.scss Clean

```scss
// frontend/_brand.scss should be EMPTY or minimal

// ✅ Good - Framework override only
.navbar-brand {
  font-size: 1.5rem; // Override Bootstrap's navbar-brand
}

// ❌ Bad - Custom application code (move to _main.scss)
.hero-section {
  padding: 4rem 0;
  background: linear-gradient(...);
}
```

### 6. Put Custom Styles in _main.scss

```scss
// frontend/_main.scss contains ALL custom application styles

// Typography
h1, h2, h3 { font-weight: 600; }

// Layouts
.hero-section { /* ... */ }
.feature-grid { /* ... */ }

// Utilities
.full-viewport { @include full-viewport; }
.safe-top { padding-top: env(safe-area-inset-top); }

// Application-specific
.brand-gradient { background: linear-gradient(...); }
```

## Vite Configuration

The `vite.config.js` includes modern Sass compiler settings:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/admin.scss',
                'resources/js/app.js',
                'resources/js/frontend.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',  // Use modern Dart Sass API
                silenceDeprecations: [
                    'legacy-js-api',
                    'import',
                    'global-builtin',
                    'color-functions'
                ],
            },
        },
    },
});
```

## Compilation

### Development Mode

```bash
npm run dev
```

**Features**:
- Hot Module Replacement (HMR)
- Source maps for debugging
- Fast compilation
- Watch mode (auto-recompile on changes)

### Production Build

```bash
npm run build
```

**Features**:
- CSS minification
- Dead code elimination
- Cache busting (hash in filename)
- Optimized bundle size

**Output**:
- `public/build/assets/app-[hash].css` (~228 KB)
- `public/build/assets/admin-[hash].css` (~536 KB)
- `public/build/assets/*.js`

## Debugging

### Check Compiled CSS

After building:

```bash
ls -lh public/build/assets/
```

Look for:
- `app-[hash].css` - Frontend styles
- `admin-[hash].css` - Admin styles

### Source Maps

In development mode, browser DevTools will show:
- Original Sass file names
- Line numbers from source files
- Not compiled CSS

### Common Issues

**Issue**: "Variable $primary is undefined"
**Solution**: Variables must be defined BEFORE using them or importing framework

**Issue**: "Deprecation warnings"
**Solution**: Check `vite.config.js` has `api: 'modern-compiler'` and `silenceDeprecations`

**Issue**: "Styles not applying"
**Solution**: 
1. Run `npm run build`
2. Clear browser cache
3. Check import order in entry files
4. Verify file paths

## Adding New Styles

### Adding a Component

1. Create component file:
```scss
// resources/sass/frontend/components/_hero.scss
.hero {
  padding: 4rem 0;
  background: $primary;
  color: white;
  
  &-title {
    font-size: 3rem;
    font-weight: 700;
  }
  
  &-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
  }
}
```

2. Import in components index:
```scss
// resources/sass/frontend/components/_components.scss
@use 'hero';
```

### Adding Custom Styles

Add to `_main.scss` (NOT `_brand.scss`):

```scss
// resources/sass/frontend/_main.scss

// Custom section
.custom-section {
  padding: 4rem 0;
  background: linear-gradient(135deg, $primary, $secondary);
}
```

### Overriding Framework Styles

Only if necessary, add to `_brand.scss`:

```scss
// resources/sass/frontend/_brand.scss

// Override Bootstrap's navbar (framework override)
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
```

### Adding Variables

Add to `_variables.scss` BEFORE framework import:

```scss
// resources/sass/frontend/_variables.scss
$custom-color: #ff6b6b;
$heading-font: 'Montserrat', sans-serif;
```

## File Size Guidelines

- **Variables**: Small (~50-100 lines)
- **Mixins**: Medium (~100-200 lines)
- **Brand**: Minimal (keep empty if possible)
- **Main**: Large (all custom styles)
- **Components**: Small to medium per file (~50-300 lines)

## Resources

- [Sass Documentation](https://sass-lang.com/documentation/)
- [Sass @use and @forward](https://sass-lang.com/documentation/at-rules/use)
- [Bootstrap Sass Customization](https://getbootstrap.com/docs/5.3/customize/sass/)
- [Tabler Customization](https://tabler.io/docs/)
- [Vite CSS Features](https://vitejs.dev/guide/features.html#css)
- [CSS Environment Variables](https://developer.mozilla.org/en-US/docs/Web/CSS/env)
- [Viewport Units](https://developer.mozilla.org/en-US/docs/Web/CSS/length#viewport-percentage_lengths)
