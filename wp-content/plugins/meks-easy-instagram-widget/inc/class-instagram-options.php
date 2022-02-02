<?php

class Meks_Instagram_Options {

	/**
	 *  Hold the class instance.
	 */
	private static $instance = null;

	/**
	 * Holds the default values
	 */
	private $defaults = array( 
		'access_token' => '', 
		'user_id' => '',
		'access_token_expires_in' => '', 
		'api_type' => 'personal', 
		'username' => '',
		'name' => '',
		'picture_url' => '',
	);

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Settings key in database, used in get_option() as first parameter
	 *
	 * @var string
	 */
	private $settings_key = 'meks_instagram_settings';

	/**
	 * Slug of the page, also used as identifier for hooks
	 *
	 * @var string
	 */
	private $slug = 'meks-instagram';

	/**
	 * Options group id, will be used as identifier for adding fields to options page
	 *
	 * @var string
	 */
	private $options_group_id = 'meks-instagram-group-id';

	/**
	 * Fields var
	 *
	 * @var string
	 */
	private $fields;


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}


	/**
	 * Get Class Instance
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new Meks_Instagram_Options();
		}
		return self::$instance;
	}


	/**
	 * Initialize method
	 */
	public function init() {

		$database_options = get_option( $this->settings_key );
		$this->options = wp_parse_args( $database_options, $this->defaults );

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

	}

	/* Add the plugin settings link */
	function plugin_settings_link( $actions, $file ) {

		if ( $file != MEKS_INSTAGRAM_BASENAME ) {
            return $actions;
        }

		$actions['meks_instagram_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->slug ) ) . '" aria-label="settings"> ' . __( 'Settings', 'meks-easy-instagram-widget' ) . '</a>';

		return $actions;
    }
    
     /**
     * Enqueue Admin Scripts
     */
    public function enqueue_admin_scripts() {
        global $pagenow;

        if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == $this->slug ) {
            wp_enqueue_style( 'meks_instagram_settings_style', MEKS_INSTAGRAM_WIDGET_URL . 'css/admin-settings.css', MEKS_INSTAGRAM_WIDGET_VER, true );
			wp_enqueue_script( 'meks_instagram_settings', MEKS_INSTAGRAM_WIDGET_URL . 'js/settings.js', array( 'jquery' ), MEKS_INSTAGRAM_WIDGET_VER, true );
			
			//Load JS settings object
			wp_localize_script( 'meks_instagram_settings', 'meks_js_settings', meks_get_js_settings() );
		}
		
    }

	/* Get fields data */
	function get_fields() {

		$fields = array(

			'access_token' => array(
				'id'       => 'access_token',
				'title'    => esc_html__( 'Access Token', 'meks-easy-instagram-widget' ),
				'sanitize' => 'text',
				'default'  => '',
			),

			'user_id' => array(
				'id'       => 'user_id',
				'title'    => esc_html__( 'User ID', 'meks-easy-instagram-widget' ),
				'sanitize' => 'text',
				'default'  => '',
			),

			'access_token_expires_in' => array(
				'id'       => 'access_token_expires_in',
				'title'    => '',
				'sanitize' => 'text',
				'default'  => '',
			),

			'api_type' => array(
				'id'       => 'api_type',
				'title'    => '',
				'sanitize' => 'text',
				'default'  => '',
			),

		);

		$fields = apply_filters( 'meks_instagram_modify_options_fields', $fields );

		return $fields;

	}


	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			esc_html__( 'Meks Instagram Settings', 'meks-easy-instagram-widget' ),
			esc_html__( 'Meks Instagram Settings', 'meks-easy-instagram-widget' ),
			'manage_options',
			$this->slug,
			array( $this, 'print_settings_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function print_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( $this->options_group_id );
					do_settings_sections( $this->slug );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {

		register_setting(
			$this->options_group_id, // Option group
			$this->settings_key, // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		$this->fields = $this->get_fields();

		if ( empty( $this->fields ) ) {
			return false;
		}

		$section_id = 'meks_instagram_section';

		add_settings_section( $section_id, '', '', $this->slug );

		foreach ( $this->fields as $field ) {

			if ( empty( $field['id'] ) ) {
				continue;
			}

			$action   = 'print_' . $field['id'] . '_field';
			$callback = method_exists( $this, $action ) ? array( $this, $action ) : $field['action'];

			add_settings_field(
				'meks_instagram_' . $field['id'] . '_id',
				$field['title'],
				$callback,
				$this->slug,
				$section_id,
				$this->options[ $field['id'] ]
			);
		}

	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param unknown $input array $input Contains all settings fields as array keys
	 * @return mixed
	 */
	public function sanitize( $input ) {

		if ( empty( $this->fields ) || empty( $input ) ) {
			return false;
		}

		$new_input = array();
		foreach ( $this->fields as $field ) {
			if ( isset( $input[ $field['id'] ] ) ) {
				$new_input[ $field['id'] ] = $this->sanitize_field( $input[ $field['id'] ], $field['sanitize'] );
			}
		}

		return $new_input;
	}

	/**
	 * Dynamically sanitize field values
	 *
	 * @param unknown $value
	 * @param unknown $sensitization_type
	 * @return int|string
	 */
	private function sanitize_field( $value, $sensitization_type ) {
		switch ( $sensitization_type ) {

			case 'checkbox':
				$new_input = array();
				foreach ( $value as $key => $val ) {
					$new_input[ $key ] = ( isset( $value[ $key ] ) ) ?
					sanitize_text_field( $val ) :
					'';
				}
				return $new_input;
			break;

			case 'radio':
				return sanitize_text_field( $value );
			break;

			case 'text':
				return sanitize_text_field( $value );
			break;

			default:
				return $value;
				break;
		}
	}


	/**
	 * Print button field
	 */
	public function instagram_auth_button() {

		$oauth_personal_url = 'https://api.instagram.com/oauth/authorize?app_id=515358899130297&redirect_uri=https://mekshq.com/instagram-personal-api/&response_type=code&scope=user_profile,user_media&state='. esc_url( admin_url( 'options-general.php?page=' . $this->slug ) );
		$oauth_business_url = 'https://www.facebook.com/v7.0/dialog/oauth?client_id=591315618393932&redirect_uri=https://mekshq.com/instagram-business-api/&response_type=code&scope=pages_show_list,instagram_basic,instagram_manage_insights&state='. esc_url( admin_url( 'options-general.php?page=' . $this->slug ) );

		?>

		<a 	class="meks-instagram-button-connect button button-secondary" 
			data-personal-api="<?php echo esc_url( $oauth_personal_url ); ?>" 	
			data-business-api="<?php echo esc_url( $oauth_business_url ); ?>" 
			href="<?php echo esc_url( $oauth_personal_url ); ?>">
			<span><?php echo esc_html( 'Authorize with Instagram', 'meks-easy-instagram-widget' ); ?></span>
		</a>
		<?php

	}

	/**
	 * Print access token field
	 */
	public function print_access_token_field( $args ) {

		printf(
			'<label class="meks-instagram-access-token"><input type="text" id="meks-access-token" name="%s[access_token]" value="%s"/></label>',
			$this->settings_key,
			esc_attr($args)
		);
		$this->instagram_auth_button();

	}

	/**
	 * Print user ID field
	 */
	public function print_user_id_field( $args ) {

		printf(
			'<label class="meks-instagram-user-id"><input type="text" id="meks-user-id" name="%s[user_id]" value="%s"/></label>',
			$this->settings_key,
			esc_attr($args)
		);

	}

	/**
	 * Print api_type field
	 */
	public function print_api_type_field( $args ) {

		printf(
			'<input type="hidden" id="meks-api-type" name="%s[api_type]" value="%s"/>',
			$this->settings_key,
			$args
		);

	}

	/**
	 * Print access_token_expires_in field
	 */
	public function print_access_token_expires_in_field( $args ) {

		printf(
			'<input type="hidden" id="meks-token-expires-in" name="%s[access_token_expires_in]" value="%s"/>',
			$this->settings_key,
			$args
		);

	}

}
