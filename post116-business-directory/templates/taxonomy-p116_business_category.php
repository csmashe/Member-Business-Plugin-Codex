<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$term = get_queried_object();
?>
<main class="p116bd-archive">
  <header class="p116bd-archive__header">
    <h1><?php echo esc_html($term->name); ?></h1>
    <p class="p116bd-legal"><?php echo esc_html__('American Legion Post 116 is not liable for or endorsing any listed businesses. Please independently verify their work quality, licenses, and insurance.', 'post116-business-directory'); ?></p>
  </header>
  <div class="p116bd-grid">
    <?php if (have_posts()) : while (have_posts()) : the_post();
      include P116BD_PLUGIN_DIR . 'templates/parts/card-business.php';
    endwhile; else: ?>
      <p><?php esc_html_e('No businesses found in this category.', 'post116-business-directory'); ?></p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>
