import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/**
 * Vite Configuration for Laravel
 * 
 * This configuration handles the build process for frontend assets including:
 * - SCSS stylesheets (app.scss, admin.scss)
 * - JavaScript files (app.js, admin.js, frontend.js)
 * - Admin dashboard specific scripts (admin/dashboard.js)
 * 
 * The configuration also includes SCSS preprocessor options to handle
 * deprecation warnings from the Sass compiler.
 */
export default defineConfig({
    plugins: [
        laravel({
            // Input files to be processed by Vite
            input: [
                'resources/sass/app.scss',           // Main application styles
                'resources/sass/admin.scss',         // Admin panel styles
                'resources/js/app.js',               // Main application JavaScript
                'resources/js/admin.js',             // Admin panel JavaScript (sidebar, etc.)
                'resources/js/admin/dashboard.js',   // Admin dashboard charts and exports
                'resources/js/frontend.js',          // Frontend-specific JavaScript
            ],
            refresh: true, // Enable hot module replacement
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler', // Use modern SCSS compiler API
                // Silence specific deprecation warnings to keep build output clean
                silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions'],
            },
        },
    },
});
