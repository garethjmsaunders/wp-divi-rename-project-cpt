<?php
// If uninstall.php is not called by WordPress, die.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Option name used by the plugin
$option_name = 'divi_projects_cpt_rename_settings';

// Delete the option from the database
delete_option( $option_name );

// For multisite
if ( is_multisite() ) {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        delete_option( $option_name );
        restore_current_blog();
    }
}