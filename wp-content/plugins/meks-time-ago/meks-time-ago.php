<?php
/*
Plugin Name: Meks Time Ago
Plugin URI: https://mekshq.com
Description: Automatically change your post date display to "time ago" format like "1 hour ago", "3 weeks ago", "2 months ago" etc...
Version: 1.1.6
time_ago: Meks
time_ago URI: https://mekshq.com
Text Domain: meks-time-ago
Domain Path: /languages
*/

/* Prevent Direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

/* Define BaseName */
if ( !defined( 'MEKS_TA_BASENAME' ) )
	define( 'MEKS_TA_BASENAME', plugin_basename( __FILE__ ) );

/* Define internal path */
if ( !defined( 'MEKS_TA_PATH' ) )
	define( 'MEKS_TA_PATH', plugin_dir_path( __FILE__ ) );

/* Define internal version for possible update changes */
define( 'MEKS_TA_VER', '1.1.6' );

/* Load Up the text domain */
function meks_ta_load_textdomain() {
	load_plugin_textdomain( 'meks-time-ago', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'meks_ta_load_textdomain' );

/* Check if we're running compatible software */
if ( version_compare( PHP_VERSION, '5.2', '<' ) && version_compare( WP_VERSION, '3.7', '<' ) ) {
	if ( is_admin() ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
		wp_die( __( 'Meks Time Ago plugin requires WordPress 3.8 and PHP 5.3 or greater. The plugin has now disabled itself', 'meks-time-ago' ) );
	}
}

/* Let's load up our plugin */

function meks_ta_admin_init() {
	require_once MEKS_TA_PATH.'includes/class.backend.php';
	new Meks_TA_Admin();
}

function meks_ta_frontend_init() {
	require_once MEKS_TA_PATH.'includes/class.frontend.php';
	new Meks_TA_Frontend();
}

if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX) ) {

    add_action('loop_start', 'meks_ta_frontend_init', 50);

} else {

    add_action('plugins_loaded', 'meks_ta_admin_init', 15);

}

/**
 * Upsell Meks themes with notice info
 *
 * @return void
 */
add_action( 'admin_notices', 'meks_admin_notice__info' );

if ( ! function_exists( 'meks_admin_notice__info' ) ) :

	function meks_admin_notice__info() {

		$meks_themes  = array( 'shamrock', 'awsm', 'safarica', 'seashell', 'sidewalk', 'throne', 'voice', 'herald', 'vlog', 'gridlove', 'pinhole', 'typology', 'trawell', 'opinion', 'johannes', 'megaphone', 'toucan', 'roogan' );
		$active_theme = get_option( 'template' );


		if ( ! in_array( $active_theme, $meks_themes ) ) {

			if ( get_option('has_transient') == 0 ) {
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
							echo sprintf( __( 'You are currently using %1$s theme. Did you know that Meks plugins give you more features and flexibility with one of our <a href="%2$s">Meks themes</a>?', 'meks-time-ago' ), $all_themes[ $active_theme ], 'https://1.envato.market/4DE2o' );
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
 * Colose/remove info notice with ajax
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

/**
 * Add admin scripts
 *
 * @return void
 */
add_action( 'admin_enqueue_scripts', 'meks_time_ago_enqueue_admin_scripts' );

if ( !function_exists( 'meks_time_ago_enqueue_admin_scripts' ) ) :
	function meks_time_ago_enqueue_admin_scripts() {
        wp_enqueue_script( 'meks-time-ago', plugin_dir_url( __FILE__ ) . 'js/admin.js', array('jquery'), MEKS_TA_VER );
	}
endif;
