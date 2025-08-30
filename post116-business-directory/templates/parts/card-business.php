<?php
use P116BD\CPT;
use P116BD\CPT as CPTClass;
if (!defined('ABSPATH')) { exit; }
$id = $args['id'] ?? get_the_ID();
$logo_id = (int) get_post_meta($id, 'business_logo_id', true);
$title = get_the_title($id);
$permalink = get_permalink($id);
$city = get_post_meta($id, 'city', true);
$phone = get_post_meta($id, 'business_phone', true);
$owners = (array)get_post_meta($id, 'owners', true);
$flags = ['veteran'=>false,'sal'=>false,'auxiliary'=>false];
foreach ($owners as $o) {
    $aff = $o['owner_affil'] ?? '';
    if (isset($flags[$aff])) $flags[$aff] = true;
}
?>
<article class="p116bd-card">
  <a class="p116bd-card__link" href="<?php echo esc_url($permalink); ?>">
    <div class="p116bd-card__media">
      <?php
        if ($logo_id) {
            echo wp_get_attachment_image($logo_id, 'medium');
        } else {
            echo get_the_post_thumbnail($id, 'medium');
        }
      ?>
    </div>
    <div class="p116bd-card__body">
      <h3 class="p116bd-card__title"><?php echo esc_html($title); ?></h3>
      <div class="p116bd-card__meta">
        <?php if ($city): ?><span class="p116bd-city"><?php echo esc_html($city); ?></span><?php endif; ?>
        <?php if ($phone): ?><span class="p116bd-phone"><?php echo esc_html(CPTClass::format_phone($phone)); ?></span><?php endif; ?>
      </div>
      <?php if (!empty($owners)): ?>
      <div class="p116bd-card__owners">
        <?php echo esc_html(implode(', ', array_map(function($o){ return $o['owner_name'] ?? '';}, array_slice($owners, 0, 2)))); ?>
      </div>
      <?php endif; ?>
      <div class="p116bd-card__excerpt"><?php echo esc_html(get_the_excerpt($id)); ?></div>
      <?php if ($flags['veteran'] || $flags['sal'] || $flags['auxiliary']): ?>
        <div class="p116bd-row__emblems">
          <?php if ($flags['veteran']): ?><img class="p116bd-flag-icon" alt="<?php esc_attr_e('American Legion', 'post116-business-directory'); ?>" src="<?php echo esc_url(\P116BD\P116BD_PLUGIN_URL . 'public/icons/TAL-emblem-full-detail-RGB.png'); ?>" /><?php endif; ?>
          <?php if ($flags['sal']): ?><img class="p116bd-flag-icon" alt="<?php esc_attr_e('SAL', 'post116-business-directory'); ?>" src="<?php echo esc_url(\P116BD\P116BD_PLUGIN_URL . 'public/icons/SAL-Emblem.png'); ?>" /><?php endif; ?>
          <?php if ($flags['auxiliary']): ?><img class="p116bd-flag-icon" alt="<?php esc_attr_e('Auxiliary', 'post116-business-directory'); ?>" src="<?php echo esc_url(\P116BD\P116BD_PLUGIN_URL . 'public/icons/Auxiliary-Emblem.png'); ?>" /><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </a>
  </article>
