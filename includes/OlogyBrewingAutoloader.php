<?php
namespace OlogyBrewing;

class OlogyBrewingAutoloader {
    private $prefix = 'OlogyBrewing\\';
    private $base_dir;
    
    public function __construct() {
        $this->base_dir = OLGY_BREWING_PLUGIN_DIR . 'includes/';
        spl_autoload_register([$this, 'load_class']);
    }
    
    public function load_class($class) {
        if (strpos($class, $this->prefix) !== 0) {
            return;
        }
        
        $relative_class = substr($class, strlen($this->prefix));
        $file = $this->base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
}

new OlogyBrewingAutoloader();
