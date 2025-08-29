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
        });
    }

    public static function search(WP_REST_Request $req) {
        $q = sanitize_text_field($req->get_param('q'));
        $category = sanitize_text_field($req->get_param('category'));
        $flags = (array)$req->get_param('flags');
        $per_page = max(1, min(50, (int)$req->get_param('per_page')));
        $page = max(1, (int)$req->get_param('page'));

        $meta_query = [['key' => 'show_in_directory', 'value' => '1']];
        if ($q) {
            $meta_query['relation'] = 'OR';
            $meta_query[] = ['key' => 'owners_search', 'value' => $q, 'compare' => 'LIKE'];
            $meta_query[] = ['key' => 'city_search', 'value' => strtolower($q), 'compare' => 'LIKE'];
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
        ]);
        foreach ($posts as $p) {
            $suggestions[] = ['type' => 'business', 'label' => $p->post_title, 'slug' => $p->post_name];
        }

        // Owners
        $owner_posts = get_posts([
            'post_type' => CPT::POST_TYPE,
            'posts_per_page' => $limit,
            'meta_query' => [
                ['key' => 'owners_search', 'value' => $q, 'compare' => 'LIKE']
            ],
            'post_status' => 'publish',
        ]);
        foreach ($owner_posts as $p) {
            $owners = (array)get_post_meta($p->ID, 'owners', true);
            foreach ($owners as $o) {
                if (!empty($o['owner_name']) && stripos($o['owner_name'], $q) !== false) {
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
        ];
    }
}

