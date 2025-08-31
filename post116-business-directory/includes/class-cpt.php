<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

class CPT {
    const POST_TYPE = 'p116_business';
    const TAXONOMY  = 'p116_business_category';

    public static function register() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomy']);
        add_filter('post_type_link', [__CLASS__, 'post_type_link'], 10, 2);
        add_filter('term_link', [__CLASS__, 'term_link'], 10, 3);
        add_filter('rewrite_rules_array', [__CLASS__, 'add_rewrite_rules']);
        add_action('admin_init', [__CLASS__, 'register_admin_columns']);
        add_action('restrict_manage_posts', [__CLASS__, 'admin_filters']);
        add_filter('parse_query', [__CLASS__, 'apply_admin_filters']);
        add_filter('use_block_editor_for_post_type', [__CLASS__, 'disable_block_editor'], 10, 2);
    }

    public static function register_post_type() {
        $labels = [
            'name' => __('Businesses', 'post116-business-directory'),
            'singular_name' => __('Business', 'post116-business-directory'),
            'add_new_item' => __('Add New Business', 'post116-business-directory'),
            'edit_item' => __('Edit Business', 'post116-business-directory'),
        ];
        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => true,
            // No content editor; data via meta fields only.
            'supports' => ['title', 'thumbnail', 'revisions'],
            'rewrite' => ['slug' => 'directory', 'with_front' => false],
            // Use a business-appropriate Dashicon for the admin menu.
            'menu_icon' => 'dashicons-store',
            'map_meta_cap' => true,
            'capability_type' => [
                'p116_business',
                'p116_businesses',
            ],
        ];
        register_post_type(self::POST_TYPE, $args);
    }

    public static function register_taxonomy() {
        $labels = [
            'name' => __('Business Categories', 'post116-business-directory'),
            'singular_name' => __('Business Category', 'post116-business-directory'),
        ];
        $args = [
            'labels' => $labels,
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'directory/category',
                'with_front' => false,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_p116_business_category',
                'edit_terms'   => 'edit_p116_business_category',
                'delete_terms' => 'delete_p116_business_category',
                'assign_terms' => 'assign_p116_business_category',
            ],
        ];
        register_taxonomy(self::TAXONOMY, [self::POST_TYPE], $args);
    }

    public static function add_caps_to_all_roles() {
        $caps = [
            'read_p116_business', 'read_p116_businesses',
            'read_private_p116_businesses',
            'edit_p116_business', 'edit_p116_businesses', 'edit_others_p116_businesses', 'edit_private_p116_businesses', 'edit_published_p116_businesses',
            'publish_p116_businesses',
            'delete_p116_business', 'delete_published_p116_businesses', 'delete_others_p116_businesses', 'delete_private_p116_businesses',
            'manage_p116_business_category', 'edit_p116_business_category', 'delete_p116_business_category', 'assign_p116_business_category',
        ];
        $roles = wp_roles();
        foreach ($roles->role_objects as $role) {
            foreach ($caps as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    public static function post_type_link($permalink, $post) {
        if ($post->post_type === self::POST_TYPE) {
            return home_url('/directory/' . $post->post_name . '/');
        }
        return $permalink;
    }

    public static function term_link($termlink, $term, $taxonomy) {
        if ($taxonomy === self::TAXONOMY) {
            return home_url('/directory/category/' . $term->slug . '/');
        }
        return $termlink;
    }

    public static function add_rewrite_rules($rules) {
        // Ensure /directory and variations resolve correctly.
        $new = [];
        $new['directory/([^/]+)/?$'] = 'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]';
        $new['directory/category/([^/]+)/?$'] = 'index.php?' . self::TAXONOMY . '=$matches[1]';
        return $new + $rules;
    }

    public static function register_admin_columns() {
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [__CLASS__, 'columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [__CLASS__, 'column_content'], 10, 2);
        add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', [__CLASS__, 'sortable_columns']);
    }

    public static function columns($columns) {
        $injected = [
            'p116bd_categories' => __('Categories', 'post116-business-directory'),
            'p116bd_owners'     => __('Owners', 'post116-business-directory'),
            'p116bd_city'       => __('City', 'post116-business-directory'),
            'p116bd_phone'      => __('Phone', 'post116-business-directory'),
            'p116bd_flags'      => __('Flags', 'post116-business-directory'),
        ];
        // Place after title.
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new = array_merge($new, $injected);
            }
        }
        return $new;
    }

    public static function column_content($column, $post_id) {
        switch ($column) {
            case 'p116bd_categories':
                $terms = get_the_terms($post_id, self::TAXONOMY);
                if ($terms && !is_wp_error($terms)) {
                    echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
                }
                break;
            case 'p116bd_owners':
                $owners = get_post_meta($post_id, 'owners', true);
                if (is_array($owners) && !empty($owners)) {
                    $names = array_filter(array_map(function ($o) {
                        return isset($o['owner_name']) ? $o['owner_name'] : '';
                    }, $owners));
                    $display = array_slice($names, 0, 2);
                    $rest = max(0, count($names) - 2);
                    echo esc_html(implode(', ', $display));
                    if ($rest > 0) {
                        echo ' ' . esc_html(sprintf('+%d more', $rest));
                    }
                }
                break;
            case 'p116bd_city':
                echo esc_html(get_post_meta($post_id, 'city', true));
                break;
            case 'p116bd_phone':
                echo esc_html(self::format_phone(get_post_meta($post_id, 'business_phone', true)));
                break;
            case 'p116bd_flags':
                $flags = [];
                if (get_post_meta($post_id, 'veteran_owned', true)) $flags[] = __('Veteran', 'post116-business-directory');
                if (get_post_meta($post_id, 'sons_owned', true)) $flags[] = __('SAL', 'post116-business-directory');
                if (get_post_meta($post_id, 'auxiliary_owned', true)) $flags[] = __('Auxiliary', 'post116-business-directory');
                echo esc_html(implode(' | ', $flags));
                break;
        }
    }

    public static function sortable_columns($columns) {
        $columns['p116bd_city'] = 'p116bd_city';
        return $columns;
    }

    public static function format_phone($phone) {
        $digits = preg_replace('/\D+/', '', (string)$phone);
        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits,0,3), substr($digits,3,3), substr($digits,6));
        }
        return $phone;
    }

    public static function admin_filters() {
        global $typenow;
        if ($typenow !== self::POST_TYPE) return;
        // Category dropdown
        wp_dropdown_categories([
            'show_option_all' => __('All Categories', 'post116-business-directory'),
            'taxonomy' => self::TAXONOMY,
            'name' => self::TAXONOMY,
            'orderby' => 'name',
            'selected' => isset($_GET[self::TAXONOMY]) ? (int)$_GET[self::TAXONOMY] : 0,
            'hierarchical' => true,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => false,
        ]);
        // Flag checkboxes
        foreach (['veteran_owned' => __('Veteran','post116-business-directory'), 'sons_owned' => __('SAL','post116-business-directory'), 'auxiliary_owned' => __('Auxiliary','post116-business-directory')] as $key => $label) {
            $checked = isset($_GET[$key]) && $_GET[$key] === '1' ? 'checked' : '';
            echo '<label style="margin-left:8px"><input type="checkbox" name="' . esc_attr($key) . '" value="1" ' . $checked . '/> ' . esc_html($label) . '</label>';
        }
    }

    public static function apply_admin_filters($query) {
        global $pagenow;
        if ($pagenow !== 'edit.php' || !is_admin()) return;
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== self::POST_TYPE) return;
        $meta_query = [];
        foreach (['veteran_owned', 'sons_owned', 'auxiliary_owned'] as $flag) {
            if (!empty($_GET[$flag])) {
                $meta_query[] = ['key' => $flag, 'value' => '1'];
            }
        }
        if (!empty($meta_query)) {
            $query->query_vars['meta_query'] = $meta_query;
        }
        if (!empty($_GET[self::TAXONOMY])) {
            $query->query_vars[self::TAXONOMY] = (int)$_GET[self::TAXONOMY];
        }
    }

    public static function disable_block_editor($use, $post_type) {
        return ($post_type === self::POST_TYPE) ? false : $use;
    }
}
