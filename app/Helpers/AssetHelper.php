<?php

// Asset Helper Functions

if (!function_exists('asset_image')) {
    /**
     * Generate an image asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset_image(string $path): string
    {
        return asset('assets/images/' . $path);
    }
}

if (!function_exists('asset_icon')) {
    /**
     * Generate an icon asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset_icon(string $path): string
    {
        return asset('assets/icons/' . $path);
    }
}

if (!function_exists('asset_font')) {
    /**
     * Generate a font asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset_font(string $path): string
    {
        return asset('assets/fonts/' . $path);
    }
}

if (!function_exists('asset_document')) {
    /**
     * Generate a document asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset_document(string $path): string
    {
        return asset('assets/documents/' . $path);
    }
}

if (!function_exists('asset_sound')) {
    /**
     * Generate a sound asset URL.
     *
     * @param string $path
     * @return string
     */
    function asset_sound(string $path): string
    {
        return asset('assets/sounds/' . $path);
    }
}
