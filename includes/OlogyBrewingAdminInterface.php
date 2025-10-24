<?php
namespace OlogyBrewing;

class OlogyBrewingAdminInterface {
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
        
        wp_enqueue_script('ology-brewing-admin', OLGY_BREWING_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], OLGY_BREWING_VERSION, true);
        wp_enqueue_style('ology-brewing-admin', OLGY_BREWING_PLUGIN_URL . 'assets/css/admin.css', [], OLGY_BREWING_VERSION);
        
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
        $sync_enabled = get_option('ology_brewing_sync_enabled', false);
        $last_sync = get_option('ology_brewing_last_sync', 'Never');
        
        include OLGY_BREWING_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    public function render_settings() {
        $settings = $this->get_settings();
        include OLGY_BREWING_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    public function render_logs() {
        $recent_logs = $this->logger->get_recent_logs(100);
        include OLGY_BREWING_PLUGIN_DIR . 'admin/views/logs.php';
    }
    
    public function handle_ajax_sync() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'ology_brewing_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'ology-brewing')]);
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        // TODO: Implement actual sync logic
        $this->logger->info('Manual sync started by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Sync started', 'ology-brewing')]);
    }
    
    public function handle_ajax_clear_logs() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'ology_brewing_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'ology-brewing')]);
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        $this->logger->clear_logs();
        $this->logger->info('Logs cleared by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Logs cleared', 'ology-brewing')]);
    }
    
    public function handle_ajax_save_settings() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'ology_brewing_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'ology-brewing')]);
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'ology-brewing')]);
        }
        
        // Sanitize and validate all input data
        $settings = [
            'ology_brewing_debug_enabled' => isset($_POST['debug_enabled']),
            'ology_brewing_debug_level' => sanitize_text_field($_POST['debug_level'] ?? 'normal'),
            'ology_brewing_debug_retention' => absint($_POST['debug_retention'] ?? 100),
            'ology_brewing_untappd_client_id' => sanitize_text_field($_POST['untappd_client_id'] ?? ''),
            'ology_brewing_untappd_client_secret' => sanitize_text_field($_POST['untappd_client_secret'] ?? ''),
            'ology_brewing_dropbox_access_token' => sanitize_text_field($_POST['dropbox_access_token'] ?? ''),
            'ology_brewing_sync_frequency' => sanitize_text_field($_POST['sync_frequency'] ?? 'hourly'),
            'ology_brewing_sync_enabled' => isset($_POST['sync_enabled'])
        ];
        
        // Validate debug retention range
        if ($settings['ology_brewing_debug_retention'] < 10) {
            $settings['ology_brewing_debug_retention'] = 10;
        } elseif ($settings['ology_brewing_debug_retention'] > 1000) {
            $settings['ology_brewing_debug_retention'] = 1000;
        }
        
        // Validate debug level
        $allowed_levels = ['minimal', 'normal', 'verbose'];
        if (!in_array($settings['ology_brewing_debug_level'], $allowed_levels)) {
            $settings['ology_brewing_debug_level'] = 'normal';
        }
        
        // Validate sync frequency
        $allowed_frequencies = ['hourly', 'twicedaily', 'daily'];
        if (!in_array($settings['ology_brewing_sync_frequency'], $allowed_frequencies)) {
            $settings['ology_brewing_sync_frequency'] = 'hourly';
        }
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        $this->logger->info('Settings updated by user: ' . get_current_user_id());
        
        wp_send_json_success(['message' => __('Settings saved', 'ology-brewing')]);
    }
    
    private function get_settings() {
        return [
            'debug_enabled' => get_option('ology_brewing_debug_enabled', false),
            'debug_level' => get_option('ology_brewing_debug_level', 'normal'),
            'debug_retention' => get_option('ology_brewing_debug_retention', 100),
            'untappd_client_id' => get_option('ology_brewing_untappd_client_id', ''),
            'untappd_client_secret' => get_option('ology_brewing_untappd_client_secret', ''),
            'dropbox_access_token' => get_option('ology_brewing_dropbox_access_token', ''),
            'sync_frequency' => get_option('ology_brewing_sync_frequency', 'hourly'),
            'sync_enabled' => get_option('ology_brewing_sync_enabled', false)
        ];
    }
}
