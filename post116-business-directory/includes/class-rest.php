<?php
namespace P116BD;

use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

if (!defined('ABSPATH')) { exit; }

class REST {
    const NS = 'p116/v1';

    public static function register() {
        add_action('rest_api_init', function () {
            register_rest_route(self::NS, '/search', [
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'search'],
                'permission_callback' => '__return_true',
                'args' => [
                    'q' => ['type'=>'string','required'=>false],
                    'category' => ['type'=>'string','required'=>false],
                    'flags' => ['type'=>'array','required'=>false],
                    'per_page' => ['type'=>'integer','default'=>20],
                    'page' => ['type'=>'integer','default'=>1],
                ],
            ]);
            register_rest_route(self::NS, '/autocomplete', [
                'methods'  => 'GET',
                'callback' => [__CLASS__, 'autocomplete'],
                'permission_callback' => '__return_true',
                'args' => [
                    'q' => ['type'=>'string','required'=>true],
                    'limit' => ['type'=>'integer','default'=>8],
                ],
            ]);
            register_rest_route(self::NS, '/contact', [
                'methods'  => 'POST',
                'callback' => [__CLASS__, 'contact_business'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public static function search(WP_REST_Request $req) {
        $q = sanitize_text_field($req->get_param('q'));
        $category = sanitize_text_field($req->get_param('category'));
        $flags = (array)$req->get_param('flags');
        $per_page = max(1, min(50, (int)$req->get_param('per_page')));
        $page = max(1, (int)$req->get_param('page'));

        $meta_query = [ 'relation' => 'AND', [ 'key' => 'show_in_directory', 'value' => '1' ] ];
        if ($q) {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => 'owners_search', 'value' => strtolower($q), 'compare' => 'LIKE'],
                ['key' => 'city_search',   'value' => strtolower($q), 'compare' => 'LIKE'],
            ];
        }
        foreach (['veteran_owned','sons_owned','auxiliary_owned'] as $flag) {
            if (in_array($flag, $flags, true)) {
                $meta_query[] = ['key' => $flag, 'value' => '1'];
            }
        }

        $tax_query = [];
        if ($category) {
            $tax_query[] = [
                'taxonomy' => CPT::TAXONOMY,
                'field' => 'slug',
                'terms' => [$category],
            ];
        }

        $query = new WP_Query([
            'post_type' => CPT::POST_TYPE,
            'post_status' => 'publish',
            's' => $q,
            'meta_query' => $meta_query,
            'tax_query' => $tax_query,
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $items = array_map([__CLASS__, 'format_item'], $query->posts);
        return new WP_REST_Response([
            'items' => $items,
            'total' => (int)$query->found_posts,
            'pages' => (int)$query->max_num_pages,
        ]);
    }

    public static function contact_business(WP_REST_Request $req) {
        $post_id = absint($req->get_param('post_id'));
        if (!$post_id || get_post_type($post_id) !== CPT::POST_TYPE) {
            return new WP_REST_Response(['error' => 'Invalid business.'], 400);
        }
        // Basic rate limiting (per IP and globally) to reduce abuse
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? sanitize_text_field(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]) : (isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0');
        $ip = substr($ip, 0, 64); // clamp
        $window = 10 * 60; // 10 minutes
        $per_post_limit = 3; // max 3 per post per IP per window
        $global_limit   = 10; // max 10 per IP per window sitewide
        $k_post   = 'p116bd_rl_post_' . md5($post_id . '|' . $ip);
        $k_global = 'p116bd_rl_global_' . md5($ip);
        $c_post   = (int) get_transient($k_post);
        $c_global = (int) get_transient($k_global);
        if ($c_post >= $per_post_limit || $c_global >= $global_limit) {
            return new WP_REST_Response(['error' => 'Rate limit reached. Please try again later.'], 429);
        }
        $name = sanitize_text_field($req->get_param('name'));
        $phone = sanitize_text_field($req->get_param('phone'));
        $email = sanitize_email($req->get_param('email'));
        $message = sanitize_textarea_field($req->get_param('message'));
        if ($name === '' || $message === '') {
            return new WP_REST_Response(['error' => 'Name and message are required.'], 400);
        }
        if ($email !== '' && !is_email($email)) {
            return new WP_REST_Response(['error' => 'Invalid email.'], 400);
        }
        // Require at least one contact method
        if ($email === '' && $phone === '') {
            return new WP_REST_Response(['error' => 'Provide phone or email.'], 400);
        }
        // reCAPTCHA validation if configured
        $secret = trim((string) get_option('p116bd_recaptcha_secret', ''));
        if ($secret !== '') {
            $token = $req->get_param('g-recaptcha-response') ?: $req->get_param('token');
            if (!$token) return new WP_REST_Response(['error' => 'Captcha required.'], 400);
            $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'timeout' => 8,
                'body' => [ 'secret' => $secret, 'response' => $token ]
            ]);
            if (is_wp_error($resp)) {
                return new WP_REST_Response(['error' => 'Captcha verification failed.'], 400);
            }
            $data = json_decode(wp_remote_retrieve_body($resp), true);
            if (empty($data['success'])) {
                return new WP_REST_Response(['error' => 'Captcha invalid.'], 400);
            }
        }
        // Build allowed recipient set (owners + business email)
        $allowed = [];
        $owners = (array) get_post_meta($post_id, 'owners', true);
        foreach ($owners as $o) {
            $oe = isset($o['owner_email']) ? sanitize_email($o['owner_email']) : '';
            if ($oe && is_email($oe)) $allowed[$oe] = true;
        }
        $be = sanitize_email((string)get_post_meta($post_id, 'business_email', true));
        if ($be && is_email($be)) $allowed[$be] = true;
        if (empty($allowed)) {
            return new WP_REST_Response(['error' => 'No recipient configured.'], 400);
        }
        // Single recipient selection, validated server-side
        $recipient = sanitize_email((string)$req->get_param('recipient'));
        if ($recipient === '' || !isset($allowed[$recipient])) {
            // If client tampers with recipient, reject
            if ($recipient !== '') {
                return new WP_REST_Response(['error' => 'Invalid recipient.'], 400);
            }
            // Fallback to business email or first allowed
            $recipient = $be ?: array_key_first($allowed);
        }
        $to = $recipient;
        $subject = sprintf('[Post 116 Directory] Inquiry for %s', get_the_title($post_id));
        $lines = [];
        $lines[] = 'From: ' . $name;
        if ($phone) $lines[] = 'Phone: ' . $phone;
        if ($email) $lines[] = 'Email: ' . $email;
        $lines[] = '';
        $lines[] = $message;
        $body = implode("\n", $lines);
        $headers = [];
        if ($email) $headers[] = 'Reply-To: ' . $email;
        $sent = wp_mail($to, $subject, $body, $headers);
        if (!$sent) return new WP_REST_Response(['error' => 'Failed to send.'], 500);
        // increment counters
        set_transient($k_post, $c_post + 1, $window);
        set_transient($k_global, $c_global + 1, $window);
        return new WP_REST_Response(['ok' => true]);
    }

    public static function autocomplete(WP_REST_Request $req) {
        $q = sanitize_text_field($req->get_param('q'));
        $limit = max(1, min(20, (int)$req->get_param('limit')));

        $suggestions = [];

        // Business titles
        $posts = get_posts([
            'post_type' => CPT::POST_TYPE,
            's' => $q,
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'meta_query' => [ [ 'key' => 'show_in_directory', 'value' => '1' ] ],
        ]);
        foreach ($posts as $p) {
            $suggestions[] = ['type' => 'business', 'label' => $p->post_title, 'slug' => $p->post_name];
        }

        // Owners
        $owner_posts = get_posts([
            'post_type' => CPT::POST_TYPE,
            'posts_per_page' => $limit,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'show_in_directory', 'value' => '1'],
                ['key' => 'owners_search', 'value' => strtolower($q), 'compare' => 'LIKE']
            ],
            'post_status' => 'publish',
        ]);
        $q_lc = strtolower($q);
        foreach ($owner_posts as $p) {
            $owners = (array)get_post_meta($p->ID, 'owners', true);
            foreach ($owners as $o) {
                $name = $o['owner_name'] ?? '';
                if ($name !== '' && strpos(strtolower($name), $q_lc) !== false) {
                    $suggestions[] = ['type' => 'owner', 'label' => $o['owner_name'], 'slug' => $p->post_name];
                }
            }
        }

        // Categories
        $terms = get_terms([
            'taxonomy' => CPT::TAXONOMY,
            'name__like' => $q,
            'number' => $limit,
            'hide_empty' => false,
        ]);
        if (!is_wp_error($terms)) {
            foreach ($terms as $t) {
                $suggestions[] = ['type' => 'category', 'label' => $t->name, 'slug' => $t->slug];
            }
        }

        // Unique by label+type
        $unique = [];
        $out = [];
        foreach ($suggestions as $s) {
            $k = $s['type'] . '|' . $s['label'];
            if (!isset($unique[$k])) { $unique[$k] = true; $out[] = $s; }
            if (count($out) >= $limit) break;
        }

        return new WP_REST_Response(['items' => $out]);
    }

    private static function format_item($post) {
        $id = $post->ID;
        $owners = (array)get_post_meta($id, 'owners', true);
        $links = (array)get_post_meta($id, 'links', true);
        $cats = get_the_terms($id, CPT::TAXONOMY);
        $logo_id = (int) get_post_meta($id, 'business_logo_id', true);
        if (!$logo_id) {
            $thumb_id = get_post_thumbnail_id($id);
            if ($thumb_id) { $logo_id = (int)$thumb_id; }
        }
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        $services = (string)get_post_meta($id, 'services_offered', true);
        $first_owner = '';
        if (!empty($owners) && is_array($owners)) {
            $first_owner = $owners[0]['owner_name'] ?? '';
        }
        return [
            'id' => $id,
            'title' => get_the_title($id),
            'excerpt' => get_the_excerpt($id),
            'permalink' => get_permalink($id),
            'city' => (string)get_post_meta($id, 'city', true),
            'phone' => (string)get_post_meta($id, 'business_phone', true),
            'email' => (string)get_post_meta($id, 'business_email', true),
            'website' => (string)get_post_meta($id, 'website_url', true),
            'owners' => array_values(array_map(function ($o) { return [
                'name' => $o['owner_name'] ?? '',
                'role' => $o['owner_role'] ?? '',
            ];}, $owners)),
            'links' => array_values(array_map(function ($l) { return [
                'label' => $l['link_label'] ?? '',
                'url' => $l['link_url'] ?? '',
            ];}, $links)),
            'categories' => $cats && !is_wp_error($cats) ? array_values(wp_list_pluck($cats, 'name')) : [],
            'logo' => (string)$logo_url,
            'services' => $services,
            'owner' => $first_owner,
            'flags' => [
                'veteran_owned' => (bool)get_post_meta($id, 'veteran_owned', true),
                'sons_owned' => (bool)get_post_meta($id, 'sons_owned', true),
                'auxiliary_owned' => (bool)get_post_meta($id, 'auxiliary_owned', true),
            ],
        ];
    }
}
