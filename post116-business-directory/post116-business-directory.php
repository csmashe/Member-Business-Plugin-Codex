<?php
/**
 * Plugin Name: Post 116 Business Directory
 * Description: Business directory for American Legion Post 116 with categories, owners, search, and templates.
 * Version: 1.0.3
 * Author: Chris Smashe
 * Plugin URI: https://github.com/csmashe/Member-Business-Plugin
 * Author URI: https://excelontheweb.com/
 * Text Domain: post116-business-directory
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) { exit; }

define('P116BD_VERSION', '1.0.3');
define('P116BD_PLUGIN_FILE', __FILE__);
define('P116BD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('P116BD_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once P116BD_PLUGIN_DIR . 'includes/class-loader.php';

// Activation tasks.
register_activation_hook(__FILE__, function () {
    \P116BD\Loader::activate();
});

register_deactivation_hook(__FILE__, function () {
    // Only flush rewrites on deactivation to be safe.
    flush_rewrite_rules();
});

add_action('plugins_loaded', function () {
    load_plugin_textdomain('post116-business-directory', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Bootstrap plugin early so CPT hooks attach to `init` before it fires.
add_action('plugins_loaded', function () {
    \P116BD\Loader::init();
});
