<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

class Templates {
    public static function register() {
        add_filter('single_template', [__CLASS__, 'single_template']);
        add_filter('taxonomy_template', [__CLASS__, 'taxonomy_template']);
        add_action('wp_head', [__CLASS__, 'json_ld']);
    }

    public static function single_template($template) {
        if (get_post_type() === CPT::POST_TYPE) {
            $tpl = P116BD_PLUGIN_DIR . 'templates/single-p116_business.php';
            if (file_exists($tpl)) return $tpl;
        }
        return $template;
    }

    public static function taxonomy_template($template) {
        $obj = get_queried_object();
        if ($obj && isset($obj->taxonomy) && $obj->taxonomy === CPT::TAXONOMY) {
            $tpl = P116BD_PLUGIN_DIR . 'templates/taxonomy-p116_business_category.php';
            if (file_exists($tpl)) return $tpl;
        }
        return $template;
    }

    public static function json_ld() {
        if (!is_singular(CPT::POST_TYPE)) return;
        $id = get_the_ID();
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => get_the_title($id),
            'url'  => get_permalink($id),
        ];
        $img = get_the_post_thumbnail_url($id, 'full');
        if ($img) $data['image'] = $img;
        $phone = get_post_meta($id, 'business_phone', true);
        if ($phone) $data['telephone'] = $phone;
        $email = get_post_meta($id, 'business_email', true);
        if ($email) $data['email'] = $email;
        $website = get_post_meta($id, 'website_url', true);
        if ($website) $data['sameAs'] = [$website];
        $address = array_filter([
            'streetAddress' => trim(get_post_meta($id, 'address1', true) . ' ' . get_post_meta($id, 'address2', true)),
            'addressLocality' => get_post_meta($id, 'city', true),
            'addressRegion' => get_post_meta($id, 'state', true),
            'postalCode' => get_post_meta($id, 'postal_code', true),
        ]);
        if (!empty($address)) $data['address'] = array_merge(['@type'=>'PostalAddress'], $address);

        echo '<script type="application/ld+json">' . wp_json_encode($data) . '</script>';
    }
}

