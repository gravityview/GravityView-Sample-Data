<?php
/*
Plugin Name: GravityView - Sample Data Importer
Plugin URI: http://gravityview.co
Description: Import data using the awesome Mockaroo.com website API.
Version: 1.0
Author: katzwebservices
Author URI: http://katz.co
*/

include_once plugin_dir_path( __FILE__ ). 'settings.php';

add_action('plugins_loaded', 'kws_gv_sample_data_run_data_import');

function kws_gv_sample_data_run_data_import() {

	if( !isset($_GET['form_id'] ) || !isset( $_GET['mockaroo_key'] ) || !current_user_can('manage_options' ) ) {
		return;
	}

	if( !wp_verify_nonce( $_GET['gv_sample_data_run_import'], 'gv_sample_data_run_import_'.$_GET['form_id'] ) ) {
		wp_die( 'Not valid - link may have expired. Try again. ');
	}

	// Prevent timeout
	set_time_limit( 0 );

	$options = get_option('gv_sample_data');

	$count = !empty( $options['count'] ) ? $options['count'] : 250;

	gv_sample_data_import( (int)$_GET['form_id'], esc_attr( $_GET['mockaroo_key'] ), $count );

}

function gv_sample_data_import( $form_id, $mockaroo_key, $count = 250 ) {

		$form = GFAPI::get_form( $form_id );

		if( empty( $form ) ) { return; }

		$path = add_query_arg( array( 'count' => (int)$count ), sprintf( 'http://www.mockaroo.com/%s/download', $mockaroo_key ) );

		$response = wp_remote_get( $path , array(
			'timeout' => 300
		));

		$json = wp_remote_retrieve_body( $response );

		$data = json_decode( $json, true );

		// We've got to flatten the array down
		foreach ( $data as $index => $datem ) {

			foreach ($datem as $key => $value) {

				// Get the form field for this column so we can check if we need
				// to format it differently.
				$gf_form_field = '';
				foreach ( $form['fields'] as $field ) {
					if( $field['id'] == $key ) {
						$gf_form_field = $field;

						switch( $field['type'] ) {
							case 'list':
								$value = empty($value) ? NULL : serialize( (array)$value );
								break;
							case 'date':
								$value = date( 'Y-m-d' , strtotime( $value ) );
								break;
							case 'fileupload':

								if( is_array( $value) ) {
									foreach ($value as &$image) {
										$image = $images[ rand( 0, sizeof($images) -1 ) ];
									}
								} else {

									if( !preg_match( '/http/', $value ) ) {

										$images = kws_gf_import_get_flickr_photos( $value );
										if( $images ) {
											$value = $images[ rand( 0, sizeof($images) -1 ) ];
										} else {
											$value = NULL;
										}
									}
								}

								if( rgar($field,"multipleFiles") ) {
									$value = is_array( $value ) ? $value : array($value);
									$value = json_encode($value);
								}

								break;
							case 'website':
							case 'email':
								// Replace China emails, which get blocked as spam
								$value = is_string( $value ) ? str_replace('.cn', '.com', $value) : $value;
								break;
						}

					}
				}

				// Mockaroo treats `.` as an array item, so we use `_` instead for field dots.
				if( preg_match( '/_/', $key ) ) {
					unset( $data[ $index ][ $key ] );
					$key = str_replace( '_', '.', $key );
					$data[ $index ][ $key ] = $value;
				}

				$value = str_replace('[null]', '', $value);

				$data[ $index ][ $key ] = $value;
			}
		}

	$entry_ids = GFAPI::add_entries( $data, $form_id );

	wp_redirect( add_query_arg(array('form_id' => $form_id, 'success' => count($entry_ids)), remove_query_arg(array('form_id', 'mockaroo_key', 'gv_sample_data_run_import') ) ));
}

/**
 * Import images for entries from Flickr based on a keyword
 *
 * @param  string $keyword Keyword to use for image search
 * @return array          Array of image URLs
 */
function kws_gf_import_get_flickr_photos( $keyword = 'screenshot' ) {

	if( $images = get_transient( 'flickr_pictures_'.$keyword ) ) {
		return $images;
	}

	$response = wp_remote_get( 'http://pipes.yahoo.com/pipes/pipe.run?_id=c3e842dba7a8b503d491908b15059ad4&_render=json&text='.urlencode($keyword) );

	$pictures = wp_remote_retrieve_body( $response );

	$pictures = json_decode( $pictures );

	if( empty( $pictures->value->items ) ) {
		return false;
	}

	if( empty($pictures->value->items[0]) || empty( $pictures->value->items[0]->photos->photo ) ) {
		return false;
	}

	$items = $pictures->value->items[0]->photos->photo;
	if( !empty( $items ) ) {
		foreach ($items as $item) {
			if( isset( $item->url_l ) ) {
				$images[] = $item->url_l;
			} else if( isset( $item->url_m ) ) {
				$images[] = $item->url_m;
			} else if( isset( $item->url_o ) ) {
				$images[] = $item->url_o;
			}
		}
	}

	set_transient( 'flickr_pictures_'.$keyword, $images, DAY_IN_SECONDS );

	return $images;
}