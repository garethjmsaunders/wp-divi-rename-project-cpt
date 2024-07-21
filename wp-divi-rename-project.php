<?php
/*
 * Plugin Name:         Rename Divi Projects post type
 * Version:             2.0.0-alpha
 * Plugin URI:          https://github.com/garethjmsaunders/wp-divi-customise-project
 * Description:         Requires Divi by Elegant Themes. Rename the Divi 'Projects' post type to a user-defined name.
 * Author:              Digital Shed45 - Gareth J M Saunders
 * Author URI:          https://digitalshed45.co.uk
 * Requires at least:   5.3
 * Tested up to:        6.6
 * License:             GPL3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if plugin accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if Divi theme is active and deactivate plugin if not
function check_divi_theme_on_activation() {
    $theme = wp_get_theme();
    if ( 'Divi' !== $theme->get( 'Name' ) && 'Divi' !== $theme->get( 'Template' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'This plugin requires the Divi theme from Elegant Themes to be active. Please activate the Divi theme and try again.', 'textdomain' ), 'Plugin Activation Error', array( 'back_link' => true ) );
    }
}
register_activation_hook( __FILE__, 'check_divi_theme_on_activation' );

// Enqueue CSS
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_assets');
function enqueue_custom_admin_assets() {
    wp_enqueue_style('dashicons');
    wp_enqueue_script('custom-admin-js', plugins_url('/custom-admin.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('custom-admin-css', plugins_url('/custom-admin.css', __FILE__));
}

// Create the admin menu item as a submenu item of Settings
add_action('admin_menu', 'divi_projects_cpt_rename_add_admin_menu');
function divi_projects_cpt_rename_add_admin_menu() {
    add_options_page(
        'Divi - Rename Projects CPT Settings',   // $page_title (string)
        'Rename Divi Projects',                  // $menu_title (string)
        'manage_options',                        // $capability (string)
        'divi_projects_cpt_rename',              // $menu_slug (string)
        'divi_projects_cpt_rename_options_page', // $callback_function (callable)
        null                                     // $position (int|float)
    );
}

// Add settings link to plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'divi_projects_cpt_rename_action_links');

function divi_projects_cpt_rename_action_links($links) {
    $settings_link = '<a href="admin.php?page=divi_projects_cpt_rename">' . __('Settings') . '</a>';
    // Prepend the settings link to the existing links array
    array_unshift($links, $settings_link);
    return $links;
}

// Register the settings
add_action('admin_init', 'divi_projects_cpt_rename_settings_init');
function divi_projects_cpt_rename_settings_init() {
    register_setting('divi_projects_cpt_rename_settings_group', 'divi_projects_cpt_rename_settings', 'divi_projects_cpt_rename_sanitize_settings');

    // Custom Post Type Settings Section
    add_settings_section(
        'divi_projects_cpt_rename_cpt_settings_section',
        __('Custom Post Type Settings', 'divi_projects_cpt_rename'),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_singular_name',
        __('Singular Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_plural_name',
        __('Plural Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_slug',
        __('Slug', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_menu_icon',
        __('Menu Icon', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_menu_icon_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_cpt_settings_section'
    );

    // Category Section
    add_settings_section(
        'divi_projects_cpt_rename_category_settings_section',
        __('Category Settings', 'divi_projects_cpt_rename'),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_singular_name',
        __('Category Singular Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_category_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_plural_name',
        __('Category Plural Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_category_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_category_slug',
        __('Category Slug', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_category_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_category_settings_section'
    );

    // Tag Section
    add_settings_section(
        'divi_projects_cpt_rename_tag_settings_section',
        __('Tag Settings', 'divi_projects_cpt_rename'),
        null,
        'divi_projects_cpt_rename'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_singular_name',
        __('Tag Singular Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_tag_singular_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_plural_name',
        __('Tag Plural Name', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_tag_plural_name_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    add_settings_field(
        'divi_projects_cpt_rename_tag_slug',
        __('Tag Slug', 'divi_projects_cpt_rename'),
        'divi_projects_cpt_rename_tag_slug_render',
        'divi_projects_cpt_rename',
        'divi_projects_cpt_rename_tag_settings_section'
    );

    // Hook into the settings update process
    add_action('update_option_divi_projects_cpt_rename_settings', 'flush_permalinks_after_settings_update', 10, 2);

    // Function to flush permalinks
    function flush_permalinks_after_settings_update($old_value, $new_value) {
        // Check if the values have changed to avoid unnecessary flushes
        if ($old_value !== $new_value) {
            flush_rewrite_rules();
        }
    }

    // Initialize the plugin settings
    function divi_projects_cpt_rename_init() {
        // Your existing initialization code...

        // Register the settings
        register_setting('divi_projects_cpt_rename_settings_group', 'divi_projects_cpt_rename_settings');
    }
    add_action('admin_init', 'divi_projects_cpt_rename_init');
}

// Sanitize settings function v2.0 -- all fields
function divi_projects_cpt_rename_sanitize_settings($settings) {
    // Sanitize each setting field
    $sanitized_settings = array();

    foreach ($settings as $key => $value) {
        switch ($key) {
            case 'singular_name':
            case 'plural_name':
            case 'category_singular_name':
            case 'category_plural_name':
            case 'tag_singular_name':
            case 'tag_plural_name':
                $sanitized_settings[$key] = sanitize_text_field($value);
                break;
            case 'slug':
            case 'category_slug':
            case 'tag_slug':
                // Sanitize the slugs to ensure they are lowercase and use dashes
                $sanitized_settings[$key] = sanitize_title_with_dashes($value);
                break;
            case 'menu_icon':
                // Additional validation if needed
                $sanitized_settings[$key] = esc_attr($value);
                break;
            default:
                // Handle other settings as needed
                $sanitized_settings[$key] = wp_kses_post($value);
                break;
        }
    }

    return $sanitized_settings;
}

// Singular Name
function divi_projects_cpt_rename_singular_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[singular_name]" value="<?php echo isset($options['singular_name']) ? esc_attr($options['singular_name']) : 'Project'; ?>">
    <p class="description">e.g. <kbd>Project</kbd></p>
    <?php
}

// Plural Name
function divi_projects_cpt_rename_plural_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[plural_name]" value="<?php echo isset($options['plural_name']) ? esc_attr($options['plural_name']) : 'Projects'; ?>">
    <p class="description">e.g. <kbd>Projects</kbd></p>
    <?php
}

// Slug
function divi_projects_cpt_rename_slug_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    $slug = isset($options['slug']) ? $options['slug'] : 'project';

    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[slug]" value="<?php echo esc_attr($slug); ?>">
    <p class="description">e.g. <kbd>project</kbd></p>
    <?php
}

// Menu Icon
function divi_projects_cpt_rename_menu_icon_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    $selected_icon = isset($options['menu_icon']) ? esc_attr($options['menu_icon']) : 'dashicons-portfolio';
    
    $menu_icons = [
        'Admin Menu' => [
            'dashicons-admin-appearance'            => 'appearance',
            'dashicons-admin-collapse'              => 'collapse',
            'dashicons-admin-comments'              => 'comments',
            'dashicons-admin-customizer'            => 'customizer',
            'dashicons-dashboard'                   => 'dashboard',
            'dashicons-filter'                      => 'filter',
            'dashicons-admin-generic'               => 'generic',
            'dashicons-admin-home'                  => 'home',
            'dashicons-admin-links'                  => 'links',
            'dashicons-admin-media'                 => 'media',
            'dashicons-menu'                        => 'menu',
            'dashicons-menu-alt'                    => 'menu (alt)',
            'dashicons-menu-alt2'                   => 'menu (alt 2)',
            'dashicons-menu-alt3'                   => 'menu (alt 3)',
            'dashicons-admin-multisite'             => 'multisite',
            'dashicons-admin-network'               => 'network',
            'dashicons-admin-page'                  => 'page',
            'dashicons-admin-plugins'               => 'plugins',
            'dashicons-plugins-checked'             => 'plugins checked',
            'dashicons-admin-post'                  => 'post',
            'dashicons-admin-settings'              => 'settings',
            'dashicons-admin-site'                  => 'site',
            'dashicons-admin-site-alt'              => 'site (alt)',
            'dashicons-admin-site-alt2'             => 'site (alt 2)',
            'dashicons-admin-site-alt3'             => 'site (alt 3)',
            'dashicons-admin-tools'                 => 'tools',
            'dashicons-admin-users'                 => 'users'
            ],
        'Block Editor' => [
            'dashicons-align-full-width' => 'align full width',
            'dashicons-align-pull-left'  => 'align pull left',
            'dashicons-align-pull-right' => 'align pull right',
            'dashicons-align-wide'       => 'align wide',
            'dashicons-block-default'    => 'block default',
            'dashicons-button'           => 'button',
            'dashicons-cloud-saved'      => 'cloud saved',
            'dashicons-cloud-upload'     => 'cloud upload',
            'dashicons-columns'          => 'columns',
            'dashicons-cover-image'      => 'cover image',
            'dashicons-ellipsis'         => 'ellipsis',
            'dashicons-embed-audio'      => 'embed audio',
            'dashicons-embed-generic'    => 'embed generic',
            'dashicons-embed-photo'      => 'embed photo',
        ],
        'Buddicons' => [
            'dashicons-buddicons-activity'        => 'activity',
            'dashicons-buddicons-bbpress-logo'    => 'bbpress logo',
            'dashicons-buddicons-buddypress-logo' => 'buddypress logo',
            'dashicons-buddicons-community'       => 'community',
            'dashicons-buddicons-forums'          => 'forums',
            'dashicons-buddicons-friends'         => 'friends',
            'dashicons-buddicons-groups'          => 'groups',
            'dashicons-buddicons-pm'              => 'pm (personal message)',
            'dashicons-buddicons-replies'         => 'replies',
            'dashicons-buddicons-topics'          => 'topics',
            'dashicons-buddicons-tracking'        => 'tracking',
        ],
        'Databases' => [
            'dashicons-database'        => 'database',
            'dashicons-database-add'    => 'database add',
            'dashicons-database-remove' => 'database remove',
            'dashicons-database-export' => 'database export',
            'dashicons-database-import' => 'database import',
            'dashicons-database-view'   => 'database view',
        ],
        'Image Editing' => [
            'dashicons-image-crop'            => 'image crop',
            'dashicons-image-filter'          => 'filter',
            'dashicons-image-flip-horizontal' => 'flip horizontal',
            'dashicons-image-flip-vertical'   => 'flip vertical',
            'dashicons-redo'                  => 'redo',
            'dashicons-image-rotate'          => 'rotate',
            'dashicons-image-rotate-left'     => 'rotate left',
            'dashicons-image-rotate-right'    => 'rotate right',
            'dashicons-undo'                  => 'undo',
        ],
        'Media' => [
            'dashicons-controls-skipback'    => 'skip back',
            'dashicons-controls-back'        => 'back',
            'dashicons-controls-play'        => 'play',
            'dashicons-controls-pause'       => 'pause',
            'dashicons-controls-forward'     => 'forward',
            'dashicons-controls-skipforward' => 'skip forward',
            'dashicons-controls-repeat'      => 'repeat',
            'dashicons-controls-volumeoff'   => 'volume off',
            'dashicons-controls-volumeon'    => 'volume on',
            'dashicons-media-archive'        => 'archive',
            'dashicons-media-audio'          => 'audio',
            'dashicons-media-code'           => 'code',
            'dashicons-media-default'        => 'default',
            'dashicons-media-document'       => 'document',
            'dashicons-media-interactive'    => 'interactive',
            'dashicons-playlist-audio'       => 'playlist audio',
            'dashicons-playlist-video'       => 'playlist video',
            'dashicons-media-spreadsheet'    => 'spreadsheet',
            'dashicons-media-text'           => 'text',
            'dashicons-media-video'          => 'video',
        ],
        'Miscellaneous' => [
            'dashicons-airplane'            => 'airplane',
            'dashicons-album'               => 'album',
            'dashicons-analytics'           => 'analytics',
            'dashicons-awards'              => 'awards',
            'dashicons-backup'              => 'backup',
            'dashicons-bank'                => 'bank',
            'dashicons-beer'                => 'beer',
            'dashicons-book'                => 'book',
            'dashicons-book-alt'            => 'book (alt)',
            'dashicons-building'            => 'building',
            'dashicons-businessman'         => 'businessman',
            'dashicons-businessperson'      => 'businessperson',
            'dashicons-businesswoman'       => 'businesswoman',
            'dashicons-calculator'          => 'calculator',
            'dashicons-car'                 => 'car',
            'dashicons-carrot'              => 'carrot',
            'dashicons-chart-area'          => 'chart area',
            'dashicons-chart-bar'           => 'chart bar',
            'dashicons-chart-line'          => 'chart line',
            'dashicons-chart-pie'           => 'chart pie',
            'dashicons-clock'               => 'clock',
            'dashicons-coffee'              => 'coffee',
            'dashicons-color-picker'        => 'color picker',
            'dashicons-desktop'             => 'desktop',
            'dashicons-download'            => 'download',
            'dashicons-drumstick'           => 'drumstick',
            'dashicons-edit-large'          => 'edit large',
            'dashicons-edit-page'           => 'edit page',
            'dashicons-food'                => 'food',
            'dashicons-forms'               => 'forms',
            'dashicons-fullscreen-alt'      => 'fullscreen (alt)',
            'dashicons-fullscreen-exit-alt' => 'fullscreen exit (alt)',
            'dashicons-games'               => 'games',
            'dashicons-groups'              => 'groups',
            'dashicons-hourglass'           => 'hourglass',
            'dashicons-id'                  => 'id',
            'dashicons-id-alt'              => 'id (alt)',
            'dashicons-index-card'          => 'index card',
            'dashicons-laptop'              => 'laptop',
            'dashicons-layout'              => 'layout',
            'dashicons-lightbulb'           => 'lightbulb',
            'dashicons-location'            => 'location',
            'dashicons-location-alt'        => 'location (alt)',
            'dashicons-microphone'          => 'microphone',
            'dashicons-money'               => 'money',
            'dashicons-money-alt'           => 'money (alt)',
            'dashicons-open-folder'         => 'open folder',
            'dashicons-palmtree'            => 'palm tree',
            'dashicons-paperclip'           => 'paperclip',
            'dashicons-pdf'                 => 'pdf',
            'dashicons-pets'                => 'pets',
            'dashicons-phone'               => 'phone',
            'dashicons-portfolio'           => 'portfolio',
            'dashicons-printer'             => 'printer',
            'dashicons-privacy'             => 'privacy',
            'dashicons-products'            => 'products',
            'dashicons-search'              => 'search',
            'dashicons-shield'              => 'shield',
            'dashicons-shield-alt'          => 'shield (alt)',
            'dashicons-slides'              => 'slides',
            'dashicons-smartphone'          => 'smartphone',
            'dashicons-smiley'              => 'smiley',
            'dashicons-sos'                 => 'sos',
            'dashicons-store'               => 'store',
            'dashicons-superhero'           => 'superhero',
            'dashicons-superhero-alt'       => 'superhero (alt)',
            'dashicons-tablet'              => 'tablet',
            'dashicons-testimonial'         => 'testimonial',
            'dashicons-text-page'           => 'text page',
            'dashicons-thumbs-down'         => 'thumbs down',
            'dashicons-thumbs-up'           => 'thumbs up',
            'dashicons-tickets-alt'         => 'tickets (alt)',
            'dashicons-upload'              => 'upload',
            'dashicons-vault'               => 'vault',
        ],        
        'Notifications' => [
            'dashicons-bell'        => 'bell',
            'dashicons-yes'         => 'yes',
            'dashicons-yes-alt'     => 'yes (alt) ',
            'dashicons-no'          => 'no',
            'dashicons-no-alt'      => 'no (alt)',
            'dashicons-plus'        => 'plus',
            'dashicons-plus-alt'    => 'plus (alt)',
            'dashicons-plus-alt2'   => 'plus (alt 2)',
            'dashicons-minus'       => 'minus',
            'dashicons-dismiss'     => 'dismiss',
            'dashicons-marker'      => 'marker',
            'dashicons-star-filled' => 'star filled',
            'dashicons-star-half'   => 'star half',
            'dashicons-star-empty'  => 'star empty',
            'dashicons-flag'        => 'flag',
            'dashicons-warning'     => 'warning',
        ],        
        'Post Formats' => [
            'dashicons-format-aside'   => 'aside',
            'dashicons-format-audio'   => 'audio',
            'dashicons-camera'         => 'camera',
            'dashicons-camera-alt'     => 'camera (alt)',
            'dashicons-format-chat'    => 'chat',
            'dashicons-format-gallery' => 'gallery',
            'dashicons-format-image'   => 'image',
            'dashicons-images-alt'     => 'images (alt)',
            'dashicons-images-alt2'    => 'images (alt 2)',
            'dashicons-format-quote'   => 'quote',
            'dashicons-format-status'  => 'status',
            'dashicons-format-video'   => 'video',
            'dashicons-video-alt'      => 'video (alt)',
            'dashicons-video-alt2'     => 'video (alt 2)',
            'dashicons-video-alt3'     => 'video (alt 3)'
        ],    
        'Posts Screen' => [
            'dashicons-align-center' => 'align center',
            'dashicons-align-left'   => 'align left',
            'dashicons-align-none'   => 'align none',
            'dashicons-align-right'  => 'align right',
            'dashicons-calendar'     => 'calendar',
            'dashicons-calendar-alt' => 'calendar (alt)',
            'dashicons-edit'         => 'edit',
            'dashicons-hidden'       => 'hidden',
            'dashicons-lock'         => 'lock',
            'dashicons-post-status'  => 'post status',
            'dashicons-sticky'       => 'sticky',
            'dashicons-trash'        => 'trash',
            'dashicons-unlock'       => 'unlock',
            'dashicons-visibility'   => 'visibility',
        ],
        'Products' => [
            'dashicons-cart'          => 'cart',
            'dashicons-cloud'         => 'cloud',
            'dashicons-feedback'      => 'feedback',
            'dashicons-info'          => 'info',
            'dashicons-pressthis'     => 'pressthis',
            'dashicons-screenoptions' => 'screen options',
            'dashicons-translation'   => 'translation',
            'dashicons-update'        => 'update',
            'dashicons-update-alt'    => 'update (alt)',
            'dashicons-wordpress'     => 'wordpress',
            'dashicons-wordpress-alt' => 'wordpress (alt)',
        ],                
        'Sorting' => [
            'dashicons-arrow-down'       => 'arrow down',
            'dashicons-arrow-down-alt'   => 'arrow down (alt)',
            'dashicons-arrow-down-alt2'  => 'arrow down (alt 2)',
            'dashicons-arrow-left'       => 'arrow left',
            'dashicons-arrow-left-alt'   => 'arrow left (alt)',
            'dashicons-arrow-left-alt2'  => 'arrow left (alt 2)',
            'dashicons-arrow-right'      => 'arrow right',
            'dashicons-arrow-right-alt'  => 'arrow right (alt)',
            'dashicons-arrow-right-alt2' => 'arrow right (alt 2)',
            'dashicons-arrow-up'         => 'arrow up',
            'dashicons-arrow-up-alt'     => 'arrow up (alt)',
            'dashicons-arrow-up-alt2'    => 'arrow up (alt 2)',
            'dashicons-excerpt-view'     => 'excerpt view',
            'dashicons-external'         => 'external',
            'dashicons-grid-view'        => 'grid view',
            'dashicons-leftright'        => 'left right',
            'dashicons-list-view'        => 'list view',
            'dashicons-move'             => 'move',
            'dashicons-randomize'        => 'randomize',
            'dashicons-sort'             => 'sort',
        ],
        'Social' => [
            'dashicons-amazon'       => 'amazon',
            'dashicons-email'        => 'email',
            'dashicons-email-alt'    => 'email (alt)',
            'dashicons-email-alt2'   => 'email (alt 2)',
            'dashicons-facebook'     => 'facebook',
            'dashicons-facebook-alt' => 'facebook (alt)',
            'dashicons-google'       => 'google',
            'dashicons-instagram'    => 'instagram',
            'dashicons-linkedin'     => 'linkedin',
            'dashicons-networking'   => 'networking',
            'dashicons-pinterest'    => 'pinterest',
            'dashicons-podio'        => 'podio',
            'dashicons-reddit'       => 'reddit',
            'dashicons-rss'          => 'rss',
            'dashicons-share'        => 'share',
            'dashicons-share-alt'    => 'share (alt)',
            'dashicons-share-alt2'   => 'share (alt 2)',
            'dashicons-spotify'      => 'spotify',
            'dashicons-twitch'       => 'twitch',
            'dashicons-twitter'      => 'twitter',
            'dashicons-twitter-alt'  => 'twitter (alt)',
            'dashicons-whatsapp'     => 'whatsapp',
            'dashicons-xing'         => 'xing',
            'dashicons-youtube'      => 'youtube',
        ],
        'Taxonomies' => [
            'dashicons-tag'      => 'tag',
            'dashicons-category' => 'category',
        ],           
        'TinyMCE' => [
            'dashicons-editor-aligncenter'      => 'align center',
            'dashicons-editor-alignleft'        => 'align left',
            'dashicons-editor-alignright'       => 'align right',
            'dashicons-editor-bold'             => 'bold',
            'dashicons-editor-break'            => 'break',
            'dashicons-editor-code'             => 'code',
            'dashicons-editor-contract'         => 'contract',
            'dashicons-editor-customchar'       => 'custom character',
            'dashicons-editor-expand'           => 'expand',
            'dashicons-editor-help'             => 'help',
            'dashicons-editor-indent'           => 'indent',
            'dashicons-editor-insertmore'       => 'insert more',
            'dashicons-editor-italic'           => 'italic',
            'dashicons-editor-justify'          => 'justify',
            'dashicons-editor-kitchensink'      => 'kitchen sink',
            'dashicons-editor-ltr'              => 'ltr (left to right)',
            'dashicons-editor-ol'               => 'ordered list',
            'dashicons-editor-ol-rtl'           => 'ordered list (rtl)',
            'dashicons-editor-outdent'          => 'outdent',
            'dashicons-editor-paragraph'        => 'paragraph',
            'dashicons-editor-paste-text'       => 'paste text',
            'dashicons-editor-paste-word'       => 'paste word',
            'dashicons-editor-quote'            => 'quote',
            'dashicons-editor-removeformatting' => 'remove formatting',
            'dashicons-editor-rtl'              => 'rtl (right to left)',
            'dashicons-editor-spellcheck'       => 'spellcheck',
            'dashicons-editor-strikethrough'    => 'strikethrough',
            'dashicons-editor-table'            => 'table',
            'dashicons-editor-textcolor'        => 'textcolor',
            'dashicons-editor-ul'               => 'unordered list',
            'dashicons-editor-underline'        => 'underline',
            'dashicons-editor-unlink'           => 'unlink',
            'dashicons-editor-video'            => 'video',
        ],     
        'Welcome Screen' => [
            'dashicons-welcome-add-page'      => 'add page',
            'dashicons-welcome-comments'      => 'comments',
            'dashicons-welcome-learn-more'    => 'learn more',
            'dashicons-welcome-view-site'     => 'view site',
            'dashicons-welcome-widgets-menus' => 'widgets menus',
            'dashicons-welcome-write-blog'    => 'write blog'
        ],
        'Widgets' => [
            'dashicons-archive'  => 'archive',
            'dashicons-tagcloud' => 'tag cloud',
            'dashicons-text'     => 'text',
        ],        
        'WordPress.org' => [
            'dashicons-art'                  => 'art',
            'dashicons-clipboard'            => 'clipboard',
            'dashicons-code-standards'       => 'code-standards',
            'dashicons-hammer'               => 'hammer',
            'dashicons-heart'                => 'heart',
            'dashicons-megaphone'            => 'megaphone',
            'dashicons-migrate'              => 'migrate',
            'dashicons-nametag'              => 'nametag',
            'dashicons-performance'          => 'performance',
            'dashicons-rest-api'             => 'rest api',
            'dashicons-schedule'             => 'schedule',
            'dashicons-tickets'              => 'tickets',
            'dashicons-tide'                 => 'tide',
            'dashicons-universal-access'     => 'universal access ',
            'dashicons-universal-access-alt' => 'universal access (alt)',
        ],
    ];
    ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<select name="divi_projects_cpt_rename_settings[menu_icon]" id="menu-icon-select">
    <?php foreach ($menu_icons as $group => $icons) : ?>
        <optgroup label="<?php echo esc_attr($group); ?>">
            <?php foreach ($icons as $icon => $label) : ?>
                <option value="<?php echo esc_attr($icon); ?>" <?php selected($selected_icon, $icon); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select>
    <p class="description">See <a href="https://developer.wordpress.org/resource/dashicons/#layout" target="_blank">Dashicons</a> (opens new window)<br>Divi's default menu icon is <kbd>Admin Menu &gt; Post</kbd>.</p>
    <?php
    }

// Category Singular Name
function divi_projects_cpt_rename_category_singular_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_singular_name]" value="<?php echo isset($options['category_singular_name']) ? esc_attr($options['category_singular_name']) : 'Project Category'; ?>">
    <p class="description">e.g. <kbd>Project Category</kbd> or <kbd>Category</kbd></p>
    <?php
}

// Category Plural Name
function divi_projects_cpt_rename_category_plural_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_plural_name]" value="<?php echo isset($options['category_plural_name']) ? esc_attr($options['category_plural_name']) : 'Project Categories'; ?>">
    <p class="description">e.g. <kbd>Project Categories</kbd> or <kbd>Categories</kbd></p>
    <?php
}

// Category Slug
function divi_projects_cpt_rename_category_slug_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[category_slug]" value="<?php echo isset($options['category_slug']) ? esc_attr($options['category_slug']) : 'project_category'; ?>">
    <p class="description">e.g. <code>project-category</code><br>Divi's default category slug is <kbd>project_category</kbd> with an underscore.</p>
    <?php
}

// Tag Singular Name
function divi_projects_cpt_rename_tag_singular_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_singular_name]" value="<?php echo isset($options['tag_singular_name']) ? esc_attr($options['tag_singular_name']) : 'Project Tag'; ?>">
    <p class="description">e.g. <kbd>Project Tag</kbd> or <kbd>Tag</kbd></p>
    <?php
}

// Tag Plural Name
function divi_projects_cpt_rename_tag_plural_name_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_plural_name]" value="<?php echo isset($options['tag_plural_name']) ? esc_attr($options['tag_plural_name']) : 'Project Tags'; ?>">
    <p class="description">e.g. <kbd>Project Tags</kbd> or <kbd>Tags</kbd></p>
    <?php
}

// Tag Slug
function divi_projects_cpt_rename_tag_slug_render() {
    $options = get_option('divi_projects_cpt_rename_settings');
    ?>
    <input type="text" name="divi_projects_cpt_rename_settings[tag_slug]" value="<?php echo isset($options['tag_slug']) ? esc_attr($options['tag_slug']) : 'project_tag'; ?>">
    <p class="description">e.g. <code>project-tag</code><br>Divi's default tag slug is <kbd>project_tag</kbd> with an underscore.</p>
    <?php
}

function divi_projects_cpt_rename_options_page() {
    ?>
    <form action="options.php" method="post">
        <h1 class="divi-purple">Rename Divi Projects post type <span class="ds45"><a href="https://digitalshed45.co.uk/">Digital Shed45</a></span></h1>            
        <?php
        settings_fields('divi_projects_cpt_rename_settings_group');
        do_settings_sections('divi_projects_cpt_rename');
        submit_button();
        ?>
        <p class="description">To <strong>reset</strong> this post type to the default Project settings deactivate the <strong>Rename Divi Projects post type</strong> plugin on the <a href="<?php echo admin_url('plugins.php'); ?>">Plugins</a> page.</p>
    </form>
    <?php
}

// Get settings values
function get_singular_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['singular_name']) ? $options['singular_name'] : 'Project';
}

function get_plural_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['plural_name']) ? $options['plural_name'] : 'Projects';
}

function get_slug() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['slug']) ? $options['slug'] : 'project';
}

function get_menu_icon() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['menu_icon']) ? $options['menu_icon'] : 'dashicons-portfolio';
}

function get_category_singular_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['category_singular_name']) ? $options['category_singular_name'] : 'Project Category';
}

function get_category_plural_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['category_plural_name']) ? $options['category_plural_name'] : 'Project Categories';
}

function get_category_slug() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['category_slug']) ? $options['category_slug'] : 'project_category';
}

function get_tag_singular_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['tag_singular_name']) ? $options['tag_singular_name'] : 'Project Tag';
}

function get_tag_plural_name() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['tag_plural_name']) ? $options['tag_plural_name'] : 'Project Tags';
}

function get_tag_slug() {
    $options = get_option('divi_projects_cpt_rename_settings');
    return isset($options['tag_slug']) ? $options['tag_slug'] : 'project_tag';
}

// Change the Divi Projects custom post type
add_action('init', 'change_divi_projects_cpt');
function change_divi_projects_cpt() {
    $singular_name = get_singular_name();
    $plural_name = get_plural_name();
    $slug = get_slug();
    $menu_icon = get_menu_icon();
    $category_singular_name = get_category_singular_name();
    $category_plural_name = get_category_plural_name();
    $category_slug = get_category_slug();
    $tag_singular_name = get_tag_singular_name();
    $tag_plural_name = get_tag_plural_name();
    $tag_slug = get_tag_slug();

    register_post_type('project', [
        'labels'            => [
            'name'          => $plural_name,
            'singular_name' => $singular_name,
            'add_new'       => sprintf('Add New %s', $singular_name),
            'add_new_item'  => sprintf('Add New %s', $singular_name),
            'all_items'     => sprintf('All %s', $plural_name),
            'edit_item'     => sprintf('Edit %s', $singular_name),
            'menu_name'     => $plural_name,
            'new_item'      => sprintf('New %s', $singular_name),
            'search_items'  => sprintf('Search %s', $plural_name),
            'view_item'     => sprintf('View %s', $singular_name),
        ],
        'has_archive'       => true,
        'hierarchical'      => true,
        'menu_icon'         => $menu_icon,
        'menu_position'     => 25,
        'public'            => true,
        'rewrite'           => [
        'slug'              => $slug,
        ],
    ]);

    register_taxonomy('project_category', array('project'), [
        'hierarchical' => true,
        'labels'            => [
            'name'          => $category_plural_name,
            'singular_name' => $category_singular_name,
            'search_items'  => sprintf('Search %s', $category_plural_name),
            'all_items'     => sprintf('All %s', $category_plural_name),
            'parent_item'   => sprintf('Parent %s', $category_singular_name),
            'parent_item_colon' => sprintf('Parent %s:', $category_singular_name),
            'edit_item'     => sprintf('Edit %s', $category_singular_name),
            'update_item'   => sprintf('Update %s', $category_singular_name),
            'add_new_item'  => sprintf('Add New %s', $category_singular_name),
            'new_item_name' => sprintf('New %s Name', $category_singular_name),
            'menu_name'     => $category_plural_name,
            'not_found'     => sprintf('You currently don\'t have any %s.', $category_plural_name),
        ],
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => [
            'slug'          => $category_slug,
            'with_front'    => true,
        ],
    ]);

    register_taxonomy('project_tag', array('project'), [
        'hierarchical' => true,
        'labels'            => [
            'name'          => $tag_plural_name,
            'singular_name' => $tag_singular_name,
            'search_items'  => sprintf('Search %s', $tag_plural_name),
            'all_items'     => sprintf('All %s', $tag_plural_name),
            'parent_item'   => sprintf('Parent %s', $tag_singular_name),
            'parent_item_colon' => sprintf('Parent %s:', $tag_singular_name),
            'edit_item'     => sprintf('Edit %s', $tag_singular_name),
            'update_item'   => sprintf('Update %s', $tag_singular_name),
            'add_new_item'  => sprintf('Add New %s', $tag_singular_name),
            'new_item_name' => sprintf('New %s Name', $tag_singular_name),
            'menu_name'     => $tag_plural_name,
            'not_found'     => sprintf('You currently don\'t have any %s.', $tag_plural_name),
        ],
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => [
            'slug'          => $tag_slug,
            'with_front'    => true,
        ],
    ]);
}