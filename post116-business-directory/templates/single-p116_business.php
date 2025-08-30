<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$id = get_the_ID();
$logo_id = (int) get_post_meta($id, 'business_logo_id', true);
$owners = (array)get_post_meta($id, 'owners', true);
$links = (array)get_post_meta($id, 'links', true);
// Ensure public styles are loaded (reuse directory styling)
wp_enqueue_style('p116bd-public');

// Compute hero banner matching the directory page
$opt_img = (string) get_option('p116bd_hero_image_url', '');
$opt_title = (string) get_option('p116bd_hero_title', '');
$opt_sub = (string) get_option('p116bd_hero_subtitle', '');
$banner_title = $opt_title !== '' ? $opt_title : __('Business Directory', 'post116-business-directory');
$banner_sub = $opt_sub;
$hero_img = $opt_img ?: 'https://alpost116nc2.wpenginepowered.com/wp-content/uploads/2024/05/ALP116_MastheadImage_Legionnaire_1920x675.png';
$hero_style = '';
if ($hero_img) {
  $hero_style = 'style="background-image: linear-gradient(180deg, rgba(0,0,0,0.25), rgba(0,0,0,0.25)), url(' . esc_url($hero_img) . ');background-size:cover;background-position:center;background-repeat:no-repeat;display:flex;align-items:center;justify-content:center;min-height:675px;"';
}
?>
<style>
/* Make single business page full-bleed and hide theme page header */
<?php global $post; if ($post && isset($post->ID)) { $pid = intval($post->ID); echo '.single.single-p116_business .entry-header{display:none !important;}'; echo '.single.single-p116_business .ast-container{max-width:100%;padding:0;}'; echo '.single.single-p116_business .content-area.primary{padding:0 !important;margin:0 !important;}'; echo '.single.single-p116_business .site-main{margin-top:0 !important;}'; echo '.single.single-p116_business .site-content::before, .single.single-p116_business #content::before{content:none !important;display:none !important;height:0 !important;margin:0 !important;padding:0 !important;}'; } ?>
/* Ensure no sidebar squeezes the hero/content */
.single.single-p116_business .secondary{display:none !important}
.single.single-p116_business .content-area.primary{width:100% !important}
.single.single-p116_business .site-content{display:block !important}

/* Strong full-bleed container overrides */
.single.single-p116_business .site-content,
.single.single-p116_business .ast-container,
.single.single-p116_business .site-main,
.single.single-p116_business .content-area,
.single.single-p116_business #primary{
  max-width:100% !important; width:100% !important; padding:0 !important; margin:0 !important;
  float:none !important; display:block !important; overflow:visible !important;
}
.single.single-p116_business #content > .ast-container{max-width:100% !important;padding:0 !important;margin:0 !important}
.single.single-p116_business #primary{padding:0 !important;margin:0 !important;width:100% !important}
.single.single-p116_business .p116bd-hero{position:relative !important;width:100vw !important;max-width:100vw !important;margin-left:calc(50% - 50vw) !important;margin-right:calc(50% - 50vw) !important}


/* Typography + layout tuned to match directory */
.p116bd-single, .p116bd-single input, .p116bd-single select, .p116bd-single button{font-family: Gothom, Helvetica, Arial, sans-serif !important;}
.p116bd-single{font-size:20px !important;font-weight:500 !important;line-height:30px !important;color:#000 !important;margin-bottom:70px !important}

.p116bd-single__container{max-width:1200px;margin:0 auto;padding:0 20px;box-sizing:border-box}
.p116bd-single__header h1{margin:28px 0 6px 0;font-size:36px !important;font-weight:700 !important;line-height:1.2;color:#000}
.p116bd-flags{margin-top:8px}
.p116bd-badge{display:inline-flex;align-items:center;gap:6px;background:#eef;border:1px solid #dde;padding:4px 8px;margin-right:6px;border-radius:4px;font-size:12px}

.p116bd-single__grid{display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;margin-top:22px}
.p116bd-single__media img{width:100%;max-width:320px;height:320px;object-fit:contain;background:#fafafa;border:1px solid #eee;border-radius:8px;display:block}

.p116bd-single__details .p116bd-list{list-style:none;padding:0;margin:0}
.p116bd-single__details .p116bd-list li{margin:8px 0}
.p116bd-single__details a{color:#0645AD;text-decoration:none}
.p116bd-single__details a:hover{text-decoration:underline}

.p116bd-owners{list-style:none;padding:0;margin:12px 0}
.p116bd-owners li{margin:10px 0}
.p116bd-owner-contacts{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}

.p116bd-links{list-style:none;padding:0;margin:12px 0;display:flex;flex-wrap:wrap;gap:12px}
.p116bd-links li a{display:inline-block;padding:6px 10px;border:1px solid #e3e3e3;border-radius:6px;background:#fff}

.p116bd-single h2{margin-top:28px;font-size:28px !important;font-weight:700 !important}
.p116bd-single h3{margin-top:18px;font-size:22px !important;font-weight:700 !important}

.p116bd-single__content{margin-top:18px}
.p116bd-legal{margin-top:16px;font-size:12px;color:#555}

@media (max-width: 900px){
  .p116bd-single__grid{grid-template-columns:1fr}
  .p116bd-single__media img{max-width:100%;height:auto}
}
</style>

<?php if (!empty($hero_style)) : ?>
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
<main class="p116bd-single">
  <div class="p116bd-single__container">
    <article <?php post_class(); ?>>
      <header class="p116bd-single__header">
        <h1><?php the_title(); ?></h1>
        <div class="p116bd-flags">
          <?php if (get_post_meta($id, 'veteran_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('Veteran', 'post116-business-directory'); ?></span><?php endif; ?>
          <?php if (get_post_meta($id, 'sons_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('SAL', 'post116-business-directory'); ?></span><?php endif; ?>
          <?php if (get_post_meta($id, 'auxiliary_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('Auxiliary', 'post116-business-directory'); ?></span><?php endif; ?>
        </div>
        <div class="p116bd-cats" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px">
          <?php $terms = get_the_terms($id, \P116BD\CPT::TAXONOMY); if ($terms && !is_wp_error($terms)) : foreach ($terms as $t): ?>
            <span style="display:inline-block;border:1px solid #e3e3e3;background:#fff;border-radius:999px;padding:4px 10px;font-size:14px;line-height:1;"><?php echo esc_html($t->name); ?></span>
          <?php endforeach; endif; ?>
        </div>
        <div style="margin-top:14px"><a href="<?php echo esc_url(home_url('/directory/')); ?>" style="text-decoration:none;color:#0645AD">&larr; <?php esc_html_e('Back to Directory','post116-business-directory'); ?></a></div>
      </header>
      <div class="p116bd-single__grid">
      <div class="p116bd-single__media">
        <?php
          if ($logo_id) {
              echo wp_get_attachment_image($logo_id, 'large');
          } else {
              the_post_thumbnail('large');
          }
        ?>
      </div>
      <div class="p116bd-single__details">
         <ul class="p116bd-list">
          <?php $phone = get_post_meta($id, 'business_phone', true); if ($phone): ?>
            <li><strong><?php esc_html_e('Phone:', 'post116-business-directory'); ?></strong> <a href="tel:<?php echo esc_attr(preg_replace('/\D+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></li>
          <?php endif; ?>
          <?php $email = get_post_meta($id, 'business_email', true); if ($email): ?>
            <li><strong><?php esc_html_e('Email:', 'post116-business-directory'); ?></strong> <a href="mailto:<?php echo antispambot(esc_attr($email)); ?>"><?php echo antispambot(esc_html($email)); ?></a></li>
          <?php endif; ?>
          <?php $website = get_post_meta($id, 'website_url', true); if ($website): ?>
            <li><strong><?php esc_html_e('Website:', 'post116-business-directory'); ?></strong> <a target="_blank" rel="noopener" href="<?php echo esc_url($website); ?>"><?php echo esc_html($website); ?></a></li>
          <?php endif; ?>
          <?php 
            $a1 = trim((string) get_post_meta($id, 'address1', true));
            $a2 = trim((string) get_post_meta($id, 'address2', true));
            $city = trim((string) get_post_meta($id, 'city', true));
            $st = trim((string) get_post_meta($id, 'state', true));
            $zip = trim((string) get_post_meta($id, 'postal_code', true));
            $addr = trim($a1 . ( $a2 ? (' ' . $a2) : '' ));
            $csz = trim($city . ( $st ? (', ' . $st) : '' ) . ( $zip ? (' ' . $zip) : '' ));
          ?>
          <?php if ($addr !== '' || $csz !== ''): ?>
            <?php $addr_display = ($addr !== '' && $csz !== '') ? ($addr . ', ' . $csz) : ($addr !== '' ? $addr : $csz); ?>
            <li><strong><?php esc_html_e('Address:', 'post116-business-directory'); ?></strong> <?php echo esc_html($addr_display); ?></li>
          <?php endif; ?>
        </ul>

        <?php if (!empty($owners)): ?>
          <h3><?php esc_html_e('Owners', 'post116-business-directory'); ?></h3>
          <ul class="p116bd-owners">
            <?php foreach ($owners as $o): ?>
              <li>
                <strong><?php echo esc_html($o['owner_name'] ?? ''); ?></strong>
                <?php if (!empty($o['owner_role'])) echo ' — ' . esc_html($o['owner_role']); ?>
                <div class="p116bd-owner-contacts">
                  <?php if (!empty($o['owner_email'])): ?><a href="mailto:<?php echo antispambot(esc_attr($o['owner_email'])); ?>"><?php echo antispambot(esc_html($o['owner_email'])); ?></a><?php endif; ?>
                  <?php if (!empty($o['owner_phone'])): ?><span><?php echo esc_html($o['owner_phone']); ?></span><?php endif; ?>
                  <?php if (!empty($o['owner_website'])): ?><a target="_blank" rel="noopener" href="<?php echo esc_url($o['owner_website']); ?>"><?php echo esc_html($o['owner_website']); ?></a><?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($links)): ?>
          <h3><?php esc_html_e('Links', 'post116-business-directory'); ?></h3>
          <ul class="p116bd-links">
            <?php foreach ($links as $l): if (empty($l['link_label']) && empty($l['link_url'])) continue; ?>
              <li><a target="_blank" rel="noopener" href="<?php echo esc_url($l['link_url'] ?? ''); ?>"><?php echo esc_html($l['link_label'] ?? ($l['link_url'] ?? '')); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      </div>

    <?php if ($services = get_post_meta($id, 'services_offered', true)) : ?>
      <h2><?php esc_html_e('Services Offered', 'post116-business-directory'); ?></h2>
      <p><?php echo esc_html($services); ?></p>
    <?php endif; ?>

    <div class="p116bd-single__content">
      <?php the_content(); ?>
    </div>

    <p class="p116bd-legal"><?php echo esc_html__('American Legion Post 116 is not liable for or endorsing any listed businesses. Please independently verify their work quality, licenses, and insurance.', 'post116-business-directory'); ?></p>
    </article>
  </div>
</main>
<?php get_footer(); ?>
