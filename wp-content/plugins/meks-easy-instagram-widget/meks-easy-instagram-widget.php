<?php
/*
Plugin Name: Meks Easy Photo Feed Widget
Description: Easily display Instagram photos as a widget that looks good in (almost) any WordPress theme.
Version: 1.2.4
Author: Meks
Author URI: https://mekshq.com
Text Domain: meks-easy-instagram-widget
Domain Path: /languages
*/
 
/* Prevent Direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

define( 'MEKS_INSTAGRAM_WIDGET_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MEKS_INSTAGRAM_WIDGET_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MEKS_INSTAGRAM_WIDGET_VER', '1.2.4' );
define( 'MEKS_INSTAGRAM_BASENAME', plugin_basename( __FILE__ ) );

/* Initialize Options */
if ( !function_exists( 'meks_instagram_options_init' ) ):
    function meks_instagram_options_init() {
        require_once MEKS_INSTAGRAM_WIDGET_DIR.'inc/helpers.php';
        require_once MEKS_INSTAGRAM_WIDGET_DIR.'inc/class-instagram-options.php';
        Meks_Instagram_Options::get_instance();
    }
endif;

add_action( 'init', 'meks_instagram_options_init' );


/* Initialize Widget */
if ( !function_exists( 'meks_instagram_widget_init' ) ):
    function meks_instagram_widget_init() {
        require_once MEKS_INSTAGRAM_WIDGET_DIR.'inc/class-instagram-widget.php';
        register_widget( 'Meks_Instagram_Widget' );
        require_once MEKS_INSTAGRAM_WIDGET_DIR.'inc/template-functions.php';
    }
endif;

add_action( 'widgets_init', 'meks_instagram_widget_init' );

/* Load text domain */
function meks_load_instagram_widget_text_domain() {
    load_plugin_textdomain( 'meks-easy-instagram-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'meks_load_instagram_widget_text_domain' );



/* Inform existing users that the plugin changed its name and was formerly known as Instagram Widget */

function meks_instagram_widget_name_suffix( $plugins ){

	if( isset($plugins['meks-easy-instagram-widget/meks-easy-instagram-widget.php'])){
		$plugins['meks-easy-instagram-widget/meks-easy-instagram-widget.php']['Name'] .= ' (formerly Meks Easy Instagram Widget)';
	}

	return $plugins;
}

add_filter( 'all_plugins', 'meks_instagram_widget_name_suffix' );