<?php

/**
 * Load admin js files
 *
 * @since  1.0
 */

add_action( 'admin_enqueue_scripts', 'typology_buddy_load_admin_js' );

function typology_buddy_load_admin_js() {

	global $pagenow;	

	if( $pagenow == 'widgets.php' ){
		wp_enqueue_script( 'typology-widgets', TYPOLOGY_BUDDY_URL . 'assets/js/admin/widgets.js', array( 'jquery', 'jquery-ui-sortable'), TYPOLOGY_BUDDY_VER );
	}

}

?>