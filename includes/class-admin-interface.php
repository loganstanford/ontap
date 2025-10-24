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
        add_action('wp_ajax_ology_brewing_save_settings', [$this, 'handle_ajax_save_settings']);
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
        
        wp_localize_script('ology-brewing-admin', 'ologyBrewing', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ology_brewing_nonce'),
            'strings' => [
                'syncStarted' => __('Sync started', 'ology-brewing'),
                'syncCompleted' => __('Sync completed', 'ology-brewing'),
                'syncFailed' => __('Sync failed', 'ology-brewing'),
                'logsCleared' => __('Logs cleared', 'ology-brewing'),
                'settingsSaved' => __('Settings saved', 'ology-brewing')
            ]
        ]);
    }
    
    public function render_dashboard() {
        $recent_logs = $this->logger->get_recent_logs(20);
        $sync_enabled = get_option('ology_sync_enabled', false);
        $last_sync = get_option('ology_last_sync', 'Never');
        
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    public function render_settings() {
        $settings = $this->get_settings();
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    public function render_logs() {
        $recent_logs = $this->logger->get_recent_logs(100);
        include OLOGY_BREWING_PLUGIN_DIR . 'admin/views/logs.php';
    }
    
    public function handle_ajax_sync() {
        check_ajax_referer('ology_brewing_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        // TODO: Implement actual sync logic
        $this->logger->info('Manual sync started by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Sync started', 'ology-brewing')]);
    }
    
    public function handle_ajax_clear_logs() {
        check_ajax_referer('ology_brewing_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        $this->logger->clear_logs();
        $this->logger->info('Logs cleared by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Logs cleared', 'ology-brewing')]);
    }
    
    public function handle_ajax_save_settings() {
        check_ajax_referer('ology_brewing_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        $settings = [
            'ology_debug_enabled' => isset($_POST['debug_enabled']),
            'ology_debug_level' => sanitize_text_field($_POST['debug_level'] ?? 'normal'),
            'ology_debug_retention' => intval($_POST['debug_retention'] ?? 100),
            'ology_untappd_client_id' => sanitize_text_field($_POST['untappd_client_id'] ?? ''),
            'ology_untappd_client_secret' => sanitize_text_field($_POST['untappd_client_secret'] ?? ''),
            'ology_dropbox_access_token' => sanitize_text_field($_POST['dropbox_access_token'] ?? ''),
            'ology_sync_frequency' => sanitize_text_field($_POST['sync_frequency'] ?? 'hourly'),
            'ology_sync_enabled' => isset($_POST['sync_enabled'])
        ];
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        $this->logger->info('Settings updated by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Settings saved', 'ology-brewing')]);
    }
    
    private function get_settings() {
        return [
            'debug_enabled' => get_option('ology_debug_enabled', false),
            'debug_level' => get_option('ology_debug_level', 'normal'),
            'debug_retention' => get_option('ology_debug_retention', 100),
            'untappd_client_id' => get_option('ology_untappd_client_id', ''),
            'untappd_client_secret' => get_option('ology_untappd_client_secret', ''),
            'dropbox_access_token' => get_option('ology_dropbox_access_token', ''),
            'sync_frequency' => get_option('ology_sync_frequency', 'hourly'),
            'sync_enabled' => get_option('ology_sync_enabled', false)
        ];
    }
}
