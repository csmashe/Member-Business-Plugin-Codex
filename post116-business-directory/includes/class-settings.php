<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

class Settings {
    public static function register() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'settings']);
    }

    public static function menu() {
        add_options_page(
            __('Business Directory', 'post116-business-directory'),
            __('Business Directory', 'post116-business-directory'),
            'manage_options',
            'p116bd-settings',
            [__CLASS__, 'render']
        );
    }

    public static function settings() {
        register_setting('p116bd', 'p116bd_directory_page_id', ['type'=>'integer']);
        register_setting('p116bd', 'p116bd_enable_map', ['type'=>'boolean', 'default'=>false]);
        register_setting('p116bd', 'p116bd_flags_visible', ['type'=>'boolean', 'default'=>true]);
        // Hero/banner settings
        register_setting('p116bd', 'p116bd_hero_image_url', ['type'=>'string', 'default'=>'']);
        register_setting('p116bd', 'p116bd_hero_title', ['type'=>'string', 'default'=>'']);
        register_setting('p116bd', 'p116bd_hero_subtitle', ['type'=>'string', 'default'=>'']);
        // Contact form (reCAPTCHA)
        register_setting('p116bd', 'p116bd_recaptcha_site_key', ['type'=>'string', 'default'=>'']);
        register_setting('p116bd', 'p116bd_recaptcha_secret', ['type'=>'string', 'default'=>'']);

        add_settings_section('p116bd_main', __('Directory Settings', 'post116-business-directory'), '__return_false', 'p116bd');

        add_settings_field('p116bd_directory_page_id', __('Directory Page', 'post116-business-directory'), function(){
            $page_id = (int)get_option('p116bd_directory_page_id');
            wp_dropdown_pages(['name'=>'p116bd_directory_page_id','selected'=>$page_id,'show_option_none'=>__('— Select —')]);
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_flags_visible', __('Show Ownership Filters', 'post116-business-directory'), function(){
            $val = (bool)get_option('p116bd_flags_visible', true);
            echo '<input type="hidden" name="p116bd_flags_visible" value="0" />';
            echo '<input type="checkbox" name="p116bd_flags_visible" value="1"' . checked($val, true, false) . ' />';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_enable_map', __('Enable Map (Phase 2)', 'post116-business-directory'), function(){
            $val = (bool)get_option('p116bd_enable_map', false);
            echo '<input type="hidden" name="p116bd_enable_map" value="0" />';
            echo '<input type="checkbox" name="p116bd_enable_map" value="1"' . checked($val, true, false) . ' />';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_hero_image_url', __('Directory Hero Image URL', 'post116-business-directory'), function(){
            $val = esc_url(get_option('p116bd_hero_image_url', ''));
            echo '<input type="url" class="regular-text" name="p116bd_hero_image_url" value="' . $val . '" placeholder="https://.../image.jpg" />';
            echo '<p class="description">' . esc_html__('Shown behind the menu, full-bleed. Recommended 1920x675.', 'post116-business-directory') . '</p>';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_hero_title', __('Directory Hero Small Title (H1)', 'post116-business-directory'), function(){
            $val = esc_attr(get_option('p116bd_hero_title', ''));
            echo '<input type="text" class="regular-text" name="p116bd_hero_title" value="' . $val . '" placeholder="Business Directory" />';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_hero_subtitle', __('Directory Hero Large Heading', 'post116-business-directory'), function(){
            $val = esc_attr(get_option('p116bd_hero_subtitle', ''));
            echo '<input type="text" class="regular-text" name="p116bd_hero_subtitle" value="' . $val . '" placeholder="Member-owned and affiliate businesses" />';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_recaptcha_site_key', __('reCAPTCHA Site Key', 'post116-business-directory'), function(){
            $val = esc_attr(get_option('p116bd_recaptcha_site_key', ''));
            echo '<input type="text" class="regular-text" name="p116bd_recaptcha_site_key" value="' . $val . '" placeholder="site key" />';
        }, 'p116bd', 'p116bd_main');

        add_settings_field('p116bd_recaptcha_secret', __('reCAPTCHA Secret', 'post116-business-directory'), function(){
            $val = esc_attr(get_option('p116bd_recaptcha_secret', ''));
            echo '<input type="password" class="regular-text" name="p116bd_recaptcha_secret" value="' . $val . '" placeholder="secret" />';
        }, 'p116bd', 'p116bd_main');
    }

    public static function render() {
        echo '<div class="wrap"><h1>' . esc_html__('Business Directory', 'post116-business-directory') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('p116bd');
        do_settings_sections('p116bd');
        submit_button();
        echo '</form></div>';
    }
}
