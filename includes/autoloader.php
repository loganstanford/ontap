<?php
/**
 * OnTap Autoloader
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
function ontap_autoload($class_name) {
    // Only handle our namespace
    if (strpos($class_name, 'OnTap\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix
    $relative_class = substr($class_name, strlen('OnTap\\'));
    
    // Convert namespace separators to directory separators
    $file = ONTAP_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
}

// Register the autoloader
spl_autoload_register('ontap_autoload');
