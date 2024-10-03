<?php
// If uninstall.php is not called by WordPress, die.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Option name used by the plugin
$option_name = 'divi_projects_cpt_rename_settings';

// Delete the option from the database
delete_option( $option_name );
