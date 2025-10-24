<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('OnTap Dashboard', 'ontap'); ?></h1>
    
    <div class="ontap-dashboard">
        <div class="ontap-cards">
            <div class="ontap-card">
                <h2><?php _e('Sync Status', 'ontap'); ?></h2>
                <p>
                    <strong><?php _e('Status:', 'ontap'); ?></strong> 
                    <?php echo $sync_enabled ? __('Enabled', 'ontap') : __('Disabled', 'ontap'); ?>
                </p>
                <p>
                    <strong><?php _e('Last Sync:', 'ontap'); ?></strong> 
                    <?php echo esc_html($last_sync); ?>
                </p>
                <p>
                    <button type="button" class="button button-primary" id="start-sync">
                        <?php _e('Start Manual Sync', 'ontap'); ?>
                    </button>
                </p>
            </div>
            
            <div class="ontap-card">
                <h2><?php _e('Recent Activity', 'ontap'); ?></h2>
                <?php if (!empty($recent_logs)): ?>
                    <div class="ontap-logs">
                        <?php foreach (array_slice($recent_logs, -10) as $log): ?>
                            <div class="ontap-log-entry ontap-log-<?php echo esc_attr($log['level']); ?>">
                                <span class="ontap-log-time"><?php echo esc_html($log['timestamp']); ?></span>
                                <span class="ontap-log-level">[<?php echo esc_html($log['level']); ?>]</span>
                                <span class="ontap-log-message"><?php echo esc_html($log['message']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?php _e('No recent activity', 'ontap'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ontap-actions">
            <a href="<?php echo admin_url('admin.php?page=ontap-settings'); ?>" class="button">
                <?php _e('Settings', 'ontap'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=ontap-logs'); ?>" class="button">
                <?php _e('View All Logs', 'ontap'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.ontap-dashboard {
    margin-top: 20px;
}

.ontap-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.ontap-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ontap-card h2 {
    margin-top: 0;
    margin-bottom: 15px;
}

.ontap-logs {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f9f9f9;
    font-family: monospace;
    font-size: 12px;
}

.ontap-log-entry {
    margin-bottom: 5px;
    padding: 2px 0;
}

.ontap-log-error {
    color: #d63638;
}

.ontap-log-warning {
    color: #dba617;
}

.ontap-log-info {
    color: #00a32a;
}

.ontap-log-debug {
    color: #72aee6;
}

.ontap-log-time {
    color: #666;
    margin-right: 10px;
}

.ontap-log-level {
    font-weight: bold;
    margin-right: 10px;
}

.ontap-actions {
    margin-top: 20px;
}

.ontap-actions .button {
    margin-right: 10px;
}
</style>
