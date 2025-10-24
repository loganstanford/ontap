<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Ology Brewing Dashboard', 'ology-brewing'); ?></h1>
    
    <div class="ology-brewing-dashboard">
        <div class="ology-brewing-cards">
            <div class="ology-brewing-card">
                <h2><?php _e('Sync Status', 'ology-brewing'); ?></h2>
                <p>
                    <strong><?php _e('Status:', 'ology-brewing'); ?></strong> 
                    <?php echo $sync_enabled ? __('Enabled', 'ology-brewing') : __('Disabled', 'ology-brewing'); ?>
                </p>
                <p>
                    <strong><?php _e('Last Sync:', 'ology-brewing'); ?></strong> 
                    <?php echo esc_html($last_sync); ?>
                </p>
                <p>
                    <button type="button" class="button button-primary" id="start-sync">
                        <?php _e('Start Manual Sync', 'ology-brewing'); ?>
                    </button>
                </p>
            </div>
            
            <div class="ology-brewing-card">
                <h2><?php _e('Recent Activity', 'ology-brewing'); ?></h2>
                <?php if (!empty($recent_logs)): ?>
                    <div class="ology-brewing-logs">
                        <?php foreach (array_slice($recent_logs, -10) as $log): ?>
                            <div class="ology-brewing-log-entry ology-brewing-log-<?php echo esc_attr($log['level']); ?>">
                                <span class="ology-brewing-log-time"><?php echo esc_html($log['timestamp']); ?></span>
                                <span class="ology-brewing-log-level">[<?php echo esc_html($log['level']); ?>]</span>
                                <span class="ology-brewing-log-message"><?php echo esc_html($log['message']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?php _e('No recent activity', 'ology-brewing'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ology-brewing-actions">
            <a href="<?php echo admin_url('admin.php?page=ology-brewing-settings'); ?>" class="button">
                <?php _e('Settings', 'ology-brewing'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=ology-brewing-logs'); ?>" class="button">
                <?php _e('View All Logs', 'ology-brewing'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.ology-brewing-dashboard {
    margin-top: 20px;
}

.ology-brewing-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.ology-brewing-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ology-brewing-card h2 {
    margin-top: 0;
    margin-bottom: 15px;
}

.ology-brewing-logs {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f9f9f9;
    font-family: monospace;
    font-size: 12px;
}

.ology-brewing-log-entry {
    margin-bottom: 5px;
    padding: 2px 0;
}

.ology-brewing-log-error {
    color: #d63638;
}

.ology-brewing-log-warning {
    color: #dba617;
}

.ology-brewing-log-info {
    color: #00a32a;
}

.ology-brewing-log-debug {
    color: #72aee6;
}

.ology-brewing-log-time {
    color: #666;
    margin-right: 10px;
}

.ology-brewing-log-level {
    font-weight: bold;
    margin-right: 10px;
}

.ology-brewing-actions {
    margin-top: 20px;
}

.ology-brewing-actions .button {
    margin-right: 10px;
}
</style>
