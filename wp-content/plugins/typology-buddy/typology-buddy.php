<?php
/*
Plugin Name: Typology Buddy
Description: A plugin which adds specific features to Typology WordPress theme.
Version: 1.0.2
Author: meks
Author URI: https://mekshq.com/
Text Domain: typology-buddy
Domain Path: /languages
*/

/* Prevent direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

/* Define */
define( 'TYPOLOGY_BUDDY_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'TYPOLOGY_BUDDY_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'TYPOLOGY_BUDDY_BASENAME', plugin_basename( __FILE__ ) );

define( 'TYPOLOGY_BUDDY_VER', '1.0.2' );


/* Compatibility checks */
require_once TYPOLOGY_BUDDY_DIR . 'inc/compat.php';

/* Register widgets */
require_once TYPOLOGY_BUDDY_DIR . 'inc/widgets.php';

if ( is_admin() ) {

	/* Enqueue */
	require_once TYPOLOGY_BUDDY_DIR . 'inc/enqueue.php';

	/* Update API */
	require_once TYPOLOGY_BUDDY_DIR . 'inc/update.php';

}

/* Load text domain */

add_action( 'plugins_loaded', 'typology_buddy_text_domain' );

function typology_buddy_text_domain() {
	load_plugin_textdomain( 'typology-buddy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

?>