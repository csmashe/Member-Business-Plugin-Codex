<?php if (!defined('ABSPATH')) { exit; } ?>
<style>
.p116bd-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.p116bd-repeater .p116bd-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:8px}
.p116bd-repeater .p116bd-row input, .p116bd-repeater .p116bd-row select{width:100%}
.p116bd-badge{display:inline-block;padding:2px 6px;background:#eee;border-radius:3px;margin-right:4px;font-size:12px}
</style>
<?php if (isset($_GET['p116bd_city_required'])): ?>
  <div class="notice notice-error"><p><?php esc_html_e('City is required for Business entries. The post was saved as draft.', 'post116-business-directory'); ?></p></div>
<?php endif; ?>

<h3><?php esc_html_e('Owners', 'post116-business-directory'); ?></h3>
<div class="p116bd-repeater" data-repeater="owners">
  <div class="p116bd-rows">
  <?php if (!empty($owners)) : foreach ($owners as $o): $aff = $o['owner_affil'] ?? ''; ?>
    <div class="p116bd-row">
      <input type="text" name="p116bd_owner_name[]" placeholder="<?php esc_attr_e('Name', 'post116-business-directory'); ?>" value="<?php echo esc_attr($o['owner_name'] ?? ''); ?>"/>
      <input type="text" name="p116bd_owner_role[]" placeholder="<?php esc_attr_e('Role', 'post116-business-directory'); ?>" value="<?php echo esc_attr($o['owner_role'] ?? ''); ?>"/>
      <input type="email" name="p116bd_owner_email[]" placeholder="<?php esc_attr_e('Email', 'post116-business-directory'); ?>" value="<?php echo esc_attr($o['owner_email'] ?? ''); ?>"/>
      <input type="text" name="p116bd_owner_phone[]" placeholder="<?php esc_attr_e('Phone', 'post116-business-directory'); ?>" value="<?php echo esc_attr($o['owner_phone'] ?? ''); ?>"/>
      <input type="url" name="p116bd_owner_website[]" placeholder="<?php esc_attr_e('Website', 'post116-business-directory'); ?>" value="<?php echo esc_attr($o['owner_website'] ?? ''); ?>"/>
      <select name="p116bd_owner_affil[]">
        <option value="" <?php selected($aff, ''); ?>><?php esc_html_e('— Ownership —', 'post116-business-directory'); ?></option>
        <option value="veteran" <?php selected($aff, 'veteran'); ?>><?php esc_html_e('Veteran', 'post116-business-directory'); ?></option>
        <option value="sal" <?php selected($aff, 'sal'); ?>><?php esc_html_e('SAL', 'post116-business-directory'); ?></option>
        <option value="auxiliary" <?php selected($aff, 'auxiliary'); ?>><?php esc_html_e('Auxiliary', 'post116-business-directory'); ?></option>
      </select>
      <button type="button" class="button p116bd-remove">&times;</button>
    </div>
  <?php endforeach; endif; ?>
  </div>
  <button type="button" class="button button-secondary p116bd-add-owner"><?php esc_html_e('Add Owner', 'post116-business-directory'); ?></button>
</div>

<h3><?php esc_html_e('Contact', 'post116-business-directory'); ?></h3>
<div class="p116bd-grid-2">
  <p>
    <label><?php esc_html_e('Business Logo', 'post116-business-directory'); ?><br/>
      <input type="hidden" id="p116bd_business_logo_id" name="p116bd_business_logo_id" value="<?php echo esc_attr($fields['business_logo_id']); ?>" />
      <div id="p116bd_logo_preview" style="margin:6px 0;">
        <?php if (!empty($fields['business_logo_id'])): ?>
          <?php echo wp_get_attachment_image((int)$fields['business_logo_id'], 'medium'); ?>
        <?php endif; ?>
      </div>
      <button type="button" class="button" id="p116bd_logo_select"><?php esc_html_e('Select Logo', 'post116-business-directory'); ?></button>
      <button type="button" class="button" id="p116bd_logo_remove" <?php echo empty($fields['business_logo_id']) ? 'style="display:none"' : ''; ?>><?php esc_html_e('Remove', 'post116-business-directory'); ?></button>
    </label>
  </p>
  <p><label><?php esc_html_e('Business Phone', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_business_phone" value="<?php echo esc_attr($fields['business_phone']); ?>"/></label></p>
  <p><label><?php esc_html_e('Business Email', 'post116-business-directory'); ?><br/>
    <input type="email" name="p116bd_business_email" value="<?php echo esc_attr($fields['business_email']); ?>"/></label></p>
  <p><label><?php esc_html_e('Website URL', 'post116-business-directory'); ?><br/>
    <input type="url" name="p116bd_website_url" value="<?php echo esc_attr($fields['website_url']); ?>"/></label></p>
</div>

<h3><?php esc_html_e('Address', 'post116-business-directory'); ?></h3>
<div class="p116bd-grid-2">
  <p><label><?php esc_html_e('City (required)', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_city" value="<?php echo esc_attr($fields['city']); ?>" required/></label></p>
  <p><label><?php esc_html_e('Address 1', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_address1" value="<?php echo esc_attr($fields['address1']); ?>"/></label></p>
  <p><label><?php esc_html_e('Address 2', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_address2" value="<?php echo esc_attr($fields['address2']); ?>"/></label></p>
  <p><label><?php esc_html_e('State', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_state" value="<?php echo esc_attr($fields['state']); ?>"/></label></p>
  <p><label><?php esc_html_e('Postal Code', 'post116-business-directory'); ?><br/>
    <input type="text" name="p116bd_postal_code" value="<?php echo esc_attr($fields['postal_code']); ?>"/></label></p>
</div>

<!-- Ownership flags now derive from per-owner affiliation -->

<h3><?php esc_html_e('Services Offered (short list)', 'post116-business-directory'); ?></h3>
<p><textarea name="p116bd_services_offered" rows="3" style="width:100%"><?php echo esc_textarea($fields['services_offered']); ?></textarea></p>

<h3><?php esc_html_e('Links', 'post116-business-directory'); ?></h3>
<div class="p116bd-repeater" data-repeater="links">
  <div class="p116bd-rows">
  <?php if (!empty($fields['links'])) : foreach ($fields['links'] as $l): ?>
    <div class="p116bd-row" style="grid-template-columns:2fr 3fr auto">
      <input type="text" name="p116bd_link_label[]" placeholder="<?php esc_attr_e('Label', 'post116-business-directory'); ?>" value="<?php echo esc_attr($l['link_label'] ?? ''); ?>"/>
      <input type="url" name="p116bd_link_url[]" placeholder="<?php esc_attr_e('URL', 'post116-business-directory'); ?>" value="<?php echo esc_attr($l['link_url'] ?? ''); ?>"/>
      <button type="button" class="button p116bd-remove">&times;</button>
    </div>
  <?php endforeach; endif; ?>
  </div>
  <button type="button" class="button button-secondary p116bd-add-link"><?php esc_html_e('Add Link', 'post116-business-directory'); ?></button>
</div>

<p><label><input type="checkbox" name="p116bd_show_in_directory" value="1" <?php checked($fields['show_in_directory']); ?>/> <?php esc_html_e('Show in directory', 'post116-business-directory'); ?></label></p>

<script>
(function(){
  // Ensure labels are available even if localized script loads later
  window.p116bdLabels = window.p116bdLabels || {
    name: '<?php echo esc_js(__('Name', 'post116-business-directory')); ?>',
    role: '<?php echo esc_js(__('Role', 'post116-business-directory')); ?>',
    email: '<?php echo esc_js(__('Email', 'post116-business-directory')); ?>',
    phone: '<?php echo esc_js(__('Phone', 'post116-business-directory')); ?>',
    website: '<?php echo esc_js(__('Website', 'post116-business-directory')); ?>',
    ownership: '<?php echo esc_js(__('— Ownership —', 'post116-business-directory')); ?>',
    veteran: '<?php echo esc_js(__('Veteran', 'post116-business-directory')); ?>',
    sal: '<?php echo esc_js(__('SAL', 'post116-business-directory')); ?>',
    auxiliary: '<?php echo esc_js(__('Auxiliary', 'post116-business-directory')); ?>',
    link_label: '<?php echo esc_js(__('Label', 'post116-business-directory')); ?>',
    link_url: '<?php echo esc_js(__('URL', 'post116-business-directory')); ?>',
    mediaSelectLogo: '<?php echo esc_js(__('Select Logo', 'post116-business-directory')); ?>',
    mediaUseLogo: '<?php echo esc_js(__('Use this logo', 'post116-business-directory')); ?>',
  };
  function addRow(container, type){
    const rows = container.querySelector('.p116bd-rows');
    let row;
    if(type==='owners'){
      row = document.createElement('div');
      row.className = 'p116bd-row';
      row.innerHTML = '\
<input type="text" name="p116bd_owner_name[]" placeholder="' + p116bdLabels.name + '"/>\
<input type="text" name="p116bd_owner_role[]" placeholder="' + p116bdLabels.role + '"/>\
<input type="email" name="p116bd_owner_email[]" placeholder="' + p116bdLabels.email + '"/>\
<input type="text" name="p116bd_owner_phone[]" placeholder="' + p116bdLabels.phone + '"/>\
<input type="url" name="p116bd_owner_website[]" placeholder="' + p116bdLabels.website + '"/>\
<select name="p116bd_owner_affil[]">\
  <option value="">' + p116bdLabels.ownership + '</option>\
  <option value="veteran">' + p116bdLabels.veteran + '</option>\
  <option value="sal">' + p116bdLabels.sal + '</option>\
  <option value="auxiliary">' + p116bdLabels.auxiliary + '</option>\
</select>\
<button type="button" class="button p116bd-remove">&times;</button>';
    } else if(type==='links'){
      row = document.createElement('div');
      row.className = 'p116bd-row';
      row.style.gridTemplateColumns = '2fr 3fr auto';
      row.innerHTML = '\
<input type="text" name="p116bd_link_label[]" placeholder="' + p116bdLabels.link_label + '"/>\
<input type="url" name="p116bd_link_url[]" placeholder="' + p116bdLabels.link_url + '"/>\
<button type="button" class="button p116bd-remove">&times;</button>';
    }
    rows.appendChild(row);
  }
  document.addEventListener('click', function(e){
    if(e.target.matches('.p116bd-add-owner')){
      e.preventDefault();
      addRow(e.target.closest('.p116bd-repeater'), 'owners');
    }
    if(e.target.matches('.p116bd-add-link')){
      e.preventDefault();
      addRow(e.target.closest('.p116bd-repeater'), 'links');
    }
    if(e.target.matches('.p116bd-remove')){
      e.preventDefault();
      const row = e.target.closest('.p116bd-row');
      row.parentNode.removeChild(row);
    }
  });

  // Media uploader for Logo
  let p116bdLogoFrame;
  function setLogo(id){
    const input = document.getElementById('p116bd_business_logo_id');
    const preview = document.getElementById('p116bd_logo_preview');
    const removeBtn = document.getElementById('p116bd_logo_remove');
    input.value = id || '';
    if(id){
      wp.media.attachment(id).fetch().then(function(){
        const img = wp.media.attachment(id).get('sizes');
        // Fallback to full if no medium
        const url = (img && img.medium ? img.medium.url : wp.media.attachment(id).get('url'));
        preview.innerHTML = '<img src="'+url+'" style="max-width:100%;height:auto;" />';
      });
      removeBtn.style.display = '';
    } else {
      preview.innerHTML = '';
      removeBtn.style.display = 'none';
    }
  }
  document.getElementById('p116bd_logo_select').addEventListener('click', function(e){
    e.preventDefault();
    if(p116bdLogoFrame){ p116bdLogoFrame.open(); return; }
    p116bdLogoFrame = wp.media({
      title: p116bdLabels.mediaSelectLogo,
      button: { text: p116bdLabels.mediaUseLogo },
      library: { type: 'image' },
      multiple: false
    });
    p116bdLogoFrame.on('select', function(){
      const selection = p116bdLogoFrame.state().get('selection');
      const att = selection.first();
      if(att){ setLogo(att.id); }
    });
    p116bdLogoFrame.open();
  });
  document.getElementById('p116bd_logo_remove').addEventListener('click', function(e){
    e.preventDefault();
    setLogo('');
  });
})();
</script>
