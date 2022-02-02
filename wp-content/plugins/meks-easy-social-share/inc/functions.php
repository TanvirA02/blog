<?php

/**
 * Get social share
 *
 * Main function to get or display social share buttons
 *
 * @return array HTML of share links
 * @since  1.0
 */

if ( !function_exists( 'meks_ess_share' ) ):
	function meks_ess_share( $options = array(), $echo = true, $before = '', $after = '' ) {

		if ( empty( $options ) && empty( $before ) && empty( $after ) ) {

			$meks_ess = Meks_ESS::get_instance();

			$args = $meks_ess->parse_settings_for_output();

			$options = $args['platforms'];
			$before = $args['before'];
			$after = $args['after'];

		}

		if ( empty( $options ) ) {
			return false;
		}

		$title = meks_ess_esc_text( wp_strip_all_tags( get_the_title() ) );
		$url = rawurlencode( esc_url( esc_attr( get_the_permalink() ) ) );

		$share = array();

		$share['facebook'] = '<a href="#" onclick="return false;" class="meks_ess-item socicon-facebook" data-url="http://www.facebook.com/sharer/sharer.php?u=' . $url . '&amp;t=' . $title . '"><span>'.__( 'Facebook', 'meks-easy-social-share' ).'</span></a>';
		$share['twitter'] = '<a href="#" onclick="return false;" class="meks_ess-item socicon-twitter" data-url="http://twitter.com/intent/tweet?url=' . $url . '&amp;text=' . $title . '"><span>'.__( 'Twitter', 'meks-easy-social-share' ).'</span></a>';
		$share['googleplus'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-googleplus" data-url="https://plus.google.com/share?url=' . $url . '"><span>'.__( 'Google+', 'meks-easy-social-share' ).'</span></a>';
		$pin_img = has_post_thumbnail() ? wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) : '';
		$pin_img = isset( $pin_img[0] ) ? $pin_img[0] : '';
		$share['pinterest'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-pinterest" data-url="http://pinterest.com/pin/create/button/?url=' . $url . '&amp;media=' . urlencode( esc_attr( $pin_img ) ) . '&amp;description=' . $title . '"><span>'.__( 'Pinterest', 'meks-easy-social-share' ).'</span></a>';
		$share['linkedin'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-linkedin" data-url="https://www.linkedin.com/cws/share?url=' . $url . '"><span>'.__( 'LinkedIn', 'meks-easy-social-share' ).'</span></a>';
		$share['reddit'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-reddit" data-url="http://www.reddit.com/submit?url=' . $url . '&amp;title=' . $title . '"><span>'.__( 'Reddit', 'meks-easy-social-share' ).'</span></a>';
		$share['email'] = '<a href="mailto:?subject=' . $title . '&amp;body=' . $url . '" class="meks_ess-item  socicon-mail prevent-share-popup "><span>'.__( 'Email', 'meks-easy-social-share' ).'</span></a>';
		$share['stumbleupon'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-stumbleupon" data-url="http://www.stumbleupon.com/badge?url=' . $url . '&amp;title=' . $title . '"><span>'.__( 'StumbleUpon', 'meks-easy-social-share' ).'</span></a>';
		$share['vk'] = '<a href="#" onclick="return false;"  class="meks_ess-item socicon-vkontakte" data-url="http://vk.com/share.php?url='.$url.'&amp;title='.$title.'"><span>'.__( 'vKontakte', 'meks-easy-social-share' ).'</span></a>';
		$share['whatsapp'] = '<a href="https://api.whatsapp.com/send?text='.$title.' '.$url.'" class="meks_ess-item socicon-whatsapp prevent-share-popup"><span>'.__( 'WhatsApp', 'meks-easy-social-share' ).'</span></a>';

		/* AMP support */
		if ( function_exists('amp_is_request') && amp_is_request() ) {

			$title = wp_strip_all_tags( get_the_title() );
			$url = esc_url( esc_attr( get_the_permalink() ) );

			$share['facebook'] = '<amp-social-share type="oldFacebookShare" class="meks_ess-item socicon-facebook" data-share-endpoint="http://www.facebook.com/sharer/sharer.php?u=' . $url . '&amp;t=' . $title . '" aria-label="Share on Facebook"></amp-social-share>';
			$share['twitter'] = '<amp-social-share type="twitter" class="meks_ess-item socicon-twitter" data-param-text="' . $title . '" data-param-url="' . $url . '" aria-label="Share on Twitter"></amp-social-share>';
			$share['googleplus'] = '<amp-social-share type="googleplus" class="meks_ess-item socicon-googleplus" data-param-text="' . $title . '" data-share-endpoint="https://plus.google.com/share?url=' . $url . '" aria-label="Share on Google+"></amp-social-share>';
			$share['pinterest'] = '<amp-social-share type="customPin" class="meks_ess-item socicon-pinterest" data-share-endpoint="http://pinterest.com/pin/create/button/?url='. $url .'&amp;media='. $pin_img .'&amp;description='. $title .'" data-param-media="'. $pin_img .'" aria-label="Share on Pinterest"></amp-social-share>';
			$share['linkedin'] = '<amp-social-share type="linkedin" class="meks_ess-item socicon-linkedin" data-param-text="' . $title . '" data-param-url="' . $url . '" aria-label="Share on LinkedIn"></amp-social-share>';
			$share['reddit'] = '<amp-social-share type="reddit" class="meks_ess-item socicon-reddit" data-share-endpoint="http://www.reddit.com/submit?url=' . $url . '" data-param-text="' . $title . '" aria-label="Share on Reddit"></amp-social-share>';
			$share['email'] = '<amp-social-share type="email" class="meks_ess-item socicon-mail prevent-share-popup" data-param-subject="' . $title . '" data-param-body="' . $url . '" aria-label="Share on Email"></amp-social-share>';
			$share['stumbleupon'] = '<amp-social-share type="stumbleupon" class="meks_ess-item socicon-stumbleupon" data-share-endpoint="https://www.stumbleupon.com/badge?url=' . $url . '" data-param-text="' . $title . '" aria-label="Share on StumbleUpon"></amp-social-share>';
			$share['vk'] = '<amp-social-share type="vkontakte" class="meks_ess-item socicon-vkontakte" data-share-endpoint="http://vk.com/share.php?url='.$url.'&amp;title='.$title.'" aria-label="Share on vKontakte"></amp-social-share>';
			$share['whatsapp'] = '<amp-social-share type="whatsapp" class="meks_ess-item socicon-whatsapp" data-param-text="'.$title.' '.$url.'" aria-label="Share on WhatsApp"></amp-social-share>';

		}


		$output = '';

		foreach ( $options as $social ) {
			if ( array_key_exists( $social, $share ) ) {
				$output.= $share[$social];
			}
		}

		$output = apply_filters( 'meks_ess_modify_share_output', $output );
		$before = apply_filters( 'meks_ess_modify_before', $before );
		$after = apply_filters( 'meks_ess_modify_after', $after );


		if ( empty( $output ) ) {
			return '';
		}

		if ( $echo ) {
			echo $before . $output . $after;

		} else {
			return $before . $output . $after;
		}

	}
endif;


/**
 * Trim text characters with UTF-8
 * for adding to html attributes it's not breaking the code and
 * you are able to have all the kind of characters (Japanese, Cyrillic, German, French, etc.)
 *
 * @param string  $text
 * @since  1.0
 */
if ( !function_exists( 'meks_ess_esc_text' ) ):
	function meks_ess_esc_text( $text ) {
		return rawurlencode( html_entity_decode( wp_kses( $text, null ), ENT_COMPAT, 'UTF-8' ) );
	}
endif;


/**
 * Get all post types
 *
 * Function to get all post types
 *
 * @return array of post types
 * @since  1.1
 */

if ( !function_exists( 'meks_ess_post_types' ) ):
	function meks_ess_post_types() {

		$args = array(
			'public' => true
		);

		$post_types =  get_post_types( $args, 'objects' );

		if ( !empty( $post_types ) ) {

			$exclude = array( 'attachment', 'topic', 'forum', 'guest-author', 'reply' );

			foreach ( $post_types as $key => $post_type ) {
				if ( in_array( $key, $exclude ) ) {
					unset( $post_types[$key] );
				}
			}

		}

		$post_types =  apply_filters( 'meks_ess_modify_post_types_list', $post_types );

		return $post_types;
	}
endif;


/**
 * Parse args ( merge arrays )
 *
 * Similar to wp_parse_args() but extended to also merge multidimensional arrays
 *
 * @param array   $a - set of values to merge
 * @param array   $b - set of default values
 * @return array Merged set of elements
 * @since  1.0.0
 */

if ( !function_exists( 'meks_ess_parse_args' ) ):
	function meks_ess_parse_args( &$a, $b ) {

		$a = (array)$a;
		$b = (array)$b;
		$r = $b;
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && !isset( $v[0] ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = meks_ess_parse_args( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}
endif;

/**
 * Prepare share platforms for theme patching
 *
 * @return array Of Social Platforms
 * @since  1.0.0
 */

if ( !function_exists( 'meks_ess_get_platforms' ) ):
	function meks_ess_get_platforms() {

		return Meks_ESS::get_platforms();
	}
endif;


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
							echo sprintf( __( 'You are currently using %1$s theme. Did you know that Meks plugins give you more features and flexibility with one of our <a href="%2$s">Meks themes</a>?', 'meks-easy-social-share' ), $all_themes[ $active_theme ], 'https://1.envato.market/4DE2o' );
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
