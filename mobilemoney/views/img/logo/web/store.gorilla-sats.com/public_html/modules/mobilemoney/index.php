<?php
/**
 * Module entry point and autoloader
 */

header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: denial');
header('X-XSS-Protection: 1; mode=block');

require_once __DIR__.'/vendor/autoload.php';

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'PrestaShop\\Module\\MobileMoney\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/classes/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators, append with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});