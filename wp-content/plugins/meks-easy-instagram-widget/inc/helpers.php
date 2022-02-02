<?php

/**
 * Debug (log) function
 *
 * Outputs any content into log file in theme root directory
 *
 * @param mixed   $mixed Content to output
 * @since  1.0
 */

if ( !function_exists( 'meks_log' ) ):
	function meks_log( $mixed ) {

		if ( !function_exists( 'WP_Filesystem' ) || !WP_Filesystem() ) {
			return false;
		}

		if ( is_array( $mixed ) ) {
			$mixed = print_r( $mixed, 1 );
		} else if ( is_object( $mixed ) ) {
				ob_start();
				var_dump( $mixed );
				$mixed = ob_get_clean();
			}

		global $wp_filesystem;
		$existing = $wp_filesystem->get_contents(  MEKS_INSTAGRAM_WIDGET_DIR . 'log' );
		$wp_filesystem->put_contents( MEKS_INSTAGRAM_WIDGET_DIR . 'log', $existing. $mixed . PHP_EOL );
	}
endif;

/**
 * Get JS settings
 *
 * Function creates list of settings from thme options to pass
 * them to global JS variable so we can use it in JS files
 *
 * @return array List of JS settings
 * @since  1.0
 */

if ( ! function_exists( 'meks_get_js_settings' ) ) :
	function meks_get_js_settings() {
		$js_settings = array();

		$protocol                 = is_ssl() ? 'https://' : 'http://';
		$js_settings['ajax_url']  = admin_url( 'admin-ajax.php', $protocol );
		$js_settings['nonce']  = wp_create_nonce( 'ajax-nonce' );
		$js_settings = apply_filters( 'meks_modify_js_settings', $js_settings );

		return $js_settings;
	}
endif;

/**
 * Function GET Instagram Business connected accounts
 *
 * @return array
 * @since  1.0
 */

if ( ! function_exists( 'meks_get_account_data_from_token' ) ) :
	function meks_get_accounts_from_token( $token ) {

		$result = wp_remote_get( 'https://graph.facebook.com/v7.0/me/accounts?fields=access_token,instagram_business_account&access_token='.$token );

		$pages_data = '{}';

		if ( ! is_wp_error( $result ) ) {
			$pages_data = $result['body'];
		} else {
			return $pages_data;
		}

		$connected_accounts = json_decode( $pages_data );
		$instagram_account_data = [];

		foreach ( $connected_accounts->data as $key => $page_data ) {

			if ( isset( $page_data->instagram_business_account ) && isset( $page_data->access_token ) ) {

				$instagram_business_id = $page_data->instagram_business_account->id;

				$result = wp_remote_get( 'https://graph.facebook.com/' . $instagram_business_id . '?fields=name,username,profile_picture_url&access_token=' . $page_data->access_token );

				if ( ! is_wp_error( $result ) ) {
					$instagram_account_info = $result['body'];
				} else {
					$instagram_account_info['error'] = $result['body'];
				}

				$account_info = json_decode( $instagram_account_info );
				$account_info->{'access_token'} = $page_data->access_token;;

				$instagram_account_data[] = $account_info;

			}

		}

		return $instagram_account_data;

	}
endif;

/**
 * Ajax - Send token and get connected accounts
 *
 * @return void
 * @since  1.0
 */

add_action( 'wp_ajax_meks_save_token', 'meks_save_token' );

if ( ! function_exists( 'meks_save_token' ) ) :
	function meks_save_token() {

		$access_token = sanitize_text_field( $_POST['access_token'] );

		$connection_data = meks_get_accounts_from_token( $access_token );

		echo wp_json_encode( $connection_data );
		die();

	}
endif;

/**
 * Ajax - Save info from Business connected account
 *
 * @since  1.0
 */

add_action( 'wp_ajax_meks_save_business_selected_account', 'meks_save_business_selected_account' );

if ( ! function_exists( 'meks_save_business_selected_account' ) ) :
	function meks_save_business_selected_account() {

		if ( !wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
			die();
		}

		if ( !current_user_can( 'manage_options' ) ) {
			die();
		}

		$options = get_option( 'meks_instagram_settings', array() );

		$options['access_token'] = sanitize_text_field( $_POST['access_token'] );
		$options['user_id'] = abs( $_POST['user_id'] );
		$options['username'] = sanitize_text_field( $_POST['username'] );
		$options['name'] = sanitize_text_field( $_POST['name'] );
		$options['picture_url'] = esc_url_raw( $_POST['image'] );
		$options['api_type'] = 'business';

		update_option( 'meks_instagram_settings', $options );

		die();

	}
endif;


/**
 * Notice info
 *
 * @return void
 */
add_action( 'admin_notices', 'meks_admin_notice__info' );

if ( ! function_exists( 'meks_admin_notice__info' ) ) :

	function meks_admin_notice__info() {

		$meks_themes  = array( 'shamrock', 'awsm', 'safarica', 'seashell', 'sidewalk', 'throne', 'voice', 'herald', 'vlog', 'gridlove', 'pinhole', 'typology', 'trawell', 'opinion', 'johannes', 'megaphone', 'toucan', 'roogan' );
		$active_theme = get_option( 'template' );


		if ( ! in_array( $active_theme, $meks_themes ) ) {

			if ( get_option( 'has_transient') == 0 ) {
				set_transient( 'meks_admin_notice_time_'. get_current_user_id() , true, WEEK_IN_SECONDS );
				update_option('has_transient', 1);
				update_option('track_transient', 1);
			}

			if (  !get_option('meks_admin_notice_info') || ( get_option('track_transient') && !get_transient( 'meks_admin_notice_time_'. get_current_user_id() ) ) ) {

				$all_themes = wp_get_themes();

?>
				<div class="meks-notice notice notice-info is-dismissible">
					<p>
						<?php
				echo sprintf( __( 'You are currently using %1$s theme. Did you know that Meks plugins give you more features and flexibility with one of our <a href="%2$s">Meks themes</a>?', 'meks-easy-instagram-widget' ), $all_themes[ $active_theme ], 'https://1.envato.market/4DE2o' );
?>
					</p>
				</div>
				<?php

			}
		} else {
			delete_option('meks_admin_notice_info');
			delete_option('has_transient');
			delete_option('track_transient');
		}
	}

endif;


/**
 * Close/remove info notice with ajax
 *
 * @return void
 */
add_action( 'wp_ajax_meks_remove_notification', 'meks_remove_notification' );
add_action( 'wp_ajax_nopriv_meks_remove_notification', 'meks_remove_notification' );

if ( !function_exists( 'meks_remove_notification' ) ) :
	function meks_remove_notification() {
		add_option('meks_admin_notice_info', 1);
		if ( !get_transient( 'meks_admin_notice_time_'. get_current_user_id() ) ) {
			update_option('track_transient', 0);
		}
	}
endif;
