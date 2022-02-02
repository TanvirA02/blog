<?php

add_action( 'admin_init', 'typology_buddy_compatibility' );

function typology_buddy_compatibility() {

	if ( is_admin() && current_user_can( 'activate_plugins' ) && !typology_buddy_is_theme_active() ) {

		add_action( 'admin_notices', 'typology_buddy_compatibility_notice' );

		deactivate_plugins( TYPOLOGY_BUDDY_BASENAME );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

function typology_buddy_compatibility_notice() {
	echo '<div class="notice notice-warning"><p><strong>Note:</strong> Typology Buddy plugin has been deactivated as it requires Typology Theme to be active.</p></div>';
}

function typology_buddy_is_theme_active() {
	return defined( 'TYPOLOGY_THEME_VERSION' );
}

function buddy_sort_option_items( $items, $selected, $field = 'term_id' ) {
	
	if ( empty( $selected ) ) {
		return $items;
	}

	$new_items = array();
	$temp_items = array();
	$temp_items_ids = array();

	foreach ( $selected as $selected_item_id ) {

		foreach ( $items as $item ) {
			if ( $selected_item_id == $item->$field ) {
				$new_items[] = $item;
			} else {
				if ( !in_array( $item->$field, $selected ) && !in_array( $item->$field, $temp_items_ids ) ) {
					$temp_items[] = $item;
					$temp_items_ids[] = $item->$field;
				}
			}
		}

	}

	$new_items = array_merge( $new_items, $temp_items );

	return $new_items;
}