<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$id = get_the_ID();
$logo_id = (int) get_post_meta($id, 'business_logo_id', true);
$owners_meta = get_post_meta($id, 'owners', true);
$owners = [];
if (is_array($owners_meta)) {
  foreach ($owners_meta as $o) {
    if (!is_array($o)) { continue; }
    // Normalize expected keys to strings
    $item = [
      'owner_name'    => isset($o['owner_name']) ? (string)$o['owner_name'] : '',
      'owner_role'    => isset($o['owner_role']) ? (string)$o['owner_role'] : '',
      'owner_email'   => isset($o['owner_email']) ? (string)$o['owner_email'] : '',
      'owner_phone'   => isset($o['owner_phone']) ? (string)$o['owner_phone'] : '',
      'owner_website' => isset($o['owner_website']) ? (string)$o['owner_website'] : '',
      'owner_affil'   => isset($o['owner_affil']) ? (string)$o['owner_affil'] : '',
    ];
    // Keep only non-empty owner entries
    if (trim($item['owner_name']) !== '' || trim($item['owner_email']) !== '' || trim($item['owner_phone']) !== '') {
      $owners[] = $item;
    }
  }
}

$links_meta = get_post_meta($id, 'links', true);
$links = [];
if (is_array($links_meta)) {
  foreach ($links_meta as $l) {
    if (!is_array($l)) { continue; }
    $label = isset($l['link_label']) ? (string)$l['link_label'] : '';
    $url   = isset($l['link_url']) ? (string)$l['link_url'] : '';
    if ($label !== '' || $url !== '') {
      $links[] = ['link_label' => $label, 'link_url' => $url];
    }
  }
}
// Ensure public styles are loaded (reuse directory styling)
wp_enqueue_style('p116bd-public');
wp_enqueue_script('p116bd-single', P116BD_PLUGIN_URL . 'public/js/single.js', [], P116BD_VERSION, true);

// Compute hero banner matching the directory page
$opt_img = (string) get_option('p116bd_hero_image_url', '');
$opt_title = (string) get_option('p116bd_hero_title', '');
$opt_sub = (string) get_option('p116bd_hero_subtitle', '');
$banner_title = $opt_title !== '' ? $opt_title : __('Business Directory', 'post116-business-directory');
$banner_sub = $opt_sub;
$hero_img = trim((string)$opt_img);
// Normalize relative path to absolute URL
if ($hero_img && !preg_match('#^https?://#i', $hero_img)) {
  $hero_img = home_url($hero_img[0] === '/' ? $hero_img : '/' . $hero_img);
}
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
.p116bd-single__header h1{margin:28px 0 6px 0;font-size:40px !important;font-weight:800 !important;line-height:1.15;color:#000}
.p116bd-flags{margin-top:8px}
.p116bd-badge{display:inline-flex;align-items:center;gap:6px;background:#eef;border:1px solid #dde;padding:4px 8px;margin-right:6px;border-radius:4px;font-size:12px}
.p116bd-row__emblems{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
.p116bd-row__emblems img{height:46px;width:auto;display:block}

.p116bd-single__grid{display:grid;grid-template-columns:360px 1fr;gap:28px;align-items:start;margin-top:26px}
.p116bd-single__media img{width:100%;max-width:320px;height:320px;object-fit:contain;background:#fafafa;border:1px solid #eee;border-radius:8px;display:block}

.p116bd-card{background:#fff;border:1px solid #e6e6e6;border-radius:12px;padding:18px 20px;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
.p116bd-card + .p116bd-card{margin-top:16px}

.p116bd-single__details .p116bd-list{list-style:none;padding:0;margin:0}
.p116bd-single__details .p116bd-list li{margin:8px 0}
.p116bd-single__details a{color:#0645AD;text-decoration:none}
.p116bd-single__details a:hover{text-decoration:underline}

.p116bd-owners{list-style:none;padding:0;margin:12px 0}
.p116bd-owners li{margin:10px 0}
.p116bd-owner-contacts{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}

.p116bd-links{list-style:none;padding:0;margin:12px 0;display:flex;flex-wrap:wrap;gap:12px}
.p116bd-links li a{display:inline-block;padding:6px 10px;border:1px solid #e3e3e3;border-radius:6px;background:#fff}

.p116bd-ctas{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0 6px}
/* Unified CTA styles: outline by default, fill on hover; color #0645AD */
.p116bd-cta{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:10px 14px;border:1px solid #0645AD;background:#fff;color:#0645AD;text-decoration:none;font-weight:700;font-size:16px;cursor:pointer;transition:background .15s,color .15s}
.p116bd-cta--ghost{background:#fff;color:#0645AD;border-color:#0645AD}
.p116bd-cta svg{width:18px;height:18px;display:block}
.p116bd-cta:focus{outline:none}
.p116bd-cta:focus-visible{box-shadow:0 0 0 3px rgba(6,69,173,0.3)}
.p116bd-cta:hover,.p116bd-cta--ghost:hover{background:#0645AD;color:#fff}

.p116bd-single h2{margin-top:28px;font-size:28px !important;font-weight:700 !important}
.p116bd-single h3{margin-top:18px;font-size:22px !important;font-weight:700 !important}

.p116bd-single__content{margin-top:18px}
.p116bd-legal{margin-top:16px;font-size:12px;color:#555}

@media (max-width: 900px){
  .p116bd-single__grid{grid-template-columns:1fr}
  .p116bd-single__media img{max-width:100%;height:auto}
}
</style>

<style>
/* Ensure contact modal is hidden by default, even if theme styles intervene */
.p116bd-modal{display:none !important}
.p116bd-modal.is-open{display:flex !important}
/* Owner emblem size */
.p116bd-owners .p116bd-flag-icon{height:35px;width:auto}
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
              if (has_post_thumbnail()) { the_post_thumbnail('large'); } else { echo '<div class="p116bd-logo--placeholder"></div>'; }
          }
        ?>
      </div>
      <div class="p116bd-single__details">
         <div class="p116bd-card">
         <ul class="p116bd-list">
          <?php $phone = get_post_meta($id, 'business_phone', true); if ($phone): ?>
            <li><strong><?php esc_html_e('Phone:', 'post116-business-directory'); ?></strong> <?php echo esc_html($phone); ?></li>
          <?php endif; ?>
          <?php $email = get_post_meta($id, 'business_email', true); if ($email): ?>
            <li><strong><?php esc_html_e('Email:', 'post116-business-directory'); ?></strong> <?php echo antispambot(esc_html($email)); ?></li>
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
            $maps_q = trim(($addr !== '' ? $addr . ', ' : '') . $csz);
          ?>
          <?php if ($addr !== '' || $csz !== ''): ?>
            <?php $addr_display = ($addr !== '' && $csz !== '') ? ($addr . ', ' . $csz) : ($addr !== '' ? $addr : $csz); ?>
            <li><strong><?php esc_html_e('Address:', 'post116-business-directory'); ?></strong> <?php echo esc_html($addr_display); ?></li>
          <?php endif; ?>
        </ul>
         <?php $have_cta = !empty($phone) || !empty($email) || !empty($website); if ($have_cta): ?>
         <div class="p116bd-ctas">
            <?php if (!empty($phone)): ?>
              <a class="p116bd-cta p116bd-cta--ghost" href="tel:<?php echo esc_attr(preg_replace('/\D+/', '', $phone)); ?>" aria-label="<?php esc_attr_e('Call business', 'post116-business-directory'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V21a1 1 0 01-1 1C10.4 22 2 13.6 2 3a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.24 1.01l-2.2 2.2z"/></svg>
                <?php esc_html_e('Call', 'post116-business-directory'); ?>
              </a>
            <?php endif; ?>
            <?php if ($addr !== '' && $city !== '' && $st !== ''): ?>
              <a class="p116bd-cta p116bd-cta--ghost" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=<?php echo rawurlencode($addr . ', ' . $city . ', ' . $st . ' ' . $zip); ?>"><?php esc_html_e('Directions', 'post116-business-directory'); ?></a>
            <?php endif; ?>
            <?php if (!empty($email)): ?>
              <button class="p116bd-cta p116bd-cta--ghost" type="button" data-p116bd-open-contact data-biz-email="<?php echo esc_attr(antispambot($email)); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                <?php esc_html_e('Email', 'post116-business-directory'); ?>
              </button>
            <?php endif; ?>
            <?php if (!empty($website)): ?>
              <a class="p116bd-cta p116bd-cta--ghost" target="_blank" rel="noopener" href="<?php echo esc_url($website); ?>" aria-label="<?php esc_attr_e('Visit website', 'post116-business-directory'); ?>">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3z"/><path d="M5 5h7V3H3v9h2V5zm0 14v-7H3v9h9v-2H5z"/></svg>
                <?php esc_html_e('Website', 'post116-business-directory'); ?>
              </a>
            <?php endif; ?>
         </div>
         <?php endif; ?>
         </div>

        <?php if (!empty($owners)): ?>
          <div class="p116bd-card">
          <h3><?php esc_html_e('Owners', 'post116-business-directory'); ?></h3>
          <ul class="p116bd-owners">
            <?php foreach ($owners as $o): ?>
              <?php 
                $aff = $o['owner_affil'] ?? '';
                $aff_icon = '';
                if ($aff === 'veteran') $aff_icon = P116BD_PLUGIN_URL . 'public/icons/TAL-emblem-full-detail-RGB.png';
                elseif ($aff === 'sal') $aff_icon = P116BD_PLUGIN_URL . 'public/icons/SAL-Emblem.png';
                elseif ($aff === 'auxiliary') $aff_icon = P116BD_PLUGIN_URL . 'public/icons/Auxiliary-Emblem.png';
                $owner_phone_raw = $o['owner_phone'] ?? '';
                $owner_email_raw = $o['owner_email'] ?? '';
                $biz_phone_raw = $phone ?? '';
                $biz_email_raw = $email ?? '';
                $owner_phone_norm = preg_replace('/\D+/', '', $owner_phone_raw);
                $biz_phone_norm   = preg_replace('/\D+/', '', $biz_phone_raw);
                $owner_email_norm = strtolower(trim($owner_email_raw));
                $biz_email_norm   = strtolower(trim($biz_email_raw));
                $show_owner_call  = ($owner_phone_norm !== '') && ($owner_phone_norm !== $biz_phone_norm);
                $show_owner_email = ($owner_email_norm !== '') && ($owner_email_norm !== $biz_email_norm);
              ?>
              <li>
                <strong><?php echo esc_html($o['owner_name'] ?? ''); ?></strong>
                <?php if (!empty($o['owner_role'])): ?> — <?php echo esc_html($o['owner_role']); ?><?php endif; ?>
                <?php if ($aff_icon): ?><img src="<?php echo esc_url($aff_icon); ?>" alt="" class="p116bd-flag-icon" style="vertical-align:middle;margin-left:6px"/><?php endif; ?>
                <div class="p116bd-owner-contacts">
                  <?php if ($show_owner_call): ?>
                    <a class="p116bd-cta p116bd-cta--ghost" href="tel:<?php echo esc_attr(preg_replace('/\D+/', '', $o['owner_phone'])); ?>"><?php esc_html_e('Call', 'post116-business-directory'); ?></a>
                  <?php endif; ?>
                  <?php if ($show_owner_email): ?>
                    <button type="button" class="p116bd-cta p116bd-cta--ghost" data-p116bd-open-contact data-owner-email="<?php echo esc_attr(antispambot($o['owner_email'])); ?>"><?php esc_html_e('Email', 'post116-business-directory'); ?></button>
                  <?php endif; ?>
                  <?php if (!empty($o['owner_website']) && (!empty($website) ? (trim($o['owner_website']) !== trim($website)) : true)): ?>
                    <a class="p116bd-cta p116bd-cta--ghost" target="_blank" rel="noopener" href="<?php echo esc_url($o['owner_website']); ?>"><?php esc_html_e('Website', 'post116-business-directory'); ?></a>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($links)): ?>
          <div class="p116bd-card">
          <h3><?php esc_html_e('Links', 'post116-business-directory'); ?></h3>
          <ul class="p116bd-links">
            <?php foreach ($links as $l): if (empty($l['link_label']) && empty($l['link_url'])) continue; ?>
              <li><a target="_blank" rel="noopener" href="<?php echo esc_url($l['link_url'] ?? ''); ?>"><?php echo esc_html($l['link_label'] ?? ($l['link_url'] ?? '')); ?></a></li>
            <?php endforeach; ?>
          </ul>
          </div>
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
<?php 
// Contact modal markup
$site_key = trim((string) get_option('p116bd_recaptcha_site_key', ''));
?>
<div class="p116bd-modal" id="p116bd-contact-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="p116bd-modal__dialog">
    <div class="p116bd-modal__header">
      <h3 class="p116bd-modal__title"><?php echo esc_html__('Contact Business', 'post116-business-directory'); ?></h3>
      <button class="p116bd-modal__close" type="button" aria-label="Close">&times;</button>
    </div>
    <form class="p116bd-form" id="p116bd-contact-form">
      <input type="hidden" name="post_id" value="<?php echo esc_attr($id); ?>" />
      <?php 
        // Build recipient options: business email + owners with emails
        $recip_opts = [];
        if (!empty($email) && is_email($email)) {
          $recip_opts[] = ['val' => antispambot($email), 'label' => __('Business', 'post116-business-directory') . ' — ' . antispambot($email)];
        }
        if (!empty($owners) && is_array($owners)) {
          foreach ($owners as $o) {
            $oe = isset($o['owner_email']) ? sanitize_email($o['owner_email']) : '';
            if ($oe && is_email($oe)) {
              $label = trim(($o['owner_name'] ?? '')); if ($label === '') { $label = __('Owner','post116-business-directory'); }
              $recip_opts[] = ['val' => antispambot($oe), 'label' => $label . ' — ' . antispambot($oe)];
            }
          }
        }
      ?>
      <?php if (count($recip_opts) > 1): ?>
      <div class="full">
        <label><?php esc_html_e('Send to', 'post116-business-directory'); ?><br/>
        <select name="recipient">
          <?php foreach ($recip_opts as $ro): ?>
            <option value="<?php echo esc_attr($ro['val']); ?>"><?php echo esc_html($ro['label']); ?></option>
          <?php endforeach; ?>
        </select></label>
      </div>
      <?php elseif (count($recip_opts) === 1): ?>
        <input type="hidden" name="recipient" value="<?php echo esc_attr($recip_opts[0]['val']); ?>" />
      <?php endif; ?>
      <div>
        <label><?php esc_html_e('Name', 'post116-business-directory'); ?><br/>
        <input type="text" name="name" required></label>
      </div>
      <div>
        <label><?php esc_html_e('Phone Number', 'post116-business-directory'); ?><br/>
        <input type="text" name="phone"></label>
      </div>
      <div class="full">
        <label><?php esc_html_e('Email (optional)', 'post116-business-directory'); ?><br/>
        <input type="email" name="email"></label>
      </div>
      <div class="full">
        <label><?php esc_html_e('Message', 'post116-business-directory'); ?><br/>
        <textarea name="message" rows="5" required></textarea></label>
      </div>
      <?php if ($site_key !== ''): ?>
        <div class="full"><div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div></div>
      <?php endif; ?>
      <div class="full">
        <button type="submit" class="button button-primary"><?php esc_html_e('Send', 'post116-business-directory'); ?></button>
        <span id="p116bd-contact-status" style="margin-left:10px"></span>
      </div>
    </form>
  </div>
</div>
<?php if ($site_key !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php get_footer(); ?>
