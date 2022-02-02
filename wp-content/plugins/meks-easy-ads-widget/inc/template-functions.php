<?php

add_shortcode( 'meks_easy_ads_blocker_message', 'meks_shortcode_easy_ads' );

function meks_shortcode_easy_ads( $atts ) {

	$atts = shortcode_atts(
		array(
			'title'   => __( 'AdBlocker Message', 'meks-easy-ads-widget' ),
			'message' => __( 'Our website is made possible by displaying online advertisements to our visitors. Please consider supporting us by disabling your ad blocker.', 'meks-easy-ads-widget' ),
		),
		$atts
	);

	$args = array(
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	);

	ob_start();

	the_widget( 'MKS_AdsBlocker_Widget', $atts, $args );

	$output = ob_get_contents();
	ob_end_clean();

	return $output;

}
