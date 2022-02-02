<?php

/**
 * Register widgets
 *
 * Callback function which includes widget classes and initialize theme specific widgets
 *
 * @since  1.0
 */

add_action( 'widgets_init', 'typology_register_widgets' );


function typology_register_widgets() {
		
		include_once TYPOLOGY_BUDDY_DIR . 'inc/widgets/posts.php';
		include_once TYPOLOGY_BUDDY_DIR . 'inc/widgets/categories.php';
		
		register_widget( 'Typology_Posts_Widget' );
		register_widget( 'Typology_Category_Widget' );
}


?>