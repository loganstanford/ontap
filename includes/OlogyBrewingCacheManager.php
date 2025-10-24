<?php
namespace OlogyBrewing;

class OlogyBrewingCacheManager {
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
    
    public function get_api_cache($endpoint, $params = []) {
        $cache_key = 'api_' . md5($endpoint . serialize($params));
        return $this->get($cache_key);
    }
    
    public function set_api_cache($endpoint, $params, $data, $expiry = 3600) {
        $cache_key = 'api_' . md5($endpoint . serialize($params));
        return $this->set($cache_key, $data, $expiry);
    }
}
