<?php
namespace P116BD;

if (!defined('ABSPATH')) { exit; }

class Block_Directory {
    public static function register() {
        add_action('init', [__CLASS__, 'register_block']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_block() {
        register_block_type('p116/directory', [
            'render_callback' => [__CLASS__, 'render'],
            'attributes' => [
                'showFlags' => ['type' => 'boolean', 'default' => true],
                'perPage' => ['type' => 'number', 'default' => 12],
                'placeholderText' => ['type' => 'string', 'default' => __('Search businesses…', 'post116-business-directory')],
            ],
        ]);
    }

    public static function register_assets() {
        wp_register_script('p116bd-directory', P116BD_PLUGIN_URL . 'public/js/directory.js', ['wp-i18n'], P116BD_VERSION, true);
        wp_register_style('p116bd-public', P116BD_PLUGIN_URL . 'public/css/public.css', [], P116BD_VERSION);
    }

    public static function render($attrs = [], $content = '', $block = null) {
        wp_enqueue_script('p116bd-directory');
        wp_enqueue_style('p116bd-public');
        $placeholder = isset($attrs['placeholderText']) ? esc_attr($attrs['placeholderText']) : __('Search businesses…', 'post116-business-directory');
        $per_page = isset($attrs['perPage']) ? (int)$attrs['perPage'] : 12;
        $show_flags = !empty($attrs['showFlags']);

        ob_start();
        ?>
        <div class="p116bd-directory" data-per-page="<?php echo esc_attr($per_page); ?>">
          <div class="p116bd-search">
            <input type="text" class="p116bd-q" placeholder="<?php echo $placeholder; ?>" />
            <select class="p116bd-category">
              <option value=""><?php esc_html_e('All Categories', 'post116-business-directory'); ?></option>
              <?php foreach (get_terms(['taxonomy' => CPT::TAXONOMY, 'hide_empty' => false]) as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>"><?php echo esc_html($t->name); ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($show_flags): ?>
            <label><input type="checkbox" class="p116bd-flag" value="veteran_owned"/> <?php esc_html_e('Veteran', 'post116-business-directory'); ?></label>
            <label><input type="checkbox" class="p116bd-flag" value="sons_owned"/> <?php esc_html_e('SAL', 'post116-business-directory'); ?></label>
            <label><input type="checkbox" class="p116bd-flag" value="auxiliary_owned"/> <?php esc_html_e('Auxiliary', 'post116-business-directory'); ?></label>
            <?php endif; ?>
          </div>
          <div class="p116bd-results" data-legal="<?php echo esc_attr(__('American Legion Post 116 is not liable for or endorsing any listed businesses. Please independently verify their work quality, licenses, and insurance.', 'post116-business-directory')); ?>">
            <div class="p116bd-grid"></div>
            <div class="p116bd-pagination"></div>
            <div class="p116bd-legal"></div>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

