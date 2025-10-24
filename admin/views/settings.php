<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('OnTap Settings', 'ontap'); ?></h1>
    
    <form id="ontap-settings-form">
        <?php wp_nonce_field('ontap_nonce', 'nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Debug Settings', 'ontap'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="debug_enabled" value="1" <?php checked($settings['debug_enabled'], true); ?>>
                            <?php _e('Enable debug logging', 'ontap'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, detailed logging will be written to files. This can help with troubleshooting.', 'ontap'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Debug Level', 'ontap'); ?></th>
                <td>
                    <select name="debug_level">
                        <option value="minimal" <?php selected($settings['debug_level'], 'minimal'); ?>><?php _e('Minimal - Only errors and important events', 'ontap'); ?></option>
                        <option value="normal" <?php selected($settings['debug_level'], 'normal'); ?>><?php _e('Normal - Standard debugging information', 'ontap'); ?></option>
                        <option value="verbose" <?php selected($settings['debug_level'], 'verbose'); ?>><?php _e('Verbose - All operations and detailed information', 'ontap'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Log Retention', 'ontap'); ?></th>
                <td>
                    <input type="number" name="debug_retention" value="<?php echo esc_attr($settings['debug_retention']); ?>" min="10" max="1000" class="small-text">
                    <p class="description">
                        <?php _e('Maximum number of log entries to keep in memory (10-1000).', 'ontap'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('API Settings', 'ontap'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Untappd Client ID', 'ontap'); ?></th>
                <td>
                    <input type="text" name="untappd_client_id" value="<?php echo esc_attr($settings['untappd_client_id']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Untappd API client ID.', 'ontap'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Untappd Client Secret', 'ontap'); ?></th>
                <td>
                    <input type="password" name="untappd_client_secret" value="<?php echo esc_attr($settings['untappd_client_secret']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Untappd API client secret.', 'ontap'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Dropbox Access Token', 'ontap'); ?></th>
                <td>
                    <input type="password" name="dropbox_access_token" value="<?php echo esc_attr($settings['dropbox_access_token']); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Your Dropbox API access token.', 'ontap'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Sync Settings', 'ontap'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Sync', 'ontap'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="sync_enabled" value="1" <?php checked($settings['sync_enabled'], true); ?>>
                        <?php _e('Enable automatic syncing', 'ontap'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Sync Frequency', 'ontap'); ?></th>
                <td>
                    <select name="sync_frequency">
                        <option value="hourly" <?php selected($settings['sync_frequency'], 'hourly'); ?>><?php _e('Hourly', 'ontap'); ?></option>
                        <option value="twicedaily" <?php selected($settings['sync_frequency'], 'twicedaily'); ?>><?php _e('Twice Daily', 'ontap'); ?></option>
                        <option value="daily" <?php selected($settings['sync_frequency'], 'daily'); ?>><?php _e('Daily', 'ontap'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'ontap'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ontap-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ologyBrewing.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_save_settings',
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
