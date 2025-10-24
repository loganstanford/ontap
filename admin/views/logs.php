<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Ology Brewing Sync Logs', 'ology-brewing'); ?></h1>
    
    <div class="ology-brewing-logs-header">
        <button type="button" class="button" id="refresh-logs">
            <?php _e('Refresh', 'ology-brewing'); ?>
        </button>
        <button type="button" class="button" id="clear-logs">
            <?php _e('Clear Logs', 'ology-brewing'); ?>
        </button>
    </div>
    
    <div class="ology-brewing-logs-container">
        <?php if (!empty($recent_logs)): ?>
            <div class="ology-brewing-logs">
                <?php foreach ($recent_logs as $log): ?>
                    <div class="ology-brewing-log-entry ology-brewing-log-<?php echo esc_attr($log['level']); ?>">
                        <span class="ology-brewing-log-time"><?php echo esc_html($log['timestamp']); ?></span>
                        <span class="ology-brewing-log-level">[<?php echo esc_html($log['level']); ?>]</span>
                        <span class="ology-brewing-log-message"><?php echo esc_html($log['message']); ?></span>
                        <?php if (!empty($log['context'])): ?>
                            <div class="ology-brewing-log-context">
                                <pre><?php echo esc_html(json_encode($log['context'], JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No logs available', 'ology-brewing'); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.ology-brewing-logs-header {
    margin-bottom: 20px;
}

.ology-brewing-logs-header .button {
    margin-right: 10px;
}

.ology-brewing-logs-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ology-brewing-logs {
    max-height: 600px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.4;
}

.ology-brewing-log-entry {
    margin-bottom: 10px;
    padding: 8px;
    border-left: 3px solid #ddd;
    background: #f9f9f9;
}

.ology-brewing-log-error {
    border-left-color: #d63638;
    background: #fcf0f1;
}

.ology-brewing-log-warning {
    border-left-color: #dba617;
    background: #fcf9e8;
}

.ology-brewing-log-info {
    border-left-color: #00a32a;
    background: #f0f8f0;
}

.ology-brewing-log-debug {
    border-left-color: #72aee6;
    background: #f0f6fc;
}

.ology-brewing-log-time {
    color: #666;
    margin-right: 10px;
    font-weight: bold;
}

.ology-brewing-log-level {
    font-weight: bold;
    margin-right: 10px;
    text-transform: uppercase;
}

.ology-brewing-log-message {
    color: #333;
}

.ology-brewing-log-context {
    margin-top: 5px;
    padding: 5px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.ology-brewing-log-context pre {
    margin: 0;
    font-size: 11px;
    color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#refresh-logs').on('click', function() {
        location.reload();
    });
    
    $('#clear-logs').on('click', function() {
        if (confirm('Are you sure you want to clear all logs?')) {
            $.ajax({
                url: ologyBrewing.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ology_brewing_clear_logs',
                    nonce: ologyBrewing.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(ologyBrewing.strings.logsCleared);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while clearing logs.');
                }
            });
        }
    });
});
</script>
