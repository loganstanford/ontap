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
    
    // Convert namespace separators to directory separators and handle WordPress naming
    $class_file = str_replace('\\', '/', $relative_class);
    
    // Handle WordPress class naming convention (class-name.php)
    if (strpos($class_file, '/') === false) {
        // Single class, convert to WordPress format
        // Convert CamelCase to kebab-case
        $class_file = 'class-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_file));
        
        // Handle special case for OnTap -> ontap
        if ($class_file === 'class-on-tap') {
            $class_file = 'class-ontap';
        }
    } else {
        // Multiple levels, only convert the last part
        $parts = explode('/', $class_file);
        $last_part = array_pop($parts);
        $last_part = 'class-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $last_part));
        $parts[] = $last_part;
        $class_file = implode('/', $parts);
    }
    
    $file = ONTAP_PLUGIN_DIR . $class_file . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
}

// Register the autoloader
spl_autoload_register('ontap_autoload');
