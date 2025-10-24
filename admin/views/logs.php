<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('OnTap Sync Logs', 'ontap'); ?></h1>
    
    <div class="ontap-logs-header">
        <button type="button" class="button" id="refresh-logs">
            <?php _e('Refresh', 'ontap'); ?>
        </button>
        <button type="button" class="button" id="clear-logs">
            <?php _e('Clear Logs', 'ontap'); ?>
        </button>
    </div>
    
    <div class="ontap-logs-container">
        <?php if (!empty($recent_logs)): ?>
            <div class="ontap-logs">
                <?php foreach ($recent_logs as $log): ?>
                    <div class="ontap-log-entry ontap-log-<?php echo esc_attr($log['level']); ?>">
                        <span class="ontap-log-time"><?php echo esc_html($log['timestamp']); ?></span>
                        <span class="ontap-log-level">[<?php echo esc_html($log['level']); ?>]</span>
                        <span class="ontap-log-message"><?php echo esc_html($log['message']); ?></span>
                        <?php if (!empty($log['context'])): ?>
                            <div class="ontap-log-context">
                                <pre><?php echo esc_html(json_encode($log['context'], JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No logs available', 'ontap'); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.ontap-logs-header {
    margin-bottom: 20px;
}

.ontap-logs-header .button {
    margin-right: 10px;
}

.ontap-logs-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ontap-logs {
    max-height: 600px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.4;
}

.ontap-log-entry {
    margin-bottom: 10px;
    padding: 8px;
    border-left: 3px solid #ddd;
    background: #f9f9f9;
}

.ontap-log-error {
    border-left-color: #d63638;
    background: #fcf0f1;
}

.ontap-log-warning {
    border-left-color: #dba617;
    background: #fcf9e8;
}

.ontap-log-info {
    border-left-color: #00a32a;
    background: #f0f8f0;
}

.ontap-log-debug {
    border-left-color: #72aee6;
    background: #f0f6fc;
}

.ontap-log-time {
    color: #666;
    margin-right: 10px;
    font-weight: bold;
}

.ontap-log-level {
    font-weight: bold;
    margin-right: 10px;
    text-transform: uppercase;
}

.ontap-log-message {
    color: #333;
}

.ontap-log-context {
    margin-top: 5px;
    padding: 5px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.ontap-log-context pre {
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
                    action: 'ontap_clear_logs',
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
