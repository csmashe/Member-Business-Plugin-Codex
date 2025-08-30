<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

class Meta {
    public static function register() {
        add_action('add_meta_boxes', [__CLASS__, 'add_metaboxes']);
        add_action('save_post_' . CPT::POST_TYPE, [__CLASS__, 'save_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin']);
    }

    public static function enqueue_admin($hook) {
        global $post_type;
        if (($hook === 'post-new.php' || $hook === 'post.php') && $post_type === CPT::POST_TYPE) {
            // Needed for media uploader (logo field)
            if (function_exists('wp_enqueue_media')) { wp_enqueue_media(); }
            wp_enqueue_script('p116bd-meta', P116BD_PLUGIN_URL . 'public/js/meta.js', ['jquery'], P116BD_VERSION, true);
            wp_enqueue_style('p116bd-admin', P116BD_PLUGIN_URL . 'public/css/admin.css', [], P116BD_VERSION);
        }
    }

    public static function add_metaboxes() {
        add_meta_box('p116bd_details', __('Business Details', 'post116-business-directory'), [__CLASS__, 'render_metabox'], CPT::POST_TYPE, 'normal', 'high');
    }

    public static function render_metabox($post) {
        wp_nonce_field('p116bd_save_meta', 'p116bd_nonce');
        $get = function ($key, $default = '') use ($post) {
            $val = get_post_meta($post->ID, $key, true);
            return $val === '' ? $default : $val;
        };
        $owners = $get('owners', []);
        if (!is_array($owners)) $owners = [];
        $fields = [
            'business_phone' => $get('business_phone'),
            'business_email' => $get('business_email'),
            'website_url'    => $get('website_url'),
            'business_logo_id' => $get('business_logo_id'),
            'city'           => $get('city'),
            'address1'       => $get('address1'),
            'address2'       => $get('address2'),
            'state'          => $get('state'),
            'postal_code'    => $get('postal_code'),
            'veteran_owned'  => (bool)$get('veteran_owned'),
            'sons_owned'     => (bool)$get('sons_owned'),
            'auxiliary_owned'=> (bool)$get('auxiliary_owned'),
            'links'          => $get('links', []),
            'services_offered'=> $get('services_offered'),
            'show_in_directory'=> $get('show_in_directory', '1') !== '0',
        ];
        if (!is_array($fields['links'])) $fields['links'] = [];
        include P116BD_PLUGIN_DIR . 'templates/admin-metabox.php';
    }

    public static function save_meta($post_id, $post) {
        if (!isset($_POST['p116bd_nonce']) || !wp_verify_nonce($_POST['p116bd_nonce'], 'p116bd_save_meta')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        // Required: city
        $city = isset($_POST['p116bd_city']) ? sanitize_text_field(wp_unslash($_POST['p116bd_city'])) : '';
        if ($city === '') {
            // Prevent publish without city: set to draft and add admin notice.
            remove_action('save_post_' . CPT::POST_TYPE, [__CLASS__, 'save_meta'], 10);
            wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
            add_filter('redirect_post_location', function ($loc) {
                return add_query_arg('p116bd_city_required', '1', $loc);
            });
        }

        // Owners repeater
        $owners = [];
        $owner_names  = (array) (isset($_POST['p116bd_owner_name']) ? wp_unslash($_POST['p116bd_owner_name']) : []);
        $owner_roles  = (array) (isset($_POST['p116bd_owner_role']) ? wp_unslash($_POST['p116bd_owner_role']) : []);
        $owner_emails = (array) (isset($_POST['p116bd_owner_email']) ? wp_unslash($_POST['p116bd_owner_email']) : []);
        $owner_phones = (array) (isset($_POST['p116bd_owner_phone']) ? wp_unslash($_POST['p116bd_owner_phone']) : []);
        $owner_sites  = (array) (isset($_POST['p116bd_owner_website']) ? wp_unslash($_POST['p116bd_owner_website']) : []);
        if (!empty($owner_names)) {
            $count = count($owner_names);
            for ($i = 0; $i < $count; $i++) {
                $name = sanitize_text_field($owner_names[$i] ?? '');
                if ($name === '') continue;
                $owners[] = [
                    'owner_name'   => $name,
                    'owner_role'   => sanitize_text_field($owner_roles[$i] ?? ''),
                    'owner_email'  => sanitize_email($owner_emails[$i] ?? ''),
                    'owner_phone'  => sanitize_text_field($owner_phones[$i] ?? ''),
                    'owner_website'=> esc_url_raw($owner_sites[$i] ?? ''),
                ];
            }
        }

        update_post_meta($post_id, 'owners', $owners);

        // Links repeater
        $links = [];
        $labels = (array) (isset($_POST['p116bd_link_label']) ? wp_unslash($_POST['p116bd_link_label']) : []);
        $urls   = (array) (isset($_POST['p116bd_link_url']) ? wp_unslash($_POST['p116bd_link_url']) : []);
        if (!empty($labels)) {
            $count = count($labels);
            for ($i = 0; $i < $count; $i++) {
                $label = sanitize_text_field($labels[$i] ?? '');
                $url   = esc_url_raw($urls[$i] ?? '');
                if ($label !== '' || $url !== '') {
                    $links[] = [ 'link_label' => $label, 'link_url' => $url ];
                }
            }
        }
        update_post_meta($post_id, 'links', $links);

        // Simple fields
        $map = [
            'business_phone' => 'p116bd_business_phone',
            'business_email' => 'p116bd_business_email',
            'website_url'    => 'p116bd_website_url',
            'business_logo_id' => 'p116bd_business_logo_id',
            'city'           => 'p116bd_city',
            'address1'       => 'p116bd_address1',
            'address2'       => 'p116bd_address2',
            'state'          => 'p116bd_state',
            'postal_code'    => 'p116bd_postal_code',
            'services_offered'=> 'p116bd_services_offered',
        ];
        foreach ($map as $meta_key => $post_key) {
            $raw = isset($_POST[$post_key]) ? wp_unslash($_POST[$post_key]) : '';
            switch ($meta_key) {
                case 'business_email':
                    $val = sanitize_email($raw);
                    break;
                case 'website_url':
                    $val = esc_url_raw($raw);
                    break;
                case 'business_logo_id':
                    $val = absint($raw);
                    break;
                case 'services_offered':
                    $val = sanitize_textarea_field($raw);
                    break;
                default:
                    $val = sanitize_text_field($raw);
            }
            $val = is_string($val) ? trim($val) : $val;
            update_post_meta($post_id, $meta_key, $val);
        }

        // Flags and visibility
        update_post_meta($post_id, 'veteran_owned', !empty($_POST['p116bd_veteran_owned']) ? '1' : '0');
        update_post_meta($post_id, 'sons_owned', !empty($_POST['p116bd_sons_owned']) ? '1' : '0');
        update_post_meta($post_id, 'auxiliary_owned', !empty($_POST['p116bd_auxiliary_owned']) ? '1' : '0');
        update_post_meta($post_id, 'show_in_directory', !empty($_POST['p116bd_show_in_directory']) ? '1' : '0');

        // Search helpers
        $owners_search = strtolower(trim(implode(' ', array_map(function ($o){ return $o['owner_name'] ?? ''; }, $owners))));
        update_post_meta($post_id, 'owners_search', $owners_search);
        update_post_meta($post_id, 'city_search', strtolower($city));

        // Invalidate cache
        delete_transient('p116bd_category_groups');
    }
}
