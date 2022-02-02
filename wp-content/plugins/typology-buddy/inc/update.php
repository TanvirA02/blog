<?php

add_filter( 'pre_set_site_transient_update_plugins', 'typology_buddy_update_transient' );

function typology_buddy_update_transient( $transient ) {

	$response = typology_buddy_update_api();

	if ( false !== $response && version_compare( $response->new_version, TYPOLOGY_BUDDY_VER, '>' ) ) {
		$transient->response[TYPOLOGY_BUDDY_BASENAME] = $response;
	}

	return $transient;
}



add_filter( 'plugins_api', 'typology_buddy_update_info', 10, 3 );

function typology_buddy_update_info( $response, $action, $info ) {

	if ( $action != 'plugin_information' ) {
		return false;
	}

	if ( $info->slug != TYPOLOGY_BUDDY_BASENAME ) {
		return false;
	}


	$remote = typology_buddy_update_api();

	if ( !$remote ) {
		return false;
	}

	$response = new stdClass();
	$response->name = $remote->plugin_name;
	$response->slug = $remote->slug;
	$response->version = $remote->new_version;
	$response->tested = $remote->tested;
	$response->requires = $remote->requires;
	$response->author = $remote->author;
	$response->author_profile = $remote->author_profile;
	$response->download_link = $remote->download_link;
	$response->trunk = $remote->download_link;
	$response->last_updated = $remote->last_updated;
	$response->sections = $remote->sections;

	return $response;

}



function typology_buddy_update_api() {

	$transient = TYPOLOGY_BUDDY_BASENAME . '-update-info';

	//delete_transient( $transient );

	$response = get_transient( $transient );

	if ( !empty( $response ) ) {
		return $response;
	}

	$request = wp_remote_post( 'http://mekshq.com/static/plugins/update-api/', array( 'body' => array( 'action' => 'plugin-information', 'slug' => TYPOLOGY_BUDDY_BASENAME ) ) );

	if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
		return false;
	}

	$response = json_decode( wp_remote_retrieve_body( $request ) );

	if ( is_object( $response ) ) {
		set_transient( $transient, $response, DAY_IN_SECONDS );
		return $response;
	} else {
		return false;
	}

}



?>
