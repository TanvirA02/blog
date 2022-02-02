<?php
/*
Plugin Name: Meks Easy Social Share
Description: Easily display social share buttons for your posts, pages and custom post types. Supports Facebook, Twitter, Reddit, Pinterest, Email, Google+, LinkedIn, StumbleUpon, WhatsApp and vKontakte. AMP supported.
Version: 1.2.6
Author: Meks
Author URI: https://mekshq.com/
Text Domain: meks-easy-social-share
Domain Path: /languages
*/

/* Prevent direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

define( 'MEKS_ESS_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MEKS_ESS_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MEKS_ESS_VER', '1.2.6' );
define( 'MEKS_ESS_BASENAME', plugin_basename( __FILE__ ) );

/* Includes */
require_once MEKS_ESS_DIR . 'inc/functions.php';
require_once MEKS_ESS_DIR . 'inc/class-share.php';


/* Start plugin */
add_action( 'init', 'meks_ess_init' );

function meks_ess_init() {
	$meks_ess = Meks_ESS::get_instance();
}
