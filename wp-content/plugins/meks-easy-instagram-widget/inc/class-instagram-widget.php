<?php

class Meks_Instagram_Widget extends WP_Widget {

	/**
	 * Unique identifier for your widget.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $widget_slug = 'meks_instagram';

	/**
	 * Unique identifier for localization.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $widget_text_domain = 'meks-easy-instagram-widget';

	/**
	 * Default value holder
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	protected $defaults;

	/**
	 * Access Token
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private $access_token;

	/**
	 * User ID 
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	private $user_id;

	/**
	 * Instagram API type
	 *
	 * @since    1.2.0
	 * @var      string
	 */
	private $api_type;

	/**
	 * Photo limit var 
	 *
	 * @since    1.2.0
	 * @var      int
	 */
	private $photo_limit;

	/**
	 * Username
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	private $username;

	/**
	 * Business username
	 *
	 * @since    1.0.0
	 * @var      array
	 */
	private $business_username;

	/**
	 * Specifies the class name and description, instantiates the widget and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		parent::__construct(
			$this->widget_slug,
			__( 'Meks Easy Instagram Widget', 'meks-easy-instagram-widget' ),
			array(
				'description' => __( 'Easily display Instagram photos with this widget.', 'meks-easy-instagram-widget' ),
			)
		);

		$this->defaults = array(
			'title'            => 'Instagram',
			'username_hashtag' => '',
			'photos_number'    => 9,
			'columns'          => 3,
			'photo_space'      => 1,
			'container_size'   => 300,
			'transient_time'   => DAY_IN_SECONDS,
			'link_text'        => __( 'Follow', 'meks-easy-instagram-widget' ),
		);

		$widget_settings    = get_option( 'meks_instagram_settings' );

		$this->access_token 	 = isset($widget_settings['access_token']) ? $widget_settings['access_token'] : '';
		$this->user_id      	 = isset($widget_settings['user_id']) ? $widget_settings['user_id'] : '';
		$this->business_username = isset($widget_settings['username']) ? $widget_settings['username'] : '';
		$this->api_type     	 = isset($widget_settings['api_type']) ? $widget_settings['api_type'] : 'personal';
		
		// Allow themes or plugins to modify default parameters
		$this->defaults = apply_filters( 'meks_instagram_widget_modify_defaults', $this->defaults );

		// refresh access_token
		$this->refresh_access_token( $widget_settings  ); 

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

		// Register admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_script' ) );

	}

	/**
	 * Check is user authenticated
	 *
	 * @return bool
	 */
	public function is_authorized(){

		return !empty($this->access_token);
	}


	/**
	 * Outputs the content of the widget.
	 *
	 * @param array   args  The array of form elements
	 * @param array   instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		$widget_settings    = get_option( 'meks_instagram_settings' );
		$this->access_token = isset($widget_settings['access_token']) ? $widget_settings['access_token'] : '';
		$this->user_id = isset($widget_settings['user_id']) ? $widget_settings['user_id'] : '';

		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$this->photo_limit = $instance['photos_number'];

		$photos = $this->get_photos( $instance['username_hashtag'], $instance['transient_time'] );

		if ( is_wp_error( $photos ) ) {
			echo $photos->get_error_message();
			echo $after_widget;
			return;
		}

		$photos = $this->limit_images_number( $photos, $this->photo_limit );
		$size   = $this->calculate_image_size( $instance['container_size'], $instance['photo_space'], $instance['columns'] );

		$follow_link = $this->get_follow_link( $instance['username_hashtag'] );

		ob_start();
		include $this->get_template( MEKS_INSTAGRAM_WIDGET_DIR . 'views/widget_html' );
		$widget_content = ob_get_clean();

		echo $widget_content;
		echo $after_widget;

	}


	/**
	 * Processes the widget options to be saved.
	 *
	 * @param array   new_instance The new instance of values to be generated via the update.
	 * @param array   old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                     = array();
		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['username_hashtag'] = strip_tags( $new_instance['username_hashtag'] );
		$instance['photos_number']    = absint( $new_instance['photos_number'] );
		$instance['columns']          = absint( $new_instance['columns'] );
		$instance['photo_space']      = absint( $new_instance['photo_space'] );
		$instance['container_size']   = absint( $new_instance['container_size'] );
		$instance['transient_time']   = absint( $new_instance['transient_time'] );
		$instance['link_text']        = strip_tags( $new_instance['link_text'] );

		return $instance;

	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array   instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults );

		include MEKS_INSTAGRAM_WIDGET_DIR . 'views/widget_admin_html.php';

	}

	/**
	 * Get predefined template which can be overridden by child themes
	 *
	 * @since 1.0.0
	 *
	 * @param string $template
	 * @return string      - File Path
	 */
	private function get_template( $template ) {
		$template_slug = rtrim( $template, '.php' );
		$template      = $template_slug . '.php';

		if ( $theme_file = locate_template( array( '/core/widgets/' . $template ) ) ) :
			$file = $theme_file;
		else :
			$file = $template;
		endif;

		return $file;
	}

	/**
	 * Get photos form Instagram base on username or hashtag with transient caching for one day
	 *
	 * @since 1.0.0
	 *
	 * @param string $username_or_hashtag Searched username or hashtag
	 * @param int    $transient_time Time in seconds
	 * @return array  List of all photos sizes with additional information
	 */
	protected function get_photos( $usernames_or_hashtags, $transient_time ) {

		$key_name = $usernames_or_hashtags; 
		if ( empty( $usernames_or_hashtags ) ) {
			$key_name = $this->user_id;
		}
		$transient_key = $this->generate_transient_key( $key_name );

		$cached = get_transient( $transient_key );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		$usernames_or_hashtags = explode( ',', $usernames_or_hashtags );

		if ( $this->is_authorized() && count( $usernames_or_hashtags ) > 1 ) {
			$usernames_or_hashtags = str_replace( '@', '', current( $usernames_or_hashtags ) );
			$usernames_or_hashtags = array( $usernames_or_hashtags );

		}

		$images = array();

		if ( !empty( $usernames_or_hashtags ) && ( $this->api_type == 'business' || !$this->is_authorized() ) ) {

			foreach ( $usernames_or_hashtags as  $username_or_hashtag ) {
	
				$this->username = trim( $username_or_hashtag );
	
				$data = $this->get_instagram_data();
	
				if ( is_wp_error( $data ) ) {
					return $data;
				}
	
				$images[] = $data;
			}

		} else {

			$this->username = !empty( $usernames_or_hashtags ) ? $usernames_or_hashtags = str_replace( '@', '', current( $usernames_or_hashtags ) ) : '';

			$data = $this->get_instagram_data();
	
			if ( is_wp_error( $data ) ) {
				return $data;
			}
	
			$images[] = $data;
		}
		
		
		$images = array_reduce( $images, 'array_merge', array() );

		usort(
			$images,
			function ( $a, $b ) {
				if ( $a['time'] == $b['time'] ) {
					return 0;
				}
				return ( $a['time'] < $b['time'] ) ? 1 : -1;
			}
		);

		if ( !$this->is_authorized() && $transient_time < ( 12 * HOUR_IN_SECONDS ) ) {
			$transient_time = DAY_IN_SECONDS;
		}

		set_transient( $transient_key, $images, $transient_time );

		return $images;
	}


	/**
	 * Generates transient key to cache the results
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function generate_transient_key( $usernames_or_hashtags ) {

		$transient_key = md5( 'meks_instagram_widget_' . $usernames_or_hashtags );

		return $transient_key;

	}


	/**
	 * Function to return endpoint URL or simple URL for follow link
	 *
	 * @since 1.0.0
	 *
	 * @param string $searched_term
	 * @param string $proxy
	 * @return string    - URL
	 */
	protected function get_instagram_url() {

		$searched_term = trim( strtolower( $this->username ) );

		switch ( substr( $searched_term, 0, 1 ) ) {
			case '#':
				//$url = 'https://instagram.com/explore/tags/' . str_replace( '#', '', $searched_term );
				$url = 'https://instagram.com/' . str_replace( '#', '', $searched_term );
				break;

			default:
				$url = 'https://instagram.com/' . str_replace( '@', '', $searched_term );
				break;
		}

		return $url;
	}


	/**
	 * Make remote request base on Instagram endpoints, get JSON and collect all images.
	 *
	 * @since 1.0.0
	 *
	 * @return array  - List of collected images
	 */
	protected function get_instagram_data() {

		if ( !$this->is_authorized() ) {
			return $this->get_instagram_data_without_token();
		}

		return $this->get_instagram_data_with_token();

	}

	/**
	 * Make request with token.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of collected images
	 */
	protected function get_instagram_data_with_token() {

		if ( 'business' == $this->api_type ) {
			
			$this->username = str_replace( '@', '', $this->username );
			$this->username = str_replace( '#', '', $this->username );
	
			if ( empty($this->username) || $this->username == $this->business_username ) {
				$response = wp_remote_get( 'https://graph.facebook.com/v7.0/' . $this->user_id . '/media?fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children{media_url,id,media_type,timestamp,permalink,thumbnail_url}&limit='. $this->photo_limit .'&access_token=' . $this->access_token );
			} else {
				$response = wp_remote_get( 'https://graph.facebook.com/v7.0/' . $this->user_id . '?fields=business_discovery.username('. $this->username .'){followers_count,media_count,media{media_url,media_type,caption,permalink,timestamp,comments_count,like_count}}&limit='. $this->photo_limit .'&access_token=' . $this->access_token );
			}

		} else {
			$response = wp_remote_get( 'https://graph.instagram.com/'. $this->user_id .'/media?fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children{media_url,id,media_type,timestamp,permalink,thumbnail_url}&limit='. $this->photo_limit .'&access_token='.$this->access_token );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			$error_message = __('Connection error. Connection fail between instagram and your server. Please try again', 'meks-easy-instagram-widget');
			if ( isset($data->error->message) ) {
				$error_message = str_replace( '(#100)', 'Error message: ', $data->error->message );
			}
			return new WP_Error( 'invalid-token', esc_html( $error_message ) );
		}

		//$data = json_decode( wp_remote_retrieve_body( $response ) );
		//print_r($data);

		$images = $this->parse_instagram_images_with_token( $data );

		if ( empty( $images ) ) {
			return new WP_Error( 'no_images', esc_html__( 'Images not found. This may be a temporary problem. Please try again soon.', 'meks-easy-instagram-widget' ) );
		}

		return $images;

	}


	/**
	 * Make request without token
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_instagram_data_without_token() {

		$url = $this->get_instagram_url();

		$request = wp_remote_get( $url );
		$body    = wp_remote_retrieve_body( $request );

		$shared = explode( 'window._sharedData = ', $body );
		$json   = explode( ';</script>', $shared[1] );
		$data   = json_decode( $json[0], true );


		if ( empty( $data ) ) {
			return new WP_Error( 'blocked',  sprintf( esc_html__('Instagram has returned empty data. Please authorize your Instagram account in the %s plugin settings %s.', 'meks-easy-instagram-widget' ), '<a href="'.esc_url( admin_url( 'options-general.php?page=meks-instagram' ) ).'">', '</a>') );
		}

		if ( isset( $data['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
			$images = $data['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
		} elseif ( isset( $data['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'] ) ) {
			$images = $data['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
		} else {
			return new WP_Error( 'blocked',  sprintf( esc_html__('Instagram has returned empty data. Please authorize your Instagram account in the %s plugin settings %s.', 'meks-easy-instagram-widget' ), '<a href="'.esc_url( admin_url( 'options-general.php?page=meks-instagram' ) ).'">', '</a>') );
		}

		
		$images = $this->parse_instagram_images_without_token( $images );
		
		if ( empty( $images ) ) {
			return new WP_Error( 'no_images', esc_html__( 'Images not found. This may be a temporary problem. Please try again soon.', 'meks-easy-instagram-widget' ) );
		}

		return $images;

	}


	/**
	 * Parse instagram images
	 *
	 * @since  1.0.1
	 *
	 * @param array $data
	 * @return array  - List of images prepared for displaying
	 */
	protected function parse_instagram_images_with_token( $data ) {

		$pretty_images = array();

		if ( 'business' == $this->api_type && ( !empty( $this->username ) && $this->username != $this->business_username ) ) {
			$images_data = $data->business_discovery->media->data;
		} else {
			$images_data = $data->data;
		}

		foreach ( $images_data as $image ) {

			if ( 'business' == $this->api_type && ( !empty( $this->username ) && $this->username != $this->business_username ) && $image->media_type == 'VIDEO' ) {
				continue;
			}

			$pretty_images[] = array(
				'caption'   => isset( $image->caption ) ? $image->caption : '',
				'link'      => trailingslashit( $image->permalink ),
				'time'      => $image->timestamp,
				'comments'  => isset( $image->comments_count ) ? $image->comments_count : '',
				'likes'     => isset( $image->like_count ) ? $image->like_count : '',
				'thumbnail' => $image->permalink.'media?size=t',
				'small'     => $image->permalink.'media?size=t',
				'medium'    => $image->permalink.'media?size=m',
				'large'     => $image->permalink.'media?size=l',
				'original'  => $image->media_type == 'VIDEO' ? $image->thumbnail_url : $image->media_url,
			);

		}

		return $pretty_images;
	}


	/**
	 * Parse instagram images
	 *
	 * @since  1.0.1
	 *
	 * @param array $images - Raw Images
	 * @return array           - List of images prepared for displaying
	 */
	protected function parse_instagram_images_without_token( $images ) {

		$pretty_images = array();

		foreach ( $images as $image ) {

			$pretty_images[] = array(
				'caption'   => isset( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ? $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] : '',
				'link'      => trailingslashit( 'https://instagram.com/p/' . $image['node']['shortcode'] ),
				'time'      => $image['node']['taken_at_timestamp'],
				'comments'  => $image['node']['edge_media_to_comment']['count'],
				'likes'     => $image['node']['edge_liked_by']['count'],
				'thumbnail' => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ), // 150
				'small'     => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][1]['src'] ), // 240
				'medium'    => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ), // 320
				'large'     => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][3]['src'] ), // 480
				'original'  => preg_replace( '/^https?\:/i', '', $image['node']['display_url'] ),
			);

		}

		return $pretty_images;
	}


	/**
	 * Limit number of displayed images on front-end
	 *
	 * @since  1.0.0
	 *
	 * @param array  $photos - Lists of images
	 * @param number $limit  - Max number of image that we want to show
	 * @return array             - Limited List
	 */
	protected function limit_images_number( $photos, $limit = 1 ) {
		if ( empty( $photos ) || is_wp_error( $photos ) ) {
			return array();
		}
		return array_slice( $photos, 0, $limit );
	}

	/**
	 * Calculate which images size  to use base on container width and photo space and calculate images columns
	 *
	 * @since  1.0.0
	 *
	 * @param int $container_size
	 * @param int $photo_space
	 * @param int $columns
	 * @return array              - Proper image size and flex column calculation
	 */
	public function calculate_image_size( $container_size, $photo_space, $columns ) {

		$width = ( $container_size - ( $photo_space * ( $columns - 1 ) ) ) / $columns;
		$flex  = 100 / $columns;

		$size         = array();
		$size['flex'] = $flex;

		// if ( $this->is_authorized() ) {
		// 	$size['thumbnail'] = 'original';
		// 	return $size;
		// }

		switch ( $width ) {

			case $width <= 150:
				$size['thumbnail'] = 'original';
				break;

			case $width <= 240:
				$size['thumbnail'] = 'original';
				break;

			case $width <= 320:
				$size['thumbnail'] = 'original';
				break;

			case $width <= 480:
				$size['thumbnail'] = 'original';
				break;

			default:
				$size['thumbnail'] = 'original';
				break;
		}

		return $size;

	}

	/**
	 * Check if is one username or hashtag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $usernames_or_hashtags String from username or hashtag input field
	 * @return string    Follow URL or empty string
	 */
	protected function get_follow_link( $usernames_or_hashtags ) {

		$usernames_hashtags_array   = explode( ',', $usernames_or_hashtags );
		$usernames_hashtags   = str_replace( '@', '', current( $usernames_hashtags_array ) );
		$this->username = $usernames_hashtags;
		
		return $this->get_instagram_url();
	}


	/**
	 * Registers and enqueue widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_enqueue_style( $this->widget_slug . '-widget-styles', MEKS_INSTAGRAM_WIDGET_URL . 'css/widget.css' );
	}

	/**
	 * Registers and enqueue admin specific scripts.
	 */
	public function register_admin_script() {
		wp_enqueue_script( $this->widget_slug . '-admin-script', MEKS_INSTAGRAM_WIDGET_URL . 'js/admin.js', true, MEKS_INSTAGRAM_WIDGET_VER );
		wp_enqueue_style( $this->widget_slug . '-admin-styles', MEKS_INSTAGRAM_WIDGET_URL . 'css/admin.css' );
	}

	public function refresh_access_token( $options ) {

		if ( empty( $options ) ) {
			$options = get_option( 'meks_instagram_settings' );
		}

		if ( empty( $options['access_token_expires_in'] ) || empty( $options['access_token'] ) ) {
			return false;
		}

		$refresh_time_past = $options['access_token_expires_in'] - ( 30 * 86400 ); // if past 30 days (60 - 30) 

		if ( $refresh_time_past > time() ) { 
			return false;
		}

		if ( 'business' == $this->api_type ) {
			$refresh_token_response = wp_remote_get( 'https://graph.facebook.com/v7.0/oauth/access_token?grant_type=fb_exchange_token&client_id=591315618393932&client_secret=14ae546b96c0684cc67a8d4b10eb3a7b&fb_exchange_token='. $options['access_token'] );
		} else {
			$refresh_token_response = wp_remote_get( 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token='. $options['access_token'] );

		}

		$refresh_data = json_decode( wp_remote_retrieve_body( $refresh_token_response ) );

		if ( is_wp_error( $refresh_token_response ) || isset( $refresh_data->error ) ) {
			$error_message = __('Some problem with connection and with refresh token', 'meks-easy-instagram-widget');
			if ( isset($refresh_data->error->message) ) {
				$error_message = $refresh_data->error->message;
			}
			return new WP_Error( 'refresh-invalid-token', esc_html( $error_message ) );
		} 

		$options['access_token'] = $refresh_data->access_token;
		
		if ( isset( $refresh_data->expires_in) ){
			$options['access_token_expires_in'] = $refresh_data->expires_in + time();
		} else {
			$options['access_token_expires_in'] = ( 60 * 86400 ) + time();
		}

		update_option( 'meks_instagram_settings', $options );		
		
	}

}
