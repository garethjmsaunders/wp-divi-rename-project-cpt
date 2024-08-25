<?php
/*
 * Plugin Name:         Rename Divi Projects post type
 * Version:             1.2.0
 * Plugin URI:          https://github.com/garethjmsaunders/wp-divi-customise-project
 * Description:         Requires Divi by Elegant Themes. Rename the Divi 'Projects' post type to a user-defined name.
 * Author:              Digital Shed45 - Gareth J M Saunders
 * Author URI:          https://digitalshed45.co.uk
 * Text domain:         divi-projects-cpt-rename
 * Requires at least:   5.3
 * Tested up to:        6.6.1
 * License:             GPL3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if plugin accessed directly
if ( !defined('ABSPATH') ) {
    exit;
}

// Check if Divi theme is active and deactivate plugin if not
function divi_projects_cpt_rename_check_divi_theme_on_activation() {
    $theme = wp_get_theme();
    if ( 'Divi' !== $theme->get( 'Name' ) && 'Divi' !== $theme->get( 'Template' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'This plugin requires the Divi theme from Elegant Themes to be active. Please activate the Divi theme and try again.', 'divi-projects-cpt-rename' ), 'Plugin Activation Error', array( 'back_link' => true ) );
    }
}
register_activation_hook( __FILE__, 'divi_projects_cpt_rename_check_divi_theme_on_activation' );

// Load the Text Domain
function my_plugin_load_textdomain() {
    load_plugin_textdomain( 'my-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'my_plugin_load_textdomain' );

// Enqueue CSS
add_action( 'admin_enqueue_scripts', 'divi_projects_cpt_rename_enqueue_custom_admin_assets' );
function divi_projects_cpt_rename_enqueue_custom_admin_assets() {
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_script( 'custom-admin-js', plugins_url( '/custom-admin.js', __FILE__ ), ['jquery'], null, true );
    wp_enqueue_style( 'custom-admin-css', plugins_url( '/custom-admin.css', __FILE__ ) );
}

// Create the admin menu item as a submenu item of Settings
add_action( 'admin_menu', 'divi_projects_cpt_rename_add_admin_menu' );
function divi_projects_cpt_rename_add_admin_menu() {
    add_options_page(
        __( 'Divi - Rename Projects CPT Settings', 'divi-projects-cpt-rename' ),   // $page_title (string)
        __( 'Rename Divi Projects', 'divi-projects-cpt-rename' ),                  // $menu_title (string)
        'manage_options',                        // $capability (string)
        'divi_projects_cpt_rename',              // $menu_slug (string)
        'divi_projects_cpt_rename_options_page', // $callback_function (callable)
        null                                     // $position (int|float)
    );
}

// Add settings link to Plugins page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'divi_projects_cpt_rename_action_links' );
function divi_projects_cpt_rename_action_links( $links ) {
    $settings_link = '<a href="admin.php?page=divi_projects_cpt_rename">' . __( 'Settings', 'divi-projects-cpt-rename' ) . '</a>';
    // Prepend the settings link to the existing links array
    array_unshift( $links, $settings_link );
    return $links;
}

// Register the settings
add_action( 'admin_init', 'divi_projects_cpt_rename_settings_init' );

function divi_projects_cpt_rename_settings_init() {

    // When registering the settings check the user capability.
    // This ensures that only authorized users can register or modify
    // the plugin settings.
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    register_setting( 'divi_projects_cpt_rename_settings_group', 'divi_projects_cpt_rename_settings', 'divi_projects_cpt_rename_sanitize_settings' );

    // Custom Post Type Settings Section
    add_settings_section(
        'divi_projects_cpt_rename_cpt_settings_section',
        __( 'Custom Post Type Settings', 'divi-projects-cpt-rename' ),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_singular_name',
        __( 'Singular Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_plural_name',
        __( 'Plural Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_slug',
        __( 'Slug', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_menu_icon',
        __( 'Menu Icon', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_menu_icon_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    // Category Section
    add_settings_section(
        'divi_projects_cpt_rename_category_settings_section',
        __( 'Category Settings', 'divi-projects-cpt-rename' ),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_singular_name',
        __( 'Category Singular Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_category_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_plural_name',
        __( 'Category Plural Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_category_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_slug',
        __( 'Category Slug', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_category_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    // Tag Section
    add_settings_section(
        'divi_projects_cpt_rename_tag_settings_section',
        __( 'Tag Settings', 'divi-projects-cpt-rename' ),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_singular_name',
        __( 'Tag Singular Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_tag_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_plural_name',
        __( 'Tag Plural Name', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_tag_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_slug',
        __( 'Tag Slug', 'divi-projects-cpt-rename' ),
        'divi_projects_cpt_rename_tag_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    // Hook into the settings update process
    add_action( 'update_option_divi_projects_cpt_rename_settings', 'divi_projects_cpt_rename_flush_permalinks_after_settings_update', 10, 2 );

    // Function to flush permalinks
    function divi_projects_cpt_rename_flush_permalinks_after_settings_update($old_value, $new_value) {
        // Check if the values have changed to avoid unnecessary flushes
        if ( $old_value !== $new_value ) {
            flush_rewrite_rules();
        }
    }

    // Initialize the plugin settings
    function divi_projects_cpt_rename_init() {
        // Register the settings
        register_setting( 'divi_projects_cpt_rename_settings_group', 'divi_projects_cpt_rename_settings' );
    }
    add_action( 'admin_init', 'divi_projects_cpt_rename_init' );
}

/**
 * Converts a field key into a human-friendly label.
 * Required by Sanitize settings function.
 *
 * @param string $field_key The field key to be transformed.
 * @return string The transformed, human-friendly field name.
 */
function divi_projects_cpt_rename_humanize_field_name( $field_key ) {
    // Replace underscores with spaces
    $human_readable = str_replace( '_', ' ', $field_key );
    
    // Capitalize each word
    $human_readable = ucwords( $human_readable );
    
    return $human_readable;
}

/**
 * Sanitize settings for the Divi Projects CPT Rename plugin.
 *
 * This function checks user capabilities, verifies the nonce for security, ensures that required fields are not empty,
 * and sanitizes the provided settings values before saving them.
 *
 * @param array $settings The array of settings fields to be sanitized.
 * @return array The array of sanitized settings.
 */
function divi_projects_cpt_rename_sanitize_settings( $settings ) {

    /**
     * Check if the current user has the capability to manage options.
     * If not, terminate the script with an error message.
     */
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to perform this action.', 'divi-projects-cpt-rename' ) );
    }

    /**
     * Verify the nonce to protect against Cross-Site Request Forgery (CSRF) attacks.
     * If the nonce is invalid, terminate the script with an error message.
     */
    if ( ! isset( $_POST['divi_projects_cpt_rename_options_nonce'] ) || 
         ! wp_verify_nonce( $_POST['divi_projects_cpt_rename_options_nonce'], 'divi_projects_cpt_rename_options_verify' ) ) {
        wp_die( __( 'Nonce verification failed.', 'divi-projects-cpt-rename' ) );
    }

    // Initialize the array for sanitized settings
    $sanitized_settings = array();

    /**
     * Define the list of required fields.
     * These fields must not be empty. If any required field is empty, an error message will be displayed.
     */
    $required_fields = array(
        'singular_name',
        'plural_name',
        'slug',
        'menu_icon',
        'category_singular_name',
        'category_plural_name',
        'category_slug',
        'tag_singular_name',
        'tag_plural_name',
        'tag_slug'
    );

    /**
     * Loop through each setting field, check if the field is required and if it's empty.
     * If a required field is empty, add an error message and skip sanitization for that field.
     * Otherwise, sanitize the field value based on its type.
     */
    foreach ( $settings as $key => $value ) {

        // Check if the field is required and if it's empty
        if ( in_array( $key, $required_fields, true ) && empty( $value ) ) {
            add_settings_error(
                'divi_projects_cpt_rename_settings', // Setting slug
                $key . '_error', // Error code
                sprintf( 
                    /* translators: %s: field name */
                    __( 'The %s field cannot be empty.', 'divi-projects-cpt-rename' ), 
                    divi_projects_cpt_rename_humanize_field_name( $key )
                ), // Error message
                'error' // Error type
            );
            // Optionally, you can set a default value or retain the previous value
            // For this example, we'll skip sanitization for empty required fields
            continue;
        }

        // Sanitize setting values based on the field key
        switch ( $key ) {
            case 'singular_name':
            case 'plural_name':
            case 'category_singular_name':
            case 'category_plural_name':
            case 'tag_singular_name':
            case 'tag_plural_name':
                $sanitized_settings[ $key ] = sanitize_text_field( $value );
                break;
            case 'slug':
            case 'category_slug':
            case 'tag_slug':
                // Sanitize the slugs to ensure they are lowercase and use dashes
                $sanitized_settings[ $key ] = sanitize_title_with_dashes( $value );
                break;
            case 'menu_icon':
                // Additional validation if needed
                $sanitized_settings[ $key ] = esc_attr( $value );
                break;
            default:
                // Handle other settings as needed
                $sanitized_settings[ $key ] = wp_kses_post( $value );
                break;
        }
    }

    // Return the sanitized settings
    return $sanitized_settings;
}


// Singular Name
function divi_projects_cpt_rename_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[singular_name]" value="<?php echo isset( $options['singular_name'] ) ? esc_attr( $options['singular_name'] ) : 'Project'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Project', 'divi-projects-cpt-rename' ); ?></kbd></p>
    <?php
}

// Plural Name
function divi_projects_cpt_rename_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[plural_name]" value="<?php echo isset( $options['plural_name'] ) ? esc_attr( $options['plural_name'] ) : 'Projects'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Projects', 'divi-projects-cpt-rename' ); ?></kbd></p>
    <?php
}

// Slug
function divi_projects_cpt_rename_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    $slug = isset( $options['slug'] ) ? $options['slug'] : 'project';

    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[slug]" value="<?php echo esc_attr( $slug ); ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'project', 'divi-projects-cpt-rename' ); ?></kbd></p>


    <?php
}


// Menu Icon
function divi_projects_cpt_rename_menu_icon_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    $selected_icon = isset( $options['menu_icon'] ) ? esc_attr( $options['menu_icon'] ) : 'dashicons-portfolio';

    // Whitelisted menu icon values
    $menu_icons = array(
        __( 'Admin Menu', 'divi-projects-cpt-rename' ) => array(
            'dashicons-admin-appearance'            => __( 'appearance', 'divi-projects-cpt-rename' ),
            'dashicons-admin-collapse'              => __( 'collapse', 'divi-projects-cpt-rename' ),
            'dashicons-admin-comments'              => __( 'comments', 'divi-projects-cpt-rename' ),
            'dashicons-admin-customizer'            => __( 'customizer', 'divi-projects-cpt-rename' ),
            'dashicons-dashboard'                   => __( 'dashboard', 'divi-projects-cpt-rename' ),
            'dashicons-filter'                      => __( 'filter', 'divi-projects-cpt-rename' ),
            'dashicons-admin-generic'               => __( 'generic', 'divi-projects-cpt-rename' ),
            'dashicons-admin-home'                  => __( 'home', 'divi-projects-cpt-rename' ),
            'dashicons-admin-links'                 => __( 'links', 'divi-projects-cpt-rename' ),
            'dashicons-admin-media'                 => __( 'media', 'divi-projects-cpt-rename' ),
            'dashicons-menu'                        => __( 'menu', 'divi-projects-cpt-rename' ),
            'dashicons-menu-alt'                    => __( 'menu (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-menu-alt2'                   => __( 'menu (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-menu-alt3'                   => __( 'menu (alt 3)', 'divi-projects-cpt-rename' ),
            'dashicons-admin-multisite'             => __( 'multisite', 'divi-projects-cpt-rename' ),
            'dashicons-admin-network'               => __( 'network', 'divi-projects-cpt-rename' ),
            'dashicons-admin-page'                  => __( 'page', 'divi-projects-cpt-rename' ),
            'dashicons-admin-plugins'               => __( 'plugins', 'divi-projects-cpt-rename' ),
            'dashicons-plugins-checked'             => __( 'plugins checked', 'divi-projects-cpt-rename' ),
            'dashicons-admin-post'                  => __( 'post', 'divi-projects-cpt-rename' ),
            'dashicons-admin-settings'              => __( 'settings', 'divi-projects-cpt-rename' ),
            'dashicons-admin-site'                  => __( 'site', 'divi-projects-cpt-rename' ),
            'dashicons-admin-site-alt'              => __( 'site (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-admin-site-alt2'             => __( 'site (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-admin-site-alt3'             => __( 'site (alt 3)', 'divi-projects-cpt-rename' ),
            'dashicons-admin-tools'                 => __( 'tools', 'divi-projects-cpt-rename' ),
            'dashicons-admin-users'                 => __( 'users', 'divi-projects-cpt-rename' ),
        ),
        __( 'Block Editor', 'divi-projects-cpt-rename' ) => array(
            'dashicons-align-full-width' => __('align full width', 'divi-projects-cpt-rename' ),
            'dashicons-align-pull-left'  => __('align pull left', 'divi-projects-cpt-rename' ),
            'dashicons-align-pull-right' => __('align pull right', 'divi-projects-cpt-rename' ),
            'dashicons-align-wide'       => __('align wide', 'divi-projects-cpt-rename' ),
            'dashicons-block-default'    => __('block default', 'divi-projects-cpt-rename' ),
            'dashicons-button'           => __('button', 'divi-projects-cpt-rename' ),
            'dashicons-cloud-saved'      => __('cloud saved', 'divi-projects-cpt-rename' ),
            'dashicons-cloud-upload'     => __('cloud upload', 'divi-projects-cpt-rename' ),
            'dashicons-columns'          => __('columns', 'divi-projects-cpt-rename' ),
            'dashicons-cover-image'      => __('cover image', 'divi-projects-cpt-rename' ),
            'dashicons-ellipsis'         => __('ellipsis', 'divi-projects-cpt-rename' ),
            'dashicons-embed-audio'      => __('embed audio', 'divi-projects-cpt-rename' ),
            'dashicons-embed-generic'    => __('embed generic', 'divi-projects-cpt-rename' ),
            'dashicons-embed-photo'      => __('embed photo', 'divi-projects-cpt-rename' ),
            'dashicons-embed-post'       => __('embed post', 'divi-projects-cpt-rename' ),
            'dashicons-embed-video'      => __('embed video', 'divi-projects-cpt-rename' ),
            'dashicons-exit'             => __('exit', 'divi-projects-cpt-rename' ),
            'dashicons-heading'          => __('heading', 'divi-projects-cpt-rename' ),
            'dashicons-html'             => __('HTML', 'divi-projects-cpt-rename' ),
            'dashicons-info-outline'     => __('info (outline)', 'divi-projects-cpt-rename' ),
            'dashicons-insert'           => __('insert', 'divi-projects-cpt-rename' ),
            'dashicons-insert-after'     => __('insert after', 'divi-projects-cpt-rename' ),
            'dashicons-insert-before'    => __('insert before', 'divi-projects-cpt-rename' ),
            'dashicons-remove'           => __('remove', 'divi-projects-cpt-rename' ),
            'dashicons-saved'            => __('saved (tick, check)', 'divi-projects-cpt-rename' ),
            'dashicons-shortcode'        => __('shortcode', 'divi-projects-cpt-rename' ),
            'dashicons-table-col-after'  => __('table column after', 'divi-projects-cpt-rename' ),
            'dashicons-table-col-before' => __('table column before', 'divi-projects-cpt-rename' ),
            'dashicons-table-col-delete' => __('table column delete', 'divi-projects-cpt-rename' ),
            'dashicons-table-row-after'  => __('table row after', 'divi-projects-cpt-rename' ),
            'dashicons-table-row-before' => __('table row before', 'divi-projects-cpt-rename' ),
            'dashicons-table-row-delete' => __('table row delete', 'divi-projects-cpt-rename' ),
        ),
        __( 'Buddicons', 'divi-projects-cpt-rename' ) => array(
            'dashicons-buddicons-activity'        => __('activity', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-bbpress-logo'    => __('bbpress logo', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-buddypress-logo' => __('buddypress logo', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-community'       => __('community', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-forums'          => __('forums', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-friends'         => __('friends', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-groups'          => __('groups', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-pm'              => __('pm (personal message)', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-replies'         => __('replies', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-topics'          => __('topics', 'divi-projects-cpt-rename' ),
            'dashicons-buddicons-tracking'        => __('tracking', 'divi-projects-cpt-rename' ),
        ),
        __( 'Databases', 'divi-projects-cpt-rename' ) => array(
            'dashicons-database'        => __( 'database', 'divi-projects-cpt-rename' ),
            'dashicons-database-add'    => __( 'database add', 'divi-projects-cpt-rename' ),
            'dashicons-database-remove' => __( 'database remove', 'divi-projects-cpt-rename' ),
            'dashicons-database-export' => __( 'database export', 'divi-projects-cpt-rename' ),
            'dashicons-database-import' => __( 'database import', 'divi-projects-cpt-rename' ),
            'dashicons-database-view'   => __( 'database view', 'divi-projects-cpt-rename' ),
        ),
        __( 'Image Editing', 'divi-projects-cpt-rename' ) => array(
            'dashicons-image-crop'            => __( 'image crop', 'divi-projects-cpt-rename' ),
            'dashicons-image-filter'          => __( 'filter', 'divi-projects-cpt-rename' ),
            'dashicons-image-flip-horizontal' => __( 'flip horizontal', 'divi-projects-cpt-rename' ),
            'dashicons-image-flip-vertical'   => __( 'flip vertical', 'divi-projects-cpt-rename' ),
            'dashicons-redo'                  => __( 'redo', 'divi-projects-cpt-rename' ),
            'dashicons-image-rotate'          => __( 'rotate', 'divi-projects-cpt-rename' ),
            'dashicons-image-rotate-left'     => __( 'rotate left', 'divi-projects-cpt-rename' ),
            'dashicons-image-rotate-right'    => __( 'rotate right', 'divi-projects-cpt-rename' ),
            'dashicons-undo'                  => __( 'undo', 'divi-projects-cpt-rename' ),
        ),
        __( 'Media', 'divi-projects-cpt-rename' ) => array(
            'dashicons-controls-skipback'    => __('skip back', 'divi-projects-cpt-rename' ),
            'dashicons-controls-back'        => __('back', 'divi-projects-cpt-rename' ),
            'dashicons-controls-play'        => __('play', 'divi-projects-cpt-rename' ),
            'dashicons-controls-pause'       => __('pause', 'divi-projects-cpt-rename' ),
            'dashicons-controls-forward'     => __('forward', 'divi-projects-cpt-rename' ),
            'dashicons-controls-skipforward' => __('skip forward', 'divi-projects-cpt-rename' ),
            'dashicons-controls-repeat'      => __('repeat', 'divi-projects-cpt-rename' ),
            'dashicons-controls-volumeoff'   => __('volume off', 'divi-projects-cpt-rename' ),
            'dashicons-controls-volumeon'    => __('volume on', 'divi-projects-cpt-rename' ),
            'dashicons-media-archive'        => __('archive', 'divi-projects-cpt-rename' ),
            'dashicons-media-audio'          => __('audio', 'divi-projects-cpt-rename' ),
            'dashicons-media-code'           => __('code', 'divi-projects-cpt-rename' ),
            'dashicons-media-default'        => __('default', 'divi-projects-cpt-rename' ),
            'dashicons-media-document'       => __('document', 'divi-projects-cpt-rename' ),
            'dashicons-media-interactive'    => __('interactive', 'divi-projects-cpt-rename' ),
            'dashicons-playlist-audio'       => __('playlist audio', 'divi-projects-cpt-rename' ),
            'dashicons-playlist-video'       => __('playlist video', 'divi-projects-cpt-rename' ),
            'dashicons-media-spreadsheet'    => __('spreadsheet', 'divi-projects-cpt-rename' ),
            'dashicons-media-text'           => __('text', 'divi-projects-cpt-rename' ),
            'dashicons-media-video'          => __('video', 'divi-projects-cpt-rename' ),
        ),
        __( 'Miscellaneous', 'divi-projects-cpt-rename' ) => array(
            'dashicons-airplane'            => __( 'airplane', 'divi-projects-cpt-rename' ),
            'dashicons-album'               => __( 'album', 'divi-projects-cpt-rename' ),
            'dashicons-analytics'           => __( 'analytics', 'divi-projects-cpt-rename' ),
            'dashicons-awards'              => __( 'awards', 'divi-projects-cpt-rename' ),
            'dashicons-backup'              => __( 'backup', 'divi-projects-cpt-rename' ),
            'dashicons-bank'                => __( 'bank', 'divi-projects-cpt-rename' ),
            'dashicons-beer'                => __( 'beer', 'divi-projects-cpt-rename' ),
            'dashicons-book'                => __( 'book', 'divi-projects-cpt-rename' ),
            'dashicons-book-alt'            => __( 'book (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-building'            => __( 'building', 'divi-projects-cpt-rename' ),
            'dashicons-businessman'         => __( 'businessman', 'divi-projects-cpt-rename' ),
            'dashicons-businessperson'      => __( 'businessperson', 'divi-projects-cpt-rename' ),
            'dashicons-businesswoman'       => __( 'businesswoman', 'divi-projects-cpt-rename' ),
            'dashicons-calculator'          => __( 'calculator', 'divi-projects-cpt-rename' ),
            'dashicons-car'                 => __( 'car', 'divi-projects-cpt-rename' ),
            'dashicons-carrot'              => __( 'carrot', 'divi-projects-cpt-rename' ),
            'dashicons-chart-area'          => __( 'chart area', 'divi-projects-cpt-rename' ),
            'dashicons-chart-bar'           => __( 'chart bar', 'divi-projects-cpt-rename' ),
            'dashicons-chart-line'          => __( 'chart line', 'divi-projects-cpt-rename' ),
            'dashicons-chart-pie'           => __( 'chart pie', 'divi-projects-cpt-rename' ),
            'dashicons-clock'               => __( 'clock', 'divi-projects-cpt-rename' ),
            'dashicons-coffee'              => __( 'coffee', 'divi-projects-cpt-rename' ),
            'dashicons-color-picker'        => __( 'color picker', 'divi-projects-cpt-rename' ),
            'dashicons-desktop'             => __( 'desktop', 'divi-projects-cpt-rename' ),
            'dashicons-download'            => __( 'download', 'divi-projects-cpt-rename' ),
            'dashicons-drumstick'           => __( 'drumstick', 'divi-projects-cpt-rename' ),
            'dashicons-edit-large'          => __( 'edit large', 'divi-projects-cpt-rename' ),
            'dashicons-edit-page'           => __( 'edit page', 'divi-projects-cpt-rename' ),
            'dashicons-food'                => __( 'food', 'divi-projects-cpt-rename' ),
            'dashicons-forms'               => __( 'forms', 'divi-projects-cpt-rename' ),
            'dashicons-fullscreen-alt'      => __( 'fullscreen (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-fullscreen-exit-alt' => __( 'fullscreen exit (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-games'               => __( 'games', 'divi-projects-cpt-rename' ),
            'dashicons-groups'              => __( 'groups', 'divi-projects-cpt-rename' ),
            'dashicons-hourglass'           => __( 'hourglass', 'divi-projects-cpt-rename' ),
            'dashicons-id'                  => __( 'id', 'divi-projects-cpt-rename' ),
            'dashicons-id-alt'              => __( 'id (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-index-card'          => __( 'index card', 'divi-projects-cpt-rename' ),
            'dashicons-laptop'              => __( 'laptop', 'divi-projects-cpt-rename' ),
            'dashicons-layout'              => __( 'layout', 'divi-projects-cpt-rename' ),
            'dashicons-lightbulb'           => __( 'lightbulb', 'divi-projects-cpt-rename' ),
            'dashicons-location'            => __( 'location', 'divi-projects-cpt-rename' ),
            'dashicons-location-alt'        => __( 'location (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-microphone'          => __( 'microphone', 'divi-projects-cpt-rename' ),
            'dashicons-money'               => __( 'money', 'divi-projects-cpt-rename' ),
            'dashicons-money-alt'           => __( 'money (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-open-folder'         => __( 'open folder', 'divi-projects-cpt-rename' ),
            'dashicons-palmtree'            => __( 'palm tree', 'divi-projects-cpt-rename' ),
            'dashicons-paperclip'           => __( 'paperclip', 'divi-projects-cpt-rename' ),
            'dashicons-pdf'                 => __( 'pdf', 'divi-projects-cpt-rename' ),
            'dashicons-pets'                => __( 'pets', 'divi-projects-cpt-rename' ),
            'dashicons-phone'               => __( 'phone', 'divi-projects-cpt-rename' ),
            'dashicons-portfolio'           => __( 'portfolio', 'divi-projects-cpt-rename' ),
            'dashicons-printer'             => __( 'printer', 'divi-projects-cpt-rename' ),
            'dashicons-privacy'             => __( 'privacy', 'divi-projects-cpt-rename' ),
            'dashicons-products'            => __( 'products', 'divi-projects-cpt-rename' ),
            'dashicons-search'              => __( 'search', 'divi-projects-cpt-rename' ),
            'dashicons-shield'              => __( 'shield', 'divi-projects-cpt-rename' ),
            'dashicons-shield-alt'          => __( 'shield (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-slides'              => __( 'slides', 'divi-projects-cpt-rename' ),
            'dashicons-smartphone'          => __( 'smartphone', 'divi-projects-cpt-rename' ),
            'dashicons-smiley'              => __( 'smiley', 'divi-projects-cpt-rename' ),
            'dashicons-sos'                 => __( 'sos', 'divi-projects-cpt-rename' ),
            'dashicons-store'               => __( 'store', 'divi-projects-cpt-rename' ),
            'dashicons-superhero'           => __( 'superhero', 'divi-projects-cpt-rename' ),
            'dashicons-superhero-alt'       => __( 'superhero (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-tablet'              => __( 'tablet', 'divi-projects-cpt-rename' ),
            'dashicons-testimonial'         => __( 'testimonial', 'divi-projects-cpt-rename' ),
            'dashicons-text-page'           => __( 'text page', 'divi-projects-cpt-rename' ),
            'dashicons-thumbs-down'         => __( 'thumbs down', 'divi-projects-cpt-rename' ),
            'dashicons-thumbs-up'           => __( 'thumbs up', 'divi-projects-cpt-rename' ),
            'dashicons-tickets-alt'         => __( 'tickets (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-upload'              => __( 'upload', 'divi-projects-cpt-rename' ),
            'dashicons-vault'               => __( 'vault', 'divi-projects-cpt-rename' ),
        ),
        __( 'Notifications', 'divi-projects-cpt-rename' ) => array(
            'dashicons-bell'        => __( 'bell', 'divi-projects-cpt-rename' ),
            'dashicons-yes'         => __( 'yes', 'divi-projects-cpt-rename' ),
            'dashicons-yes-alt'     => __( 'yes (alt) ', 'divi-projects-cpt-rename' ),
            'dashicons-no'          => __( 'no', 'divi-projects-cpt-rename' ),
            'dashicons-no-alt'      => __( 'no (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-plus'        => __( 'plus', 'divi-projects-cpt-rename' ),
            'dashicons-plus-alt'    => __( 'plus (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-plus-alt2'   => __( 'plus (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-minus'       => __( 'minus', 'divi-projects-cpt-rename' ),
            'dashicons-dismiss'     => __( 'dismiss', 'divi-projects-cpt-rename' ),
            'dashicons-marker'      => __( 'marker', 'divi-projects-cpt-rename' ),
            'dashicons-star-filled' => __( 'star filled', 'divi-projects-cpt-rename' ),
            'dashicons-star-half'   => __( 'star half', 'divi-projects-cpt-rename' ),
            'dashicons-star-empty'  => __( 'star empty', 'divi-projects-cpt-rename' ),
            'dashicons-flag'        => __( 'flag', 'divi-projects-cpt-rename' ),
            'dashicons-warning'     => __( 'warning', 'divi-projects-cpt-rename' ),
        ),
        __( 'Post Formats', 'divi-projects-cpt-rename' ) => array(
            'dashicons-format-aside'   => __('aside', 'divi-projects-cpt-rename' ),
            'dashicons-format-audio'   => __('audio', 'divi-projects-cpt-rename' ),
            'dashicons-camera'         => __('camera', 'divi-projects-cpt-rename' ),
            'dashicons-camera-alt'     => __('camera (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-format-chat'    => __('chat', 'divi-projects-cpt-rename' ),
            'dashicons-format-gallery' => __('gallery', 'divi-projects-cpt-rename' ),
            'dashicons-format-image'   => __('image', 'divi-projects-cpt-rename' ),
            'dashicons-images-alt'     => __('images (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-images-alt2'    => __('images (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-format-quote'   => __('quote', 'divi-projects-cpt-rename' ),
            'dashicons-format-status'  => __('status', 'divi-projects-cpt-rename' ),
            'dashicons-format-video'   => __('video', 'divi-projects-cpt-rename' ),
            'dashicons-video-alt'      => __('video (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-video-alt2'     => __('video (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-video-alt3'     => __('video (alt 3)', 'divi-projects-cpt-rename' ),
        ),
        __( 'Posts Screen', 'divi-projects-cpt-rename' ) => array(
            'dashicons-align-center' => __('align center', 'divi-projects-cpt-rename' ),
            'dashicons-align-left'   => __('align left', 'divi-projects-cpt-rename' ),
            'dashicons-align-none'   => __('align none', 'divi-projects-cpt-rename' ),
            'dashicons-align-right'  => __('align right', 'divi-projects-cpt-rename' ),
            'dashicons-calendar'     => __('calendar', 'divi-projects-cpt-rename' ),
            'dashicons-calendar-alt' => __('calendar (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-edit'         => __('edit', 'divi-projects-cpt-rename' ),
            'dashicons-hidden'       => __('hidden', 'divi-projects-cpt-rename' ),
            'dashicons-lock'         => __('lock', 'divi-projects-cpt-rename' ),
            'dashicons-post-status'  => __('post status', 'divi-projects-cpt-rename' ),
            'dashicons-sticky'       => __('sticky', 'divi-projects-cpt-rename' ),
            'dashicons-trash'        => __('trash', 'divi-projects-cpt-rename' ),
            'dashicons-unlock'       => __('unlock', 'divi-projects-cpt-rename' ),
            'dashicons-visibility'   => __('visibility', 'divi-projects-cpt-rename' ),
        ),
        __( 'Products', 'divi-projects-cpt-rename' ) => array(
            'dashicons-cart'          => __('cart', 'divi-projects-cpt-rename' ),
            'dashicons-cloud'         => __('cloud', 'divi-projects-cpt-rename' ),
            'dashicons-feedback'      => __('feedback', 'divi-projects-cpt-rename' ),
            'dashicons-info'          => __('info', 'divi-projects-cpt-rename' ),
            'dashicons-pressthis'     => __('pressthis', 'divi-projects-cpt-rename' ),
            'dashicons-screenoptions' => __('screen options', 'divi-projects-cpt-rename' ),
            'dashicons-translation'   => __('translation', 'divi-projects-cpt-rename' ),
            'dashicons-update'        => __('update', 'divi-projects-cpt-rename' ),
            'dashicons-update-alt'    => __('update (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-wordpress'     => __('wordpress', 'divi-projects-cpt-rename' ),
            'dashicons-wordpress-alt' => __('wordpress (alt)', 'divi-projects-cpt-rename' ),
        ),
        __( 'Sorting', 'divi-projects-cpt-rename' ) => array(
            'dashicons-arrow-down'       => __('arrow down', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-down-alt'   => __('arrow down (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-down-alt2'  => __('arrow down (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-left'       => __('arrow left', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-left-alt'   => __('arrow left (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-left-alt2'  => __('arrow left (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-right'      => __('arrow right', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-right-alt'  => __('arrow right (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-right-alt2' => __('arrow right (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-up'         => __('arrow up', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-up-alt'     => __('arrow up (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-arrow-up-alt2'    => __('arrow up (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-excerpt-view'     => __('excerpt view', 'divi-projects-cpt-rename' ),
            'dashicons-external'         => __('external', 'divi-projects-cpt-rename' ),
            'dashicons-grid-view'        => __('grid view', 'divi-projects-cpt-rename' ),
            'dashicons-leftright'        => __('left right', 'divi-projects-cpt-rename' ),
            'dashicons-list-view'        => __('list view', 'divi-projects-cpt-rename' ),
            'dashicons-move'             => __('move', 'divi-projects-cpt-rename' ),
            'dashicons-randomize'        => __('randomize', 'divi-projects-cpt-rename' ),
            'dashicons-sort'             => __('sort', 'divi-projects-cpt-rename' ),
        ),
        __( 'Social', 'divi-projects-cpt-rename' ) => array(
            'dashicons-amazon'       => __('amazon', 'divi-projects-cpt-rename' ),
            'dashicons-email'        => __('email', 'divi-projects-cpt-rename' ),
            'dashicons-email-alt'    => __('email (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-email-alt2'   => __('email (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-facebook'     => __('facebook', 'divi-projects-cpt-rename' ),
            'dashicons-facebook-alt' => __('facebook (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-google'       => __('google', 'divi-projects-cpt-rename' ),
            'dashicons-instagram'    => __('instagram', 'divi-projects-cpt-rename' ),
            'dashicons-linkedin'     => __('linkedin', 'divi-projects-cpt-rename' ),
            'dashicons-networking'   => __('networking', 'divi-projects-cpt-rename' ),
            'dashicons-pinterest'    => __('pinterest', 'divi-projects-cpt-rename' ),
            'dashicons-podio'        => __('podio', 'divi-projects-cpt-rename' ),
            'dashicons-reddit'       => __('reddit', 'divi-projects-cpt-rename' ),
            'dashicons-rss'          => __('rss', 'divi-projects-cpt-rename' ),
            'dashicons-share'        => __('share', 'divi-projects-cpt-rename' ),
            'dashicons-share-alt'    => __('share (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-share-alt2'   => __('share (alt 2)', 'divi-projects-cpt-rename' ),
            'dashicons-spotify'      => __('spotify', 'divi-projects-cpt-rename' ),
            'dashicons-twitch'       => __('twitch', 'divi-projects-cpt-rename' ),
            'dashicons-twitter'      => __('twitter', 'divi-projects-cpt-rename' ),
            'dashicons-twitter-alt'  => __('twitter (alt)', 'divi-projects-cpt-rename' ),
            'dashicons-whatsapp'     => __('whatsapp', 'divi-projects-cpt-rename' ),
            'dashicons-xing'         => __('xing', 'divi-projects-cpt-rename' ),
            'dashicons-youtube'      => __('youtube', 'divi-projects-cpt-rename' ),
        ),
        __( 'Taxonomies', 'divi-projects-cpt-rename' ) => array(
            'dashicons-tag'      => __('tag', 'divi-projects-cpt-rename' ),
            'dashicons-category' => __('category', 'divi-projects-cpt-rename' ),
        ),
        __( 'TinyMCE', 'divi-projects-cpt-rename' ) => array(
            'dashicons-editor-aligncenter'      => __( 'align center', 'divi-projects-cpt-rename' ),
            'dashicons-editor-alignleft'        => __( 'align left', 'divi-projects-cpt-rename' ),
            'dashicons-editor-alignright'       => __( 'align right', 'divi-projects-cpt-rename' ),
            'dashicons-editor-bold'             => __( 'bold', 'divi-projects-cpt-rename' ),
            'dashicons-editor-break'            => __( 'break', 'divi-projects-cpt-rename' ),
            'dashicons-editor-code'             => __( 'code', 'divi-projects-cpt-rename' ),
            'dashicons-editor-contract'         => __( 'contract', 'divi-projects-cpt-rename' ),
            'dashicons-editor-customchar'       => __( 'custom character', 'divi-projects-cpt-rename' ),
            'dashicons-editor-expand'           => __( 'expand', 'divi-projects-cpt-rename' ),
            'dashicons-editor-help'             => __( 'help', 'divi-projects-cpt-rename' ),
            'dashicons-editor-indent'           => __( 'indent', 'divi-projects-cpt-rename' ),
            'dashicons-editor-insertmore'       => __( 'insert more', 'divi-projects-cpt-rename' ),
            'dashicons-editor-italic'           => __( 'italic', 'divi-projects-cpt-rename' ),
            'dashicons-editor-justify'          => __( 'justify', 'divi-projects-cpt-rename' ),
            'dashicons-editor-kitchensink'      => __( 'kitchen sink', 'divi-projects-cpt-rename' ),
            'dashicons-editor-ltr'              => __( 'ltr (left to right)', 'divi-projects-cpt-rename' ),
            'dashicons-editor-ol'               => __( 'ordered list', 'divi-projects-cpt-rename' ),
            'dashicons-editor-ol-rtl'           => __( 'ordered list (rtl)', 'divi-projects-cpt-rename' ),
            'dashicons-editor-outdent'          => __( 'outdent', 'divi-projects-cpt-rename' ),
            'dashicons-editor-paragraph'        => __( 'paragraph', 'divi-projects-cpt-rename' ),
            'dashicons-editor-paste-text'       => __( 'paste text', 'divi-projects-cpt-rename' ),
            'dashicons-editor-paste-word'       => __( 'paste word', 'divi-projects-cpt-rename' ),
            'dashicons-editor-quote'            => __( 'quote', 'divi-projects-cpt-rename' ),
            'dashicons-editor-removeformatting' => __( 'remove formatting', 'divi-projects-cpt-rename' ),
            'dashicons-editor-rtl'              => __( 'rtl (right to left)', 'divi-projects-cpt-rename' ),
            'dashicons-editor-spellcheck'       => __( 'spellcheck', 'divi-projects-cpt-rename' ),
            'dashicons-editor-strikethrough'    => __( 'strikethrough', 'divi-projects-cpt-rename' ),
            'dashicons-editor-table'            => __( 'table', 'divi-projects-cpt-rename' ),
            'dashicons-editor-textcolor'        => __( 'textcolor', 'divi-projects-cpt-rename' ),
            'dashicons-editor-ul'               => __( 'unordered list', 'divi-projects-cpt-rename' ),
            'dashicons-editor-underline'        => __( 'underline', 'divi-projects-cpt-rename' ),
            'dashicons-editor-unlink'           => __( 'unlink', 'divi-projects-cpt-rename' ),
            'dashicons-editor-video'            => __( 'video', 'divi-projects-cpt-rename' ),
        ),
        __( 'Welcome Screen', 'divi-projects-cpt-rename' ) => array(
            'dashicons-welcome-add-page'      => __( 'add page', 'divi-projects-cpt-rename' ),
            'dashicons-welcome-comments'      => __( 'comments', 'divi-projects-cpt-rename' ),
            'dashicons-welcome-learn-more'    => __( 'learn more', 'divi-projects-cpt-rename' ),
            'dashicons-welcome-view-site'     => __( 'view site', 'divi-projects-cpt-rename' ),
            'dashicons-welcome-widgets-menus' => __( 'widgets menus', 'divi-projects-cpt-rename' ),
            'dashicons-welcome-write-blog'    => __( 'write blog', 'divi-projects-cpt-rename' ),
        ),
        __( 'Widgets', 'divi-projects-cpt-rename' ) => array(
            'dashicons-archive'  => __( 'archive', 'divi-projects-cpt-rename' ),
            'dashicons-tagcloud' => __( 'tag cloud', 'divi-projects-cpt-rename' ),
            'dashicons-text'     => __( 'text', 'divi-projects-cpt-rename' ),
        ),
        __( 'WordPress.org', 'divi-projects-cpt-rename' ) => array(
            'dashicons-art'                  => __('art', 'divi-projects-cpt-rename' ),
            'dashicons-clipboard'            => __('clipboard', 'divi-projects-cpt-rename' ),
            'dashicons-code-standards'       => __('code-standards', 'divi-projects-cpt-rename' ),
            'dashicons-hammer'               => __('hammer', 'divi-projects-cpt-rename' ),
            'dashicons-heart'                => __('heart', 'divi-projects-cpt-rename' ),
            'dashicons-megaphone'            => __('megaphone', 'divi-projects-cpt-rename' ),
            'dashicons-migrate'              => __('migrate', 'divi-projects-cpt-rename' ),
            'dashicons-nametag'              => __('nametag', 'divi-projects-cpt-rename' ),
            'dashicons-performance'          => __('performance', 'divi-projects-cpt-rename' ),
            'dashicons-rest-api'             => __('rest api', 'divi-projects-cpt-rename' ),
            'dashicons-schedule'             => __('schedule', 'divi-projects-cpt-rename' ),
            'dashicons-tickets'              => __('tickets', 'divi-projects-cpt-rename' ),
            'dashicons-tide'                 => __('tide', 'divi-projects-cpt-rename' ),
            'dashicons-universal-access'     => __('universal access ', 'divi-projects-cpt-rename' ),
            'dashicons-universal-access-alt' => __('universal access (alt)', 'divi-projects-cpt-rename' ),
        ),
    );
    ?>

<select name="divi_projects_cpt_rename_settings[menu_icon]" id="menu-icon-select">
    <?php foreach ( $menu_icons as $group => $icons ) : ?>
        <optgroup label="<?php echo esc_html__( $group, 'divi-projects-cpt-rename' ); ?>">
            <?php foreach ( $icons as $icon => $label ) : ?>
                <option value="<?php echo esc_attr( $icon ); ?>" <?php selected( $selected_icon, $icon ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select>
    <p class="description"><?php esc_html_e( 'See', 'divi-projects-cpt-rename' ); ?> <a href="https://developer.wordpress.org/resource/dashicons/#layout" target="_blank"><?php esc_html_e( 'Dashicons', 'divi-projects-cpt-rename' ); ?></a> <?php esc_html_e( '(opens new window)', 'divi-projects-cpt-rename' ); ?><br /><?php esc_html_e( "Divi's default menu icon is", 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Admin Menu &gt; Post', 'divi-projects-cpt-rename' ); ?></kbd>.</p>
    <?php
    }

// Category Singular Name
function divi_projects_cpt_rename_category_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_singular_name]" value="<?php echo isset( $options['category_singular_name'] ) ? esc_attr( $options['category_singular_name'] ) : 'Project Category'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Project Category', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'or', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Category', 'divi-projects-cpt-rename' ); ?></kbd></p>
    <?php
}

// Category Plural Name
function divi_projects_cpt_rename_category_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_plural_name]" value="<?php echo isset( $options['category_plural_name'] ) ? esc_attr( $options['category_plural_name'] ) : 'Project Categories'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Project Categories', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'or', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Categories', 'divi-projects-cpt-rename' ); ?></kbd></p>
    <?php
}

// Category Slug
function divi_projects_cpt_rename_category_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_slug]" value="<?php echo isset( $options['category_slug'] ) ? esc_attr( $options['category_slug'] ) : 'project_category'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <code><?php esc_html_e( 'project-category', 'divi-projects-cpt-rename' ); ?></code><br /><?php esc_html_e( "Divi's default category slug is", 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'project_category', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'with an underscore.', 'divi-projects-cpt-rename' ); ?></p>
    <?php
}

// Tag Singular Name
function divi_projects_cpt_rename_tag_singular_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_singular_name]" value="<?php echo isset( $options['tag_singular_name'] ) ? esc_attr( $options['tag_singular_name'] ) : 'Project Tag'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Project Tag', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'or', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Tag', 'divi-projects-cpt-rename' ); ?></kbd></p>

    <?php
}

// Tag Plural Name
function divi_projects_cpt_rename_tag_plural_name_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_plural_name]" value="<?php echo isset( $options['tag_plural_name'] ) ? esc_attr( $options['tag_plural_name'] ) : 'Project Tags'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Project Tags', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'or', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'Tags', 'divi-projects-cpt-rename' ); ?></kbd></p>
    <?php
}

// Tag Slug
function divi_projects_cpt_rename_tag_slug_render() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_slug]" value="<?php echo isset( $options['tag_slug'] ) ? esc_attr( $options['tag_slug'] ) : 'project_tag'; ?>">
    <p class="description"><?php esc_html_e( 'e.g.', 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'project-tag', 'divi-projects-cpt-rename' ); ?></kbd><br /><?php esc_html_e( "Divi's default tag slug is", 'divi-projects-cpt-rename' ); ?> <kbd><?php esc_html_e( 'project_tag', 'divi-projects-cpt-rename' ); ?></kbd> <?php esc_html_e( 'with an underscore.', 'divi-projects-cpt-rename' ); ?></p>
    <?php
}

// Display settings page
function divi_projects_cpt_rename_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        // Check user capabilities
        // User should not be able to access this plugin admin page as it is
        // listed under Settings but this will double check.
        // If the user doesn't have the capability, display an error message and exit.
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'divi-projects-cpt-rename' ) );
    }
    ?>
    <form action="options.php" method="post">

    <header class="divi-purple">
        <h1><?php _e( 'Rename Divi Projects', 'divi-projects-cpt-rename' ); ?> | <span class="plugin-version"> v<?php $plugin_data = get_plugin_data(__FILE__); $plugin_version = $plugin_data['Version']; echo esc_html($plugin_version); ?></span></h1>
        <p class="ds45"><?php _e( 'by', 'divi-projects-cpt-rename' ); ?> <a href="https://digitalshed45.co.uk/">Digital Shed45</a></p>
    </header>

        <?php
            settings_fields( 'divi_projects_cpt_rename_settings_group' );
            do_settings_sections( 'divi_projects_cpt_rename' );
            wp_nonce_field( 'divi_projects_cpt_rename_options_verify', 'divi_projects_cpt_rename_options_nonce' );
            submit_button();
        ?>
        <h2><?php _e( 'Reset to defaults', 'divi-projects-cpt-rename' ); ?></h2>
        <p class="reset"><?php _e( 'To', 'divi-projects-cpt-rename' ); ?> <strong><?php _e( 'reset', 'divi-projects-cpt-rename' ); ?></strong> <?php _e( 'this custom post type to the default Divi Project settings (1) go to the', 'divi-projects-cpt-rename' ); ?> <a href="<?php echo admin_url( 'plugins.php' ); ?>"><?php _e( 'Plugins', 'divi-projects-cpt-rename' ); ?></a> <?php _e( 'page and deactivate the', 'divi-projects-cpt-rename' ); ?> <strong><?php _e( 'Rename Divi Projects post type', 'divi-projects-cpt-rename' ); ?></strong> <?php _e( 'plugin then (2) go to', 'divi-projects-cpt-rename' ); ?> <a href="options-permalink.php" target="_blank"><?php _e( 'Settings &gt; Permalinks', 'divi-projects-cpt-rename' ); ?></a> <?php _e( 'and click the Save Changes button to flush the rewrite rules cache.', 'divi-projects-cpt-rename' ); ?></p>
    </form>
    <?php
}


// Get settings values
// Ternary operator format: (if statement is true) ? (do this) : (else, do this);
function divi_projects_cpt_rename_get_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['singular_name'] ) ? $options['singular_name'] : 'Project';
}

function divi_projects_cpt_rename_get_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['plural_name'] ) ? $options['plural_name'] : 'Projects';
}

function divi_projects_cpt_rename_get_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['slug'] ) ? $options['slug'] : 'project';
}

function divi_projects_cpt_rename_get_menu_icon() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['menu_icon'] ) ? $options['menu_icon'] : 'dashicons-portfolio';
}

function divi_projects_cpt_rename_get_category_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_singular_name'] ) ? $options['category_singular_name'] : 'Project Category';
}

function divi_projects_cpt_rename_get_category_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_plural_name'] ) ? $options['category_plural_name'] : 'Project Categories';
}

function divi_projects_cpt_rename_get_category_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['category_slug'] ) ? $options['category_slug'] : 'project_category';
}

function divi_projects_cpt_rename_get_tag_singular_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_singular_name'] ) ? $options['tag_singular_name'] : 'Project Tag';
}

function divi_projects_cpt_rename_get_tag_plural_name() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_plural_name'] ) ? $options['tag_plural_name'] : 'Project Tags';
}

function divi_projects_cpt_rename_get_tag_slug() {
    $options = get_option( 'divi_projects_cpt_rename_settings' );
    return isset( $options['tag_slug'] ) ? $options['tag_slug'] : 'project_tag';
}

// Change the Divi Projects custom post type
add_action( 'init', 'divi_projects_cpt_rename_register_new_values' );
function divi_projects_cpt_rename_register_new_values() {
    $singular_name          = divi_projects_cpt_rename_get_singular_name();
    $plural_name            = divi_projects_cpt_rename_get_plural_name();
    $slug                   = divi_projects_cpt_rename_get_slug();
    $menu_icon              = divi_projects_cpt_rename_get_menu_icon();
    $category_singular_name = divi_projects_cpt_rename_get_category_singular_name();
    $category_plural_name   = divi_projects_cpt_rename_get_category_plural_name();
    $category_slug          = divi_projects_cpt_rename_get_category_slug();
    $tag_singular_name      = divi_projects_cpt_rename_get_tag_singular_name();
    $tag_plural_name        = divi_projects_cpt_rename_get_tag_plural_name();
    $tag_slug               = divi_projects_cpt_rename_get_tag_slug();

    register_post_type( 'project', [
        'labels'            => [
            'name'          => __( $plural_name, 'divi-projects-cpt-rename' ),
            'singular_name' => __( $singular_name, 'divi-projects-cpt-rename' ),
            'add_new'       => sprintf( __( 'Add New %s', 'divi-projects-cpt-rename' ), $singular_name ),
            'add_new_item'  => sprintf( __( 'Add New %s', 'divi-projects-cpt-rename' ), $singular_name ),
            'all_items'     => sprintf( __( 'All %s', 'divi-projects-cpt-rename' ), $plural_name ),
            'edit_item'     => sprintf( __( 'Edit %s', 'divi-projects-cpt-rename' ), $singular_name ),
            'menu_name'     => __( $plural_name, 'divi-projects-cpt-rename' ),
            'new_item'      => sprintf( __( 'New %s', 'divi-projects-cpt-rename' ), $singular_name ),
            'search_items'  => sprintf( __( 'Search %s', 'divi-projects-cpt-rename' ), $plural_name ),
            'view_item'     => sprintf( __( 'View %s', 'divi-projects-cpt-rename' ), $singular_name ),
        ],
        'has_archive'       => true,
        'hierarchical'      => true,
        'menu_icon'         => $menu_icon,
        'menu_position'     => 25,
        'public'            => true,
        'rewrite'           => [
        'slug'              => $slug,
        ],
    ] );

    register_taxonomy( 'project_category', array( 'project' ), [
        'hierarchical' => true,
        'labels'                => [
            'name'              => __( $category_plural_name, 'divi-projects-cpt-rename' ),
            'singular_name'     => __( $category_singular_name, 'divi-projects-cpt-rename' ),
            'search_items'      => sprintf( __( 'Search %s', 'divi-projects-cpt-rename' ), $category_plural_name ),
            'all_items'         => sprintf( __( 'All %s', 'divi-projects-cpt-rename' ), $category_plural_name ),
            'parent_item'       => sprintf( __( 'Parent %s', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'parent_item_colon' => sprintf( __( 'Parent %s:', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'edit_item'         => sprintf( __( 'Edit %s', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'update_item'       => sprintf( __( 'Update %s', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'add_new_item'      => sprintf( __( 'Add New %s', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'new_item_name'     => sprintf( __( 'New %s Name', 'divi-projects-cpt-rename' ), $category_singular_name ),
            'menu_name'         => __( $category_plural_name, 'divi-projects-cpt-rename' ),
            'not_found'         => sprintf( __( 'You currently don\'t have any %s.', 'divi-projects-cpt-rename' ), $category_plural_name ),
        ],
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'show_in_rest'          => true,
        'rewrite'               => [
            'slug'              => $category_slug,
            'with_front'        => true,
        ],
    ]);

    register_taxonomy( 'project_tag', array('project'), [
        'hierarchical' => true,
        'labels'                => [
            'name'              => __( $tag_plural_name, 'divi-projects-cpt-rename' ),
            'singular_name'     => __( $tag_singular_name, 'divi-projects-cpt-rename' ),
            'search_items'      => sprintf( __( 'Search %s', 'divi-projects-cpt-rename' ), $tag_plural_name ),
            'all_items'         => sprintf( __( 'All %s', 'divi-projects-cpt-rename' ), $tag_plural_name ),
            'parent_item'       => sprintf( __( 'Parent %s', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'parent_item_colon' => sprintf( __( 'Parent %s:', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'edit_item'         => sprintf( __( 'Edit %s', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'update_item'       => sprintf( __( 'Update %s', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'add_new_item'      => sprintf( __( 'Add New %s', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'new_item_name'     => sprintf( __( 'New %s Name', 'divi-projects-cpt-rename' ), $tag_singular_name ),
            'menu_name'         => __( $tag_plural_name, 'divi-projects-cpt-rename' ),
            'not_found'         => sprintf( __( 'You currently don\'t have any %s.', 'divi-projects-cpt-rename' ), $tag_plural_name ),
        ],
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'show_in_rest'          => true,
        'rewrite'               => [
            'slug'              => $tag_slug,
            'with_front'        => true,
        ],
    ] );

    // Flush WordPress rewrite (permalink) rules
    flush_rewrite_rules();
}
