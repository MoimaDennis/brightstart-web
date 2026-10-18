<?php
// Central settings. On Azure these come from App Service > Environment variables.
// On your own computer (XAMPP) the defaults below are used.
if (!function_exists('cfg')) {
    function cfg($key, $default = '') {
        $v = getenv($key);
        return ($v !== false && $v !== '') ? $v : $default;
    }
}
if (!defined('DB_HOST')) {
    define('DB_HOST', cfg('DB_HOST', 'localhost'));
    define('DB_USER', cfg('DB_USER', 'root'));
    define('DB_PASS', cfg('DB_PASS', ''));
    define('DB_NAME', cfg('DB_NAME', 'brightstart'));
    define('DB_PORT', (int) cfg('DB_PORT', '3306'));
    // Firebase "Web API key" (public value, safe to keep here). Used to verify Google sign-in tokens.
    define('FIREBASE_API_KEY', cfg('FIREBASE_API_KEY', 'AIzaSyBTTdUkcsUGhKoTtVNPiLCVWJIXzLIxffE'));
}