<?php

class Meks_ESS {

    /**
     *  Hold the class instance.
     */
    private static $instance = null;

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Settings key in database, used in get_option() as first parameter
     *
     * @var string
     */
    private $settings_key = 'meks_ess_settings';

    /**
     * Slug of the page, also used as identifier for hooks
     *
     * @var string
     */
    private $slug = 'meks-easy-social-share';

    /**
     * Options group id, will be used as identifier for adding fields to options page
     *
     * @var string
     */
    private $options_group_id = 'meks-ess-settings';

    /**
     * Array of all fields that will be printed on the settings page
     *
     * @var array
     */

    private $fields;

    /**
     * Array of styles
     *
     * @var array
     */
    private $styles = array(

        'style' => array(
            '1' => 'rectangle no-labels',
            '2' => 'rounded no-labels',
            '3' => 'circle no-labels',
            '4' => 'square no-labels',
            '5' => 'transparent no-labels',
            '6' => 'rectangle',
            '7' => 'rounded',
            '8' => 'transparent'
        ),

        'variant' => array(
            '1' => 'solid',
            '2' => 'outline'
        )

    );


    /**
     * Start up
     */
    public function __construct() {

        $this->fields = $this->get_fields();
        $this->options = $this->get_options();

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        
        if(!is_admin()){
            add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
        }
        

    }

    public static function get_instance() {
        if ( self::$instance == null ) {
            self::$instance = new Meks_ESS();
        }
        return self::$instance;
    }

    /* Load translation file */
    function load_textdomain() {
        load_plugin_textdomain( 'meks-easy-social-share', false, dirname( MEKS_ESS_BASENAME ) . '/languages' );
    }

    /* Get fields data */
    function get_fields() {

        $fields = array(
            'platforms' => array(
                'id' => 'platforms',
                'title' => esc_html__( 'Platforms', 'meks-easy-social-share' ),
                'sanitize' => 'checkbox',
                'default' => array( 'facebook', 'twitter' )
            ),
            'style' => array(
                'id' => 'style',
                'title' => esc_html__( 'Style', 'meks-easy-social-share' ),
                'sanitize' => 'radio',
                'default' => '1'
            ),
            'variant' => array(
                'id' => 'variant',
                'title' => esc_html__( 'Variant', 'meks-easy-social-share' ),
                'sanitize' => 'radio',
                'default' => '1'
            ),
            'color' => array(
                'id' => 'color',
                'title' => esc_html__( 'Color', 'meks-easy-social-share' ),
                'sanitize' => 'checkbox',
                'default' => array(
                    'type' => 'brand',
                    'custom_color' => '#ffd635'
                )
            ),
            'location' => array(
                'id' => 'location',
                'title' => esc_html__( 'Location', 'meks-easy-social-share' ),
                'sanitize' => 'radio',
                'default' => 'above'
            ),
            'post_type' => array(
                'id' => 'post_type',
                'title' => esc_html__( 'Post Type', 'meks-easy-social-share' ),
                'sanitize' => 'checkbox',
                'default' => array( 'post' )
            ),
            'label_share' => array(
                'id' => 'label_share',
                'title' =>  esc_html__( 'Share label', 'meks-easy-social-share' ),
                'sanitize' => 'checkbox',
                'default' => array(
                    'text' => 'Share this',
                    'active' => 0
                )
            ),

        );

        $fields = apply_filters( 'meks_ess_modify_options_fields', $fields );

        return $fields;

    }

    /* Add the plugin settings link */
    function plugin_settings_link( $actions, $file ) {

        if ( $file != MEKS_ESS_BASENAME ) {
            return $actions;
        }

        $actions['meks_ess_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page='.$this->slug ) ) . '" aria-label="settings"> '. __( 'Settings', 'meks-easy-social-share' ) . '</a>';

        return $actions;
    }


    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            esc_html__( 'Meks Easy Social Share', 'meks-easy-social-share' ),
            esc_html__( 'Meks Easy Social Share', 'meks-easy-social-share' ),
            'manage_options',
            $this->slug,
            array( $this, 'print_settings_page' )
        );
    }

    /**
     * Get options from database
     */
    private function get_options() {

        $defaults = array();

        foreach ( $this->fields as $field => $args ) {
            $defaults[$field] = $args['default'];
        }

        $defaults = apply_filters( 'meks_ess_modify_defaults', $defaults );

        $options = get_option( $this->settings_key );

        $options = meks_ess_parse_args( $options, $defaults );

        $options = apply_filters( 'meks_ess_modify_options', $options );

        //print_r( $options );

        return $options;

    }



    /**
     * Enqueue Admin Scripts
     */
    public function enqueue_admin_scripts() {
        global $pagenow;

        if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == $this->slug ) {
            wp_enqueue_style( 'meks_ess_settings', MEKS_ESS_URL . 'assets/css/admin.css', array('wp-color-picker'), MEKS_ESS_VER );
            wp_enqueue_script( 'meks_ess_settings', MEKS_ESS_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), MEKS_ESS_VER, true );
        }
    }

    /**
     * Enqueue Frontend Scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style( 'meks_ess-main', MEKS_ESS_URL . 'assets/css/main.css', false, MEKS_ESS_VER );
        wp_enqueue_script( 'meks_ess-main', MEKS_ESS_URL . 'assets/js/main.js', array( 'jquery' ), MEKS_ESS_VER, true );

        $inline_styles = $this->get_inline_styles();
        if ( !empty( $inline_styles ) ) {
            wp_add_inline_style( 'meks_ess-main', $inline_styles );
        }

    }

    public function get_inline_styles() {
        $styles = '';
        if ( $this->options['color']['type'] == 'custom' ) {
            $styles = '
                body .meks_ess a {
                    background: '.$this->options['color']['custom_color'].' !important;
                }
                body .meks_ess.transparent a::before, body .meks_ess.transparent a span, body .meks_ess.outline a span {
                    color: '.$this->options['color']['custom_color'].' !important;
                }
                body .meks_ess.outline a::before {
                    color: '.$this->options['color']['custom_color'].' !important;
                }
                body .meks_ess.outline a {
                    border-color: '.$this->options['color']['custom_color'].' !important;
                }
                body .meks_ess.outline a:hover {
                    border-color: '.$this->options['color']['custom_color'].' !important;
                }
            ';

        }

        return $styles;
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

        if ( empty( $this->fields ) ) {
            return false;
        }

        $section_id = 'meks_ess_section';

        add_settings_section( $section_id, '', '', $this->slug );

        foreach ( $this->fields as $field ) {

            if ( empty( $field['id'] ) ) {
                continue;
            }

            $action = 'print_' . $field['id'] . '_field';
            $callback = method_exists( $this, $action ) ? array( $this, $action ) : $field['action'];

            add_settings_field(
                'meks_ess_' . $field['id'] . '_id',
                $field['title'],
                $callback,
                $this->slug,
                $section_id,
                $this->options[$field['id']]
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
            if ( isset( $input[$field['id']] ) ) {
                $new_input[$field['id']] = $this->sanitize_field( $input[$field['id']], $field['sanitize'] );
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

        case "checkbox":
            $new_input = array();
            foreach ( $value as $key => $val ) {
                $new_input[$key] = ( isset( $value[$key] ) ) ?
                    sanitize_text_field( $val ) :
                    '';
            }
            return $new_input;
            break;

        case "radio":
            return sanitize_text_field( $value );
            break;

        case "text":
            return sanitize_text_field( $value );
            break;

        default:
            break;
        }
    }

    /**
     * Sort Social platforms
     *
     * Use this function to properly order sortable options
     *
     * @param array   $items    Array of items
     * @param array   $selected Array of IDs of currently selected items
     * @return array ordered items
     * @since  1.0
     */
    private function sort_platforms( $platforms, $selected ) {

        if ( empty( $selected ) ) {
            return $platforms;
        }

        $new_items = array();
        $temp_items = array();
        $temp_items_ids = array();

        foreach ( $selected as $id ) {

            foreach ( $platforms as $key => $value ) {
                if ( $id == $key ) {
                    $new_items[$key] = $value;
                } else {
                    if ( !in_array( $key, $selected ) && !in_array( $key, $temp_items_ids ) ) {
                        $temp_items[$key] = $value;
                        $temp_items_ids[] = $key;
                    }
                }
            }

        }

        $new_items = array_merge( $new_items, $temp_items );

        return $new_items;
    }


    public static function get_platforms() {

        $platforms = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'reddit' => 'Reddit',
            'pinterest' => 'Pinterest',
            'email' => 'Email',
            'googleplus' => 'Google+',
            'linkedin' => 'LinkedIn',
            'stumbleupon' => 'StumbleUpon',
            'whatsapp' => 'WhatsApp',
            'vk' => 'vKontakte'
        );

        return $platforms;
    }

    /**
     * Print Social Share platforms fields
     */
    public function print_platforms_field( $args ) {

        $social_platforms = self::get_platforms();

        $social_platforms = apply_filters( 'meks_ess_modify_social_platforms', $social_platforms );

        $social_platforms = $this->sort_platforms( $social_platforms, $args );

?>
        <div class="platforms-sortable">
        <?php

        foreach ( $social_platforms as $soc_slug => $soc_name ) {
            $checked =  in_array( $soc_slug, $args ) ? $soc_slug : '';

            printf(
                '<label><input type="checkbox" id="meks-ess-platforms-%s" name="%s[platforms][]" value="%s" %s/> %s</label>',
                $soc_slug,
                $this->settings_key,
                $soc_slug,
                checked( $checked, $soc_slug, false ),
                $soc_name
            );
        }
?>
        </div>

        <?php
        printf( '<div class="platforms-note">%s</div>', __( 'Note: To reorder platforms just click, hold, and drag them.', 'meks-easy-social-share' ) );
    }

    /**
     * Print Styles
     */
    public function print_style_field( $args ) {

        $this->styles['style'] = apply_filters( 'meks_ess_modify_styles', $this->styles['style'] );

        $i = 1;

        foreach ( $this->styles['style'] as $key => $value ) {

            if ( $i % 2 !== 0 ) {
                echo '<div class="meks_ess_clear">';
            }

            printf(
                '<label class="meks-ess-style"><input type="radio" id="meks_ess-style-%s" name="%s[style]" value="%s" %s/><img src="%s" alt="style-%s"> <span>%s</span></label>',
                $key,
                $this->settings_key,
                $key,
                checked( $args, $key, false ),
                MEKS_ESS_URL . 'assets/images/style-'.$key.'.svg',
                $key,
                'Style '.$key
            );

            if ( $i % 2 == 0 ) {
                echo '</div>';
            }

            $i++;
        }

    }

    /**
     * Print Style Variant
     */
    public function print_variant_field( $args ) {

        $this->styles['variant'] =  apply_filters( 'meks_ess_modify_styles', $this->styles['variant'] );

        foreach ( $this->styles['variant'] as $key => $value ) {
            printf(
                '<label class="meks-ess-style meks-ess-style-variant"><input type="radio" id="meks_ess-variant-%s" name="%s[variant]" value="%s" %s/><img src="%s" alt="variant-%s"> <span>%s</span></label>',
                $key,
                $this->settings_key,
                $key,
                checked( $args, $key, false ),
                MEKS_ESS_URL . 'assets/images/variant-'.$key.'.svg',
                $key,
                ucfirst( $value )
            );
        }

    }

    /**
     * Print Style Colors
     */
    public function print_color_field( $args ) {


        printf(
            '<label class="meks_ess-color"><input type="radio" id="meks_ess-color-brand" name="%s[color][type]" value="brand" %s/>%s</label><br>',
            $this->settings_key,
            checked( $args['type'], 'brand', false ),
            __( 'Brand' , 'meks-easy-social-share' )
        );

        printf(
            '<label class="meks_ess-color"><input type="radio" id="meks_ess-color-custom" name="%s[color][type]" value="custom" %s/>%s</label><br>',
            $this->settings_key,
            checked( $args['type'], 'custom', false ),
            __( 'Custom' , 'meks-easy-social-share' )
        );

        printf( '<input type="text" id="meks_ess-custom-color" name="%s[color][custom_color]" value="%s" />',
            $this->settings_key,
            $args['custom_color']
        );


    }

    /**
     * Print Locations radio buttons
     */
    public function print_location_field( $args ) {

        printf(
            '<label><input type="radio" id="meks_ess_location_above" name="%s[location]" value="%s" %s/> %s</label><br>',
            $this->settings_key,
            'above',
            checked( $args, 'above', false ),
            __( 'Above content', 'meks-easy-social-share' )
        );
        printf(
            '<label><input type="radio" id="meks_ess_location_below" name="%s[location]" value="%s" %s/> %s</label><br>',
            $this->settings_key,
            'below',
            checked( $args, 'below', false ),
            __( 'Below content', 'meks-easy-social-share' )
        );
        printf(
            '<label><input type="radio" id="meks_ess_location_above_below" name="%s[location]" value="%s" %s/> %s</label><br>',
            $this->settings_key,
            'above_below',
            checked( $args, 'above_below', false ),
            __( 'Above and below content', 'meks-easy-social-share' )
        );
        printf(
            '<label><input type="radio" id="meks_ess_location_custom" name="%s[location]" value="%s" %s/> %s</label>',
            $this->settings_key,
            'custom',
            checked( $args, 'custom', false ),
            __( 'Custom (template tag) use:', 'meks-easy-social-share' ) . ' <code> meks_ess_share(); </code>'
        );

    }

    /**
     * Print Post Types fields
     */
    public function print_post_type_field( $args ) {

        $post_types = meks_ess_post_types();

        foreach ( $post_types as $key => $type ) {

            $checked =  in_array( $key, $args ) ? $key : '';

            printf(
                '<label><input type="checkbox" id="meks_ess_post_type_%s" name="%s[post_type][]" value="%s" %s/> %s</label><br>',
                $key,
                $this->settings_key,
                $key,
                checked( $checked, $key, false ),
                $type->label
            );
        }

    }

    /**
     * Print Label field
     */
    public function print_label_share_field( $args ) {

        printf(
            '<label><input type="text" id="meks_ess_label" name="%s[label_share][text]" value="%s"/></label><br>',
            $this->settings_key,
            esc_html( $args['text'] )
        );

        printf(
            '<label>
                <input type="hidden" id="meks_ess_share_label_active" name="%s[label_share][active]" value="0" />
                <input type="checkbox" id="meks_ess_share_label_active" name="%s[label_share][active]" value="1" %s/>
                %s
            </label><br>',
            $this->settings_key,
            $this->settings_key,
            checked( $args['active'], '1', false ),
            __( 'Enabled', 'meks-easy-social-share' )
        );


    }


    public function parse_settings_for_output() {

        $before = '<div class="meks_ess layout-'.$this->options['style'].'-'.$this->options['variant'].' '.$this->styles['style'][$this->options['style']].' '.$this->styles['variant'][$this->options['variant']].'">';
        $after = '</div>';

        $share_label = '';

        if ( $this->options['label_share']['active'] ) {

            $share_label = $this->options['label_share']['text'];

            if ( empty( $share_label ) ) {
                $share_label = esc_html__( 'Share this', 'meks-easy-social-share' );
            }

        }

        if (  !empty( $share_label ) ) {
            $before = '<div class="meks_ess_share_label"><h5>' . $share_label . '</h5></div>'. $before;
        }

        return array( 'platforms' => $this->options['platforms'], 'before' => $before, 'after' => $after );
    }


    public function pre_get_posts( $query ){
        if( $query->is_main_query() ){
            add_filter( 'the_content', array( $this, 'print_social_share' ) );
        }
    }

    /**
     * Draw social share box base on settings
     *
     * @return void
     */
    public function print_social_share( $content ) {

        global $wp_current_filter;
        $counter = count($wp_current_filter);

        if ( $counter == 1 ) {
            remove_filter( 'the_content', array( $this, 'print_social_share' ) );
            remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
        } 

        if ( empty( $this->options['platforms'] ) || empty( $this->options['post_type'] ) || $this->options['location'] == 'custom' ) {
            return $content;
        }

        if ( is_front_page() || is_home() || !is_singular( $this->options['post_type'] )  ) {
            return $content;
        }

        $output = meks_ess_share( array(), false, '', '' );

        switch ( $this->options['location'] ) {

        case 'above':
            return $output . $content;
            break;

        case 'below':
            return $content . $output;
            break;

        case 'above_below':
            return $output . $content . $output;
            break;

        default:
            break;
        }

        return $content;

    }



}
