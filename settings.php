<?php

add_action('admin_menu', 'gv_sample_data_add_management_page', 1000);

// Add menu page
function gv_sample_data_add_management_page() {
  add_submenu_page( 'edit.php?post_type=gravityview', 'Sample Data Importer', 'Sample Data Import', 'manage_options', 'gv-data-importer', 'gv_sample_data_settings_page');
}


add_action( 'admin_init', 'gv_sample_data_register_setting' );

function gv_sample_data_register_setting() {
  register_setting( 'gv_sample_data_options', 'gv_sample_data' );
}

// Draw the menu page itself
function gv_sample_data_settings_page() {

  if( !class_exists( 'GFAPI' ) ) {
    echo '<h2>Activate Gravity Forms!</h2>';
    return;
  }

  if( isset( $_GET['success'] ) ) {
    ?>
  <div class="updated" id="message">
    <?php echo wpautop(sprintf( '%d entries were successfully added to Form #%d. <a href="%s">View Entries</a>', $_GET['success'], $_GET['form_id'], admin_url('admin.php?page=gf_entries&amp;id='.intval($_GET['form_id']) ) )); ?>
  </div>
    <?php
  } else if( isset( $_GET['error'] ) ) {
    ?>
  <div class="error" id="message">
    <?php echo wpautop(sprintf( 'There was an error connecting to Mockaroo: %s', '<code>'.esc_html( $_GET['error'] ).'</code>' ) ); ?>
  </div>
    <?php
  }

    ?>
    <div class="wrap">
        <h2>Import GravityView Data</h2>
        <h3 class="subsubsub">Map the forms to the View Presets, save the form, then you'll see an Import link next to each preset.</h3>
        <form method="post" action="options.php">
            <?php settings_fields('gv_sample_data_options'); ?>
            <?php

            $options = get_option('gv_sample_data', array('count' => 250));

            $forms = GFAPI::get_forms();

            $presets = array(
              'Issue Tracker' => '8b3ad4d0',
              'Website Directory' => '1d9905e0',
              'Profiles' => '2157c7a0',
              'Staff Profiles' => 'e8799370',
              'Event Listing' => '31e534d0',
              'Business Data' => '5d69bc10',
              'Business Listing' => '5d69bc10',
              'Resume Board' => '4d7d9b40',
              'Job Board' => '2b1c5470',
            );

            ?>
            <table class="form-table">

        <?php

        foreach ($presets as $title => $mockaroo_key ) {

          $option_key = sanitize_title( $title . '-' .$mockaroo_key );
        ?>

        <tr valign="top">
          <th scope="row"><?php echo esc_html( $title ); ?></th>
            <td>
              <select name="gv_sample_data[<?php echo esc_attr( $option_key ); ?>]">
                <option value="">Select a Form</option>
              <?php
              foreach ($forms as $form) {
                $value = isset( $options[ $option_key ] ) ? $options[ $option_key ] : NULL;
              ?>
                <option value="<?php echo intval( $form['id'] ); ?>" <?php selected( $form['id'], $value, true ); ?>><?php echo esc_attr( $form['title'] ); ?>
              <?php } ?>
              </select>
            <?php if( !empty( $options[ $option_key ] ) ) { ?>
              <a href="<?php echo wp_nonce_url( add_query_arg( array('form_id' => $options[ $option_key ],'mockaroo_key' => $mockaroo_key ), remove_query_arg( array('run-import', 'form_id', 'mockaroo_key', 'success', 'gv_sample_data_run_import'))), 'gv_sample_data_run_import_'.$options[ $option_key ], 'gv_sample_data_run_import' ); ?>">Import</a>
              <?php } ?>
            </td>
        </tr>
        <?php
        }

            ?>
              <tr>
                <th>Number to Import</th>
                <td>
                  <select name="gv_sample_data[count]">
                    <?php
                    $i = 50;
                    while( $i <= 2100 ) { ?>
                    <option value="<?php echo $i; ?>" <?php selected( $i, isset( $options['count'] ) ? $options['count'] : 250, true );?>><?php echo $i; ?></option>
                    <?php
                      $i = ( $i > 500 ) ? $i + 250 : $i + 50;
                    }
                    ?>
                  </select>
                </td>
              </tr>
            </table>

            <p class="submit">
              <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </div>
    <?php
}