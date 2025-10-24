# Ology Brewing - Implementation Guide

## 🎯 Current Status

**Phase**: Planning Complete  
**Next**: Phase 1 - Foundation  
**Timeline**: Week 1

## 📋 Phase 1: Foundation Implementation

### Step 1: Create Main Plugin File

**File**: `ology-brewing.php`  
**Purpose**: WordPress plugin entry point

```php
<?php
/**
 * Plugin Name: Ology Brewing
 * Plugin URI: https://ologybrewing.com
 * Description: Modern brewery management system with Untappd and Dropbox integration
 * Version: 1.0.0
 * Author: Ology Brewing
 * License: GPL v2 or later
 * Text Domain: ology-brewing
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OLOGY_BREWING_VERSION', '1.0.0');
define('OLOGY_BREWING_PLUGIN_FILE', __FILE__);
define('OLOGY_BREWING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OLOGY_BREWING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OLOGY_BREWING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
require_once OLOGY_BREWING_PLUGIN_DIR . 'includes/class-autoloader.php';

// Initialize plugin
add_action('plugins_loaded', function() {
    OlogyBrewing\OlogyBrewing::get_instance();
});
```

### Step 2: Create Autoloader

**File**: `includes/class-autoloader.php`  
**Purpose**: PSR-4 autoloading for classes

```php
<?php
namespace OlogyBrewing;

class Autoloader {
    private $prefix = 'OlogyBrewing\\';
    private $base_dir;

    public function __construct() {
        $this->base_dir = OLOGY_BREWING_PLUGIN_DIR . 'includes/';
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

new Autoloader();
```

### Step 3: Create Main Plugin Class

**File**: `includes/class-ology-brewing.php`  
**Purpose**: Core plugin functionality

```php
<?php
namespace OlogyBrewing;

class OlogyBrewing {
    private static $instance = null;
    private $logger;
    private $cache_manager;
    private $admin_interface;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }

    private function init_hooks() {
        register_activation_hook(OLOGY_BREWING_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(OLOGY_BREWING_PLUGIN_FILE, [$this, 'deactivate']);

        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    private function init_components() {
        $this->logger = new Logger();
        $this->cache_manager = new CacheManager();
        $this->admin_interface = new AdminInterface();
    }

    public function activate() {
        $this->create_tables();
        $this->set_default_options();
        $this->schedule_cron_jobs();
    }

    public function deactivate() {
        $this->clear_scheduled_hooks();
    }

    public function init() {
        load_plugin_textdomain('ology-brewing', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function admin_init() {
        $this->admin_interface->init();
    }

    public function admin_menu() {
        $this->admin_interface->add_menu_pages();
    }
}
```

### Step 4: Create Logger Class

**File**: `includes/class-logger.php`  
**Purpose**: Centralized logging system

```php
<?php
namespace OlogyBrewing;

class Logger {
    private $log_dir;
    private $log_levels = ['error', 'warning', 'info', 'debug'];

    public function __construct() {
        $this->log_dir = WP_CONTENT_DIR . '/logs/ology-brewing/';
        $this->ensure_log_directory();
    }

    public function log($message, $level = 'info', $context = []) {
        $debug_enabled = get_option('ology_debug_enabled', false);
        if (!$debug_enabled && $level === 'debug') {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}";

        if (!empty($context)) {
            $log_entry .= ' ' . json_encode($context);
        }

        $this->write_to_file($log_entry, $level);
        $this->write_to_transient($message, $level, $context);
    }

    public function error($message, $context = []) {
        $this->log($message, 'error', $context);
    }

    public function warning($message, $context = []) {
        $this->log($message, 'warning', $context);
    }

    public function info($message, $context = []) {
        $this->log($message, 'info', $context);
    }

    public function debug($message, $context = []) {
        $this->log($message, 'debug', $context);
    }

    private function write_to_file($log_entry, $level) {
        $log_file = $this->log_dir . $level . '.log';

        // Rotate log if too large
        if (file_exists($log_file) && filesize($log_file) > 10485760) { // 10MB
            rename($log_file, $log_file . '.' . date('Y-m-d-H-i-s'));
        }

        error_log($log_entry . "\n", 3, $log_file);
    }

    private function write_to_transient($message, $level, $context) {
        $recent_logs = get_transient('ology_brewing_recent_logs') ?: [];
        $recent_logs[] = [
            'timestamp' => current_time('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];

        // Keep only last 50 entries
        if (count($recent_logs) > 50) {
            $recent_logs = array_slice($recent_logs, -50);
        }

        set_transient('ology_brewing_recent_logs', $recent_logs, HOUR_IN_SECONDS);
    }

    private function ensure_log_directory() {
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }
    }
}
```

### Step 5: Create Cache Manager

**File**: `includes/class-cache-manager.php`  
**Purpose**: Caching layer for API responses

```php
<?php
namespace OlogyBrewing;

class CacheManager {
    private $cache_prefix = 'ology_brewing_';
    private $default_expiry = 3600; // 1 hour

    public function get($key, $default = false) {
        return get_transient($this->cache_prefix . $key) ?: $default;
    }

    public function set($key, $value, $expiry = null) {
        if ($expiry === null) {
            $expiry = $this->default_expiry;
        }

        return set_transient($this->cache_prefix . $key, $value, $expiry);
    }

    public function delete($key) {
        return delete_transient($this->cache_prefix . $key);
    }

    public function flush() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$this->cache_prefix}%'");
    }
}
```

### Step 6: Create Admin Interface

**File**: `includes/class-admin-interface.php`  
**Purpose**: Admin interface management

```php
<?php
namespace OlogyBrewing;

class AdminInterface {
    private $logger;

    public function __construct() {
        $this->logger = new Logger();
    }

    public function init() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_ology_brewing_sync', [$this, 'handle_ajax_sync']);
        add_action('wp_ajax_ology_brewing_clear_logs', [$this, 'handle_ajax_clear_logs']);
    }

    public function add_menu_pages() {
        add_menu_page(
            'Ology Brewing',
            'Ology Brewing',
            'manage_options',
            'ology-brewing',
            [$this, 'render_dashboard'],
            'dashicons-beer',
            30
        );

        add_submenu_page(
            'ology-brewing',
            'Settings',
            'Settings',
            'manage_options',
            'ology-brewing-settings',
            [$this, 'render_settings']
        );

        add_submenu_page(
            'ology-brewing',
            'Sync Logs',
            'Sync Logs',
            'manage_options',
            'ology-brewing-logs',
            [$this, 'render_logs']
        );
    }

    public function enqueue_scripts($hook) {
        if (strpos($hook, 'ology-brewing') === false) {
            return;
        }

        wp_enqueue_script('ology-brewing-admin', OLOGY_BREWING_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], OLOGY_BREWING_VERSION, true);
        wp_enqueue_style('ology-brewing-admin', OLOGY_BREWING_PLUGIN_URL . 'assets/css/admin.css', [], OLOGY_BREWING_VERSION);
    }

    public function render_dashboard() {
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function render_settings() {
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function render_logs() {
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/logs.php';
    }

    public function handle_ajax_sync() {
        check_ajax_referer('ology_brewing_sync', 'nonce');

        // TODO: Implement sync logic
        wp_send_json_success(['message' => 'Sync started']);
    }

    public function handle_ajax_clear_logs() {
        check_ajax_referer('ology_brewing_clear_logs', 'nonce');

        delete_transient('ology_brewing_recent_logs');
        wp_send_json_success(['message' => 'Logs cleared']);
    }
}
```

### Step 7: Create Database Tables

**File**: `includes/class-database.php`  
**Purpose**: Database schema management

```php
<?php
namespace OlogyBrewing;

class Database {
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Beers table
        $beers_table = $wpdb->prefix . 'ology_beers';
        $beers_sql = "CREATE TABLE $beers_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            style varchar(100),
            abv decimal(3,1),
            ibu int(11),
            description text,
            untappd_id varchar(50),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY untappd_id (untappd_id)
        ) $charset_collate;";

        // Locations table
        $locations_table = $wpdb->prefix . 'ology_locations';
        $locations_sql = "CREATE TABLE $locations_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(100) UNIQUE,
            availability longtext,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";

        // Sync logs table
        $sync_logs_table = $wpdb->prefix . 'ology_sync_logs';
        $sync_logs_sql = "CREATE TABLE $sync_logs_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sync_type enum('beer', 'file', 'location') NOT NULL,
            status enum('success', 'error', 'warning') NOT NULL,
            message text,
            data longtext,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sync_type (sync_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($beers_sql);
        dbDelta($locations_sql);
        dbDelta($sync_logs_sql);
    }
}
```

## 🧪 Testing Strategy

### Unit Tests

**File**: `tests/unit/class-logger-test.php`

```php
<?php
namespace OlogyBrewing\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OlogyBrewing\Logger;

class LoggerTest extends TestCase {
    private $logger;

    protected function setUp(): void {
        $this->logger = new Logger();
    }

    public function test_log_writes_to_file() {
        $this->logger->info('Test message');

        $log_file = WP_CONTENT_DIR . '/logs/ology-brewing/info.log';
        $this->assertFileExists($log_file);

        $content = file_get_contents($log_file);
        $this->assertStringContainsString('Test message', $content);
    }

    public function test_log_writes_to_transient() {
        $this->logger->info('Test message');

        $recent_logs = get_transient('ology_brewing_recent_logs');
        $this->assertIsArray($recent_logs);
        $this->assertCount(1, $recent_logs);
        $this->assertEquals('Test message', $recent_logs[0]['message']);
    }
}
```

## 📦 Deployment Checklist

### Pre-deployment

- [ ] All tests passing
- [ ] Code review completed
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Performance benchmarks recorded

### Deployment Steps

1. **Create feature branch**: `git checkout -b feature/phase-1-foundation`
2. **Implement changes**: Follow implementation guide
3. **Run tests**: `composer test`
4. **Code review**: Submit pull request
5. **Merge to develop**: After approval
6. **Deploy to staging**: Test in staging environment
7. **Deploy to production**: After staging validation

### Post-deployment

- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Verify admin interface works
- [ ] Test logging system
- [ ] Update documentation

## 🔧 Development Environment Setup

### Prerequisites

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install PHPUnit
composer global require phpunit/phpunit

# Install WordPress CLI
curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

### Local Development

```bash
# Clone repository
git clone <repository-url> ology-brewing
cd ology-brewing

# Install dependencies
composer install

# Run tests
composer test

# Watch for changes
composer watch
```

## 📝 Next Steps

### Immediate Actions

1. **Create feature branch**: `git checkout -b feature/phase-1-foundation`
2. **Implement main plugin file**: Follow Step 1
3. **Create autoloader**: Follow Step 2
4. **Implement core classes**: Follow Steps 3-7
5. **Test implementation**: Run unit tests
6. **Create pull request**: Submit for review

### Success Criteria

- [ ] Plugin activates without errors
- [ ] Admin menu appears in WordPress
- [ ] Settings page loads correctly
- [ ] Logging system works
- [ ] Database tables created
- [ ] All tests passing

---

**Last Updated**: 2024-10-23  
**Phase**: 1 - Foundation  
**Status**: Ready for Implementation
