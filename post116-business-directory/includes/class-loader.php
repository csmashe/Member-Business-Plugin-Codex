<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

require_once __DIR__ . '/class-cpt.php';
require_once __DIR__ . '/class-meta.php';
require_once __DIR__ . '/class-rest.php';
require_once __DIR__ . '/class-block-directory.php';
require_once __DIR__ . '/class-templates.php';
require_once __DIR__ . '/class-settings.php';

class Loader {
    public static function init() {
        CPT::register();
        Meta::register();
        REST::register();
        Block_Directory::register();
        Templates::register();
        Settings::register();
    }

    public static function activate() {
        // Register first to ensure rewrites exist before flush.
        self::init();
        // Create directory page with block if missing.
        self::maybe_create_directory_page();
        // Add useful DB indexes.
        self::maybe_add_meta_indexes();
        // Capabilities to all roles.
        CPT::add_caps_to_all_roles();
        flush_rewrite_rules();
    }

    private static function maybe_create_directory_page() {
        $option_key = 'p116bd_directory_page_id';
        $page_id = (int) get_option($option_key);
        if ($page_id) {
            $p = get_post($page_id);
            if ($p && $p->post_type === 'page' && $p->post_status === 'publish') {
                return; // Option already points to a published page.
            }
        }

        // Look for an existing published page explicitly.
        $published = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'name'        => 'directory', // Matches post_name (slug)
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if (!empty($published)) {
            update_option($option_key, (int) $published[0]);
            return;
        }
        $content = '<!-- wp:p116/directory {"showFlags":true,"perPage":12} /-->';
        $page_id = wp_insert_post([
            'post_title'   => __('Business Directory', 'post116-business-directory'),
            'post_name'    => 'directory',
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => $content,
        ]);
        if ($page_id && !is_wp_error($page_id)) {
            update_option($option_key, $page_id);
        }
    }

    private static function maybe_add_meta_indexes() {
        global $wpdb;
        $table = $wpdb->postmeta;
        // Index on owners_search and city_search speeds LIKE queries.
        $indexes = [
            'p116bd_owners_search' => "(meta_key(32), meta_value(191))",
            'p116bd_city_search'   => "(meta_key(32), meta_value(191))",
        ];
        foreach ($indexes as $name => $cols) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SHOW INDEX FROM {$table} WHERE Key_name = %s",
                $name
            ));
            if (!$exists) {
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->query("CREATE INDEX {$name} ON {$table} {$cols}");
            }
        }
    }
}
