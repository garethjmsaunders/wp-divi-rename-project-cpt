<?php
// If uninstall.php is not called by WordPress, die.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Option name used by the plugin
$option_name = 'divi_projects_cpt_rename_settings';

// Delete the option from the database
delete_option($option_name);

// For multisite
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option($option_name);
        restore_current_blog();
    }
}

/*
Explanation
Security Check: The script first checks if WP_UNINSTALL_PLUGIN is defined. This constant is defined by WordPress when it calls the uninstall script. If the script is accessed directly, it will terminate, providing a security measure.

Delete Option: The delete_option() function is used to remove the option from the WordPress database.

Multisite Support: If your plugin supports WordPress Multisite, the script includes logic to delete the option from all sites in the network. It uses the global $wpdb object to get all blog IDs, switches to each blog, deletes the option, and then restores the current blog.

Step 3: Test the Uninstall Script
Activate Your Plugin: Ensure your plugin is activated and the settings are saved.
Uninstall the Plugin: Go to the WordPress admin, deactivate, and then delete the plugin.
Verify Deletion: Check the database to verify that the options have been removed.
By following these steps, you ensure that your plugin cleans up after itself and removes all saved options when it is uninstalled, leaving no residual data in the WordPress database.
*/