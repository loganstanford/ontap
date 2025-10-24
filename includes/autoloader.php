<?php
/**
 * Ology Brewing Autoloader
 * 
 * Simple autoloader following WordPress conventions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple autoloader function
 * 
 * @param string $class_name The class name to load
 */
function ology_brewing_autoload($class_name) {
    // Only handle our namespace
    if (strpos($class_name, 'OlogyBrewing\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix
    $relative_class = substr($class_name, strlen('OlogyBrewing\\'));
    
    // Convert namespace separators to directory separators
    $file = OLOGY_BREWING_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
}

// Register the autoloader
spl_autoload_register('ology_brewing_autoload');
