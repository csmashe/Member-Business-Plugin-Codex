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
                'showBanner' => ['type' => 'boolean', 'default' => true],
                'bannerTitle' => ['type' => 'string', 'default' => __('Business Directory', 'post116-business-directory')],
                'bannerSubtitle' => ['type' => 'string', 'default' => __('Member-owned and affiliate businesses', 'post116-business-directory')],
            ],
        ]);
    }

    public static function register_assets() {
        wp_register_script('p116bd-directory', P116BD_PLUGIN_URL . 'public/js/directory.js', ['wp-i18n'], P116BD_VERSION, true);
        wp_register_style('p116bd-public', P116BD_PLUGIN_URL . 'public/css/public.css', [], P116BD_VERSION);
    }

    public static function render($attrs = [], $content = '', $block = null) {
        // Pass plugin URL to JS for icon paths
        wp_add_inline_script('p116bd-directory', 'window.p116bdPluginUrl = ' . json_encode(P116BD_PLUGIN_URL) . ';', 'before');
        wp_enqueue_script('p116bd-directory');
        wp_enqueue_style('p116bd-public');
        $placeholder = isset($attrs['placeholderText']) ? esc_attr($attrs['placeholderText']) : __('Search businesses…', 'post116-business-directory');
        $per_page = isset($attrs['perPage']) ? (int)$attrs['perPage'] : 12;
        $show_flags = !empty($attrs['showFlags']);
        $show_banner = isset($attrs['showBanner']) ? (bool)$attrs['showBanner'] : true;
        // Pull admin settings as fallbacks
        $opt_img = (string) get_option('p116bd_hero_image_url', '');
        $opt_title = (string) get_option('p116bd_hero_title', '');
        $opt_sub = (string) get_option('p116bd_hero_subtitle', '');
        $banner_title = isset($attrs['bannerTitle']) && $attrs['bannerTitle'] !== '' ? $attrs['bannerTitle'] : ($opt_title !== '' ? $opt_title : __('Business Directory', 'post116-business-directory'));
        $banner_sub = isset($attrs['bannerSubtitle']) && $attrs['bannerSubtitle'] !== '' ? $attrs['bannerSubtitle'] : $opt_sub;
        $hero_style = '';
        $img = $opt_img ?: 'https://alpost116nc2.wpenginepowered.com/wp-content/uploads/2024/05/ALP116_MastheadImage_Legionnaire_1920x675.png';
        if ($img) {
            $hero_style = 'style="background-image: linear-gradient(180deg, rgba(0,0,0,0.25), rgba(0,0,0,0.25)), url(' . esc_url($img) . ');background-size:cover;background-position:center;background-repeat:no-repeat;display:flex;align-items:center;justify-content:center;min-height:675px;"';
        }

        ob_start();
        ?>
        <div class="p116bd-directory" data-per-page="<?php echo esc_attr($per_page); ?>">
          <?php
            // Hide native page H1 on the Directory page and tweak layout container spacing only on this page
            global $post;
            if ($post && isset($post->ID)) {
                $pid = intval($post->ID);
                echo '<style>
                .page-id-' . $pid . ' main article > h1{display:none;}
                .page-id-' . $pid . ' .entry-title{display:none;}
                .page-id-' . $pid . ' .entry-header{display:none !important;}
                .page-id-' . $pid . ' .ast-container{max-width:100%;padding:0;}
                .page-id-' . $pid . ' .content-area.primary{padding:0 !important;margin:0 !important;}
                .page-id-' . $pid . ' .site-main{margin-top:0 !important;}
                .page-id-' . $pid . ' .site-content::before, .page-id-' . $pid . ' #content::before{content:none !important;display:none !important;height:0 !important;margin:0 !important;padding:0 !important;}
                </style>';
            }
          ?>
          <?php if ($show_banner): ?>
          <section class="p116bd-hero" <?php echo $hero_style; ?> aria-label="<?php echo esc_attr__('Directory banner', 'post116-business-directory'); ?>">
            <div class="hero-container">
              <div class="p116bd-hero__inner">
              <h1 class="p116bd-hero__kicker" style="font-family:Gothom, Helvetica, Arial, sans-serif;font-size:29px;font-weight:700;text-transform:uppercase;line-height:1.2;color:#000;">
                <?php echo esc_html($banner_title); ?>
              </h1>
              <?php if (!empty($banner_sub)): ?>
                <h2 class="p116bd-hero__heading" style="font-family:'Gothom', Sans-serif;font-size:53px;font-weight:700;line-height:1.1;color:#000;margin:15px 0 0 0;">
                  <?php echo esc_html($banner_sub); ?>
                </h2>
              <?php endif; ?>
              </div>
            </div>
          </section>
          <?php endif; ?>
          <div class="p116bd-search">
            <input type="text" class="p116bd-q" placeholder="<?php echo $placeholder; ?>" style="height:44px;line-height:44px;" />
            <select class="p116bd-category" style="height:44px;line-height:44px;">
              <option value=""><?php esc_html_e('All Categories', 'post116-business-directory'); ?></option>
              <?php foreach (get_terms(['taxonomy' => CPT::TAXONOMY, 'hide_empty' => false]) as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>"><?php echo esc_html($t->name); ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($show_flags): ?>
            <div class="p116bd-flags">
              <label><input type="checkbox" class="p116bd-flag" value="veteran_owned"/> <?php esc_html_e('Veteran', 'post116-business-directory'); ?></label>
              <label><input type="checkbox" class="p116bd-flag" value="sons_owned"/> <?php esc_html_e('SAL', 'post116-business-directory'); ?></label>
              <label><input type="checkbox" class="p116bd-flag" value="auxiliary_owned"/> <?php esc_html_e('Auxiliary', 'post116-business-directory'); ?></label>
            </div>
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
