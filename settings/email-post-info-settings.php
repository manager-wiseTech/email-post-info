<?php
register_setting('epi-plugin-settings','epi_plugin_email_txtbox');
register_setting('epi-plugin-settings','epi_plugin_posttype_checkboxes');
add_settings_section(
        'epi_plugin_email_txtbox_section',
        'EPI Plugin Settings',
        'epi_plugin_email_txtbox_section_cb',
        'epi-plugin-settings'
    );
add_settings_field(
        'epi_plugin_email_settings_field',
        'Whom do you want to send Email: ',
        'epi_plugin_email_settings_field_cb',
        'epi-plugin-settings',
        'epi_plugin_email_txtbox_section'
    );
add_settings_field(
        'epi_plugin_posttype_checkboxes_settings_field',
        'Please select post types you want to include in report: ',
        'epi_plugin_posttype_checkboxes_field_cb',
        'epi-plugin-settings',
        'epi_plugin_email_txtbox_section'
    );

function epi_plugin_email_txtbox_section_cb(){
  echo "";
}
function epi_plugin_email_settings_field_cb(){
  
  $email = get_option('epi_plugin_email_txtbox');
  ?>
  <input type="text"  name="epi_plugin_email_txtbox" value="<?php echo  $email ?>"  /><span class="description"></span>
  <?php


}


function epi_plugin_posttype_checkboxes_field_cb(){
  $checked = $selected = $disabled = $value = NULL;
 // Excluded CPTs. You can expand the list.
$exclude_cpts = array(
    'portfolio','ads','forum','topic','reply'
);
// All CPTs.
$cpts = get_post_types( array(
    'public'   => true,
    '_builtin' => false//remove bit_in cpts like attachment and page etc...
) );
// remove Excluded CPTs from All CPTs.
foreach($exclude_cpts as $exclude_cpt)
    unset($cpts[$exclude_cpt]);

$post_types =  $cpts;
  $opt_arr = get_option('epi_plugin_posttype_checkboxes');
  if (!is_array($opt_arr)) {
    $opt_arr = array();
  }
  if (in_array('post',$opt_arr)) {
    $value = 'post';
  }
  ?>
  <ul>
    <li><input type="checkbox" value="post" name="epi_plugin_posttype_checkboxes[]" <?php checked($value, 'post', TRUE ); ?> /> Wordpress Default Post </li>
  <?php
    foreach ($post_types as $key) {
      if (in_array($key,$opt_arr)) {
    $value = $key;
  }
      ?>

      <li><input type="checkbox" value="<?php echo $key; ?>" name="epi_plugin_posttype_checkboxes[]" <?php checked( $value, $key, TRUE ); ?> /><?php echo $key; ?></li>
      <?php
    }
    ?>     
  </ul>

    <?php


   
    
}
?>
