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

        add_settings_field('p116bd_hero_image_url', __('Directory Hero Image', 'post116-business-directory'), function(){
            if (function_exists('wp_enqueue_media')) { wp_enqueue_media(); }
            $val = esc_url(get_option('p116bd_hero_image_url', ''));
            // Use text to allow relative paths like /wp-content/uploads/... which we will normalize on render.
            echo '<input type="text" id="p116bd_hero_image_url" class="regular-text" name="p116bd_hero_image_url" value="' . $val . '" placeholder="https://.../image.jpg or /wp-content/uploads/....png" /> ';
            echo '<button type="button" class="button" id="p116bd_hero_image_browse">' . esc_html__('Browse', 'post116-business-directory') . '</button>';
            echo '<p class="description">' . esc_html__('Shown behind the menu, full-bleed. Recommended 1920x675.', 'post116-business-directory') . '</p>';
            echo '<script>(function(){
              var b = document.getElementById("p116bd_hero_image_browse");
              if(!b) return;
              var input = document.getElementById("p116bd_hero_image_url");
              var frame;
              b.addEventListener("click", function(e){
                e.preventDefault();
                if(frame){ frame.open(); return; }
                frame = wp.media({ title: "' . esc_js(__('Select Image', 'post116-business-directory')) . '", button: { text: "' . esc_js(__('Use this image', 'post116-business-directory')) . '" }, library:{ type:"image" }, multiple:false });
                frame.on("select", function(){ var att = frame.state().get("selection").first().toJSON(); if(att && att.url){ input.value = att.url; } });
                frame.open();
              });
            })();</script>';
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
