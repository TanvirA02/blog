<?php
/*
Plugin Name: Meks ThemeForest Smart Widget
Plugin URI: https://mekshq.com
Description: A simple and powerful WordPress plugin with which you can display ThemeForest items as a WordPress widget. Several smart options are provided for selecting and ordering. You can select ThemeForest latest items, popular items or items from one or more specific users. Optionally, you can connect items with your affiliate links as well.
Author: Meks
Version: 1.4
Author URI: https://mekshq.com
Text Domain: meks-themeforest-smart-widget
Domain Path: /languages
License: GPL3
*/

define( 'MTW_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MTW_PLUGIN_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MKS_TF_WIDGET_VER', '1.4' );

/* Initialize Widget */
if ( !function_exists( 'mtw_widget_init' ) ):
    function mtw_widget_init() {
        require_once MTW_PLUGIN_DIR.'inc/class-themeforest-widget.php';
        register_widget( 'MKS_ThemeForest_Widget' );
    }
endif;

add_action( 'widgets_init', 'mtw_widget_init' );

/* Load text domain */
function mks_load_tf_widget_text_domain() {
    load_plugin_textdomain( 'meks-themeforest-smart-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'mks_load_tf_widget_text_domain' );


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
							echo sprintf( __( 'You are currently using %1$s theme. Did you know that Meks plugins give you more features and flexibility with one of our <a href="%2$s">Meks themes</a>?',  'meks-themeforest-smart-widget' ), $all_themes[ $active_theme ], 'https://1.envato.market/4DE2o' );
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
