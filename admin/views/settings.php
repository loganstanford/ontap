<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Ology Brewing Settings', 'ology-brewing'); ?></h1>
    
    <form id="ology-brewing-settings-form">
        <?php wp_nonce_field('ology_brewing_nonce', 'nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Debug Settings', 'ology-brewing'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="debug_enabled" value="1" <?php checked($settings['debug_enabled'], true); ?>>
                            <?php _e('Enable debug logging', 'ology-brewing'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, detailed logging will be written to files. This can help with troubleshooting.', 'ology-brewing'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Debug Level', 'ology-brewing'); ?></th>
                <td>
                    <select name="debug_level">
                        <option value="minimal" <?php selected($settings['debug_level'], 'minimal'); ?>><?php _e('Minimal - Only errors and important events', 'ology-brewing'); ?></option>
                        <option value="normal" <?php selected($settings['debug_level'], 'normal'); ?>><?php _e('Normal - Standard debugging information', 'ology-brewing'); ?></option>
                        <option value="verbose" <?php selected($settings['debug_level'], 'verbose'); ?>><?php _e('Verbose - All operations and detailed information', 'ology-brewing'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Log Retention', 'ology-brewing'); ?></th>
                <td>
                    <input type="number" name="debug_retention" value="<?php echo esc_attr($settings['debug_retention']); ?>" min="10" max="1000" class="small-text">
                    <p class="description">
                        <?php _e('Maximum number of log entries to keep in memory (10-1000).', 'ology-brewing'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('API Settings', 'ology-brewing'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Untappd Client ID', 'ology-brewing'); ?></th>
                <td>
                    <input type="text" name="untappd_client_id" value="<?php echo esc_attr($settings['untappd_client_id']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Untappd API client ID.', 'ology-brewing'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Untappd Client Secret', 'ology-brewing'); ?></th>
                <td>
                    <input type="password" name="untappd_client_secret" value="<?php echo esc_attr($settings['untappd_client_secret']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Untappd API client secret.', 'ology-brewing'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Dropbox Access Token', 'ology-brewing'); ?></th>
                <td>
                    <input type="password" name="dropbox_access_token" value="<?php echo esc_attr($settings['dropbox_access_token']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Dropbox API access token.', 'ology-brewing'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Sync Settings', 'ology-brewing'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Sync', 'ology-brewing'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="sync_enabled" value="1" <?php checked($settings['sync_enabled'], true); ?>>
                        <?php _e('Enable automatic syncing', 'ology-brewing'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Sync Frequency', 'ology-brewing'); ?></th>
                <td>
                    <select name="sync_frequency">
                        <option value="hourly" <?php selected($settings['sync_frequency'], 'hourly'); ?>><?php _e('Hourly', 'ology-brewing'); ?></option>
                        <option value="twicedaily" <?php selected($settings['sync_frequency'], 'twicedaily'); ?>><?php _e('Twice Daily', 'ology-brewing'); ?></option>
                        <option value="daily" <?php selected($settings['sync_frequency'], 'daily'); ?>><?php _e('Daily', 'ology-brewing'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'ology-brewing'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ology-brewing-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ologyBrewing.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ology_brewing_save_settings',
                nonce: ologyBrewing.nonce,
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    alert(ologyBrewing.strings.settingsSaved);
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred while saving settings.');
            }
        });
    });
});
</script>
