<?php

add_shortcode( 'meks_easy_photo_feed', 'meks_shortcode_easy_photo_feed' );

function meks_shortcode_easy_photo_feed( $atts ) {

	$atts = shortcode_atts(
		array(
			'title'          => '',
			'username'       => '',
			'photos_number'  => 12,
			'columns'        => 3,
			'photo_space'    => 2, 
			'container_size' => 300,
			'link_text'      => '',
		),
		$atts
	);

	ob_start();

	the_widget(
		'Meks_Instagram_Widget',
		array(
			'title'            => $atts['title'],
			'username_hashtag' => $atts['username'],
			'photos_number'    => $atts['photos_number'],
			'columns'          => $atts['columns'],
			'photo_space'      => $atts['photo_space'],
			'container_size'   => $atts['container_size'],
			'link_text'        => $atts['link_text'],
		)
	);

	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}
