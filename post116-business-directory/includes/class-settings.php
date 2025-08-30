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
