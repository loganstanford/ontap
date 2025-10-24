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
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'ology_beers',
            $wpdb->prefix . 'ology_locations',
            $wpdb->prefix . 'ology_sync_logs'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
