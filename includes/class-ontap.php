<?php
namespace OnTap;

class OnTap {
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
        register_activation_hook(ONTAP_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(ONTAP_PLUGIN_FILE, [$this, 'deactivate']);
        
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
        
        $this->logger->info('OnTap plugin activated');
    }
    
    public function deactivate() {
        $this->clear_scheduled_hooks();
        $this->logger->info('OnTap plugin deactivated');
    }
    
    public function init() {
        load_plugin_textdomain('ontap', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function admin_init() {
        $this->admin_interface->init();
    }
    
    public function admin_menu() {
        $this->admin_interface->add_menu_pages();
    }
    
    private function create_tables() {
        Database::create_tables();
    }
    
    private function set_default_options() {
        $default_options = [
            'ontap_debug_enabled' => false,
            'ontap_debug_level' => 'normal',
            'ontap_debug_retention' => 100,
            'ontap_untappd_client_id' => '',
            'ontap_untappd_client_secret' => '',
            'ontap_dropbox_access_token' => '',
            'ontap_sync_frequency' => 'hourly',
            'ontap_sync_enabled' => false
        ];
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    private function schedule_cron_jobs() {
        if (!wp_next_scheduled('ontap_sync_cron')) {
            wp_schedule_event(time(), 'hourly', 'ontap_sync_cron');
        }
        
        if (!wp_next_scheduled('ontap_cleanup_cron')) {
            wp_schedule_event(time(), 'daily', 'ontap_cleanup_cron');
        }
    }
    
    private function clear_scheduled_hooks() {
        wp_clear_scheduled_hook('ontap_sync_cron');
        wp_clear_scheduled_hook('ontap_cleanup_cron');
    }
}
