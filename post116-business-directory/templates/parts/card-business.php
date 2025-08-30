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
$flags = [];
if (get_post_meta($id, 'veteran_owned', true)) $flags[] = __('Veteran', 'post116-business-directory');
if (get_post_meta($id, 'sons_owned', true)) $flags[] = __('SAL', 'post116-business-directory');
if (get_post_meta($id, 'auxiliary_owned', true)) $flags[] = __('Auxiliary', 'post116-business-directory');
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
      <?php if (!empty($flags)): ?>
      <div class="p116bd-card__flags">
        <?php foreach ($flags as $f): ?><span class="p116bd-badge"><?php echo esc_html($f); ?></span><?php endforeach; ?>
      </div>
      <?php endif; ?>
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
    </div>
  </a>
  </article>
