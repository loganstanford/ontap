<?php
namespace OnTap;

class Logger {
    private $log_dir;
    private $log_levels = ['error', 'warning', 'info', 'debug'];
    
    public function __construct() {
        $this->log_dir = WP_CONTENT_DIR . '/logs/ontap/';
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
        $recent_logs = get_transient('ontap_recent_logs') ?: [];
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
        
        set_transient('ontap_recent_logs', $recent_logs, HOUR_IN_SECONDS);
    }
    
    private function ensure_log_directory() {
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }
    }
    
    public function get_recent_logs($limit = 50) {
        return get_transient('ontap_recent_logs') ?: [];
    }
    
    public function clear_logs() {
        delete_transient('ontap_recent_logs');
        
        // Clear log files
        $files = glob($this->log_dir . '*.log*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
