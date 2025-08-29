<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$id = get_the_ID();
$owners = (array)get_post_meta($id, 'owners', true);
$links = (array)get_post_meta($id, 'links', true);
?>
<main class="p116bd-single">
  <article <?php post_class(); ?>>
    <header class="p116bd-single__header">
      <h1><?php the_title(); ?></h1>
      <div class="p116bd-flags">
        <?php if (get_post_meta($id, 'veteran_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('Veteran', 'post116-business-directory'); ?></span><?php endif; ?>
        <?php if (get_post_meta($id, 'sons_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('SAL', 'post116-business-directory'); ?></span><?php endif; ?>
        <?php if (get_post_meta($id, 'auxiliary_owned', true)) : ?><span class="p116bd-badge"><?php esc_html_e('Auxiliary', 'post116-business-directory'); ?></span><?php endif; ?>
      </div>
    </header>
    <div class="p116bd-single__grid">
      <div class="p116bd-single__media"><?php the_post_thumbnail('large'); ?></div>
      <div class="p116bd-single__details">
        <ul class="p116bd-list">
          <?php $phone = get_post_meta($id, 'business_phone', true); if ($phone): ?>
            <li><strong><?php esc_html_e('Phone:', 'post116-business-directory'); ?></strong> <?php echo esc_html($phone); ?></li>
          <?php endif; ?>
          <?php $email = get_post_meta($id, 'business_email', true); if ($email): ?>
            <li><strong><?php esc_html_e('Email:', 'post116-business-directory'); ?></strong> <a href="mailto:<?php echo antispambot(esc_attr($email)); ?>"><?php echo antispambot(esc_html($email)); ?></a></li>
          <?php endif; ?>
          <?php $website = get_post_meta($id, 'website_url', true); if ($website): ?>
            <li><strong><?php esc_html_e('Website:', 'post116-business-directory'); ?></strong> <a target="_blank" rel="noopener" href="<?php echo esc_url($website); ?>"><?php echo esc_html($website); ?></a></li>
          <?php endif; ?>
          <?php $city = get_post_meta($id, 'city', true); if ($city): ?>
            <li><strong><?php esc_html_e('City:', 'post116-business-directory'); ?></strong> <?php echo esc_html($city); ?></li>
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
</main>
<?php get_footer(); ?>

