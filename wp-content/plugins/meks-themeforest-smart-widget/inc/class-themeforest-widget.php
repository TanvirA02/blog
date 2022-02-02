<?php

/*	ThemeForest Widget Class */

class MKS_ThemeForest_Widget extends WP_Widget {

	var $tf_cats; //ThemeForest items categories
	var $exclude; //Wheter to exclude items or not
	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'mks_themeforest_widget', 'description' => __( 'Display ThemeForest items with this widget', 'meks-themeforest-smart-widget' ) );
		$control_ops = array( 'id_base' => 'mks_themeforest_widget' );
		parent::__construct( 'mks_themeforest_widget', __( 'Meks ThemeForest Smart Widget', 'meks-themeforest-smart-widget' ), $widget_ops, $control_ops );

		$this->tf_cats = array(
			array( 'name' => 'wordpress', 'title' => 'WordPress' ),
			array( 'name' => 'site-templates', 'title' => 'Site Templates' ),
			array( 'name' => 'psd-templates', 'title' => 'PSD Templates' ),
			array( 'name' => 'cms-themes', 'title' => 'CMS Themes' ),
			array( 'name' => 'ecommerce', 'title' => 'eCommerce' ),
			array( 'name' => 'blogging', 'title' => 'Blogging' ),
			array( 'name' => 'marketing', 'title' => 'Marketing' ),
			array( 'name' => 'forums', 'title' => 'Forums' ),
			array( 'name' => 'muse-templates', 'title' => 'Muse Templates' ),
			array( 'name' => 'typeengine-themes', 'title' => 'TypeEngine Themes' )
		);

		$this->exclude = array();

		if ( !is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		} else {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		$this->defaults = array(
			'title' => 'ThemeForest',
			'description' => '',
			'items_type' => array( 'wordpress' ),
			'items_from' => 'user',
			'user' => 'meks',
			'num_items' => 9,
			'orderby' => 'uploaded_on',
			'ref' => 'meks',
			'more_link_url' => 'https://themeforest.net/user/meks/portfolio?ref=meks',
			'more_link_txt' => __( 'View more', 'meks-themeforest-smart-widget' ),
			'order' => 'desc',
			'target' => '_blank',
			'exclude' => '',
			'nofollow' => 0,
			'token' => ''
		);

		//Allow themes or plugins to modify default parameters
		$this->defaults = apply_filters( 'mks_tf_widget_modify_defaults', $this->defaults );

	}


	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		} ?>

		<?php if ( !empty( $instance['description'] ) ) : ?>
			<p><?php echo nl2br( $instance['description'] );?></p>
		<?php endif; ?>

		<?php

		if ( isset( $instance['exclude'] ) && !empty( $instance['exclude'] ) ) {
			$this->exclude = explode( ',', $instance['exclude'] );
			$this->exclude = array_map( 'absint', $this->exclude );
		}

		$this->token = $instance['token'];

		$items = array();
		$order = false;
		switch ( $instance['items_from'] ) {
			case 'popular': $items = $this->get_popular_items( $instance['items_type'] ); break;
			case 'latest': $items = $this->get_latest_items( $instance['items_type'] ); break;
			default:
				if ( !empty( $instance['user'] ) ) {
					$users = array_map( 'trim', explode( ',', $instance['user'] ) );
					$items = $this->get_items_from_users( $users, $instance['items_type'] );

				} break;
		}

		if ( !empty( $items ) ):

		if( $instance['items_from'] == 'user'){

			$this->orderby = $instance['orderby'];
			$this->items_order = $instance['order'];

			if ( $this->orderby != 'random' ) {
				usort( $items, array( $this, "cmp" ) );
			} else {
				shuffle( $items );
			}
		}

		$items = array_slice( $items, 0, absint( $instance['num_items'] ) );
		$ref = !empty( $instance['ref'] ) ? '?ref='.$instance['ref'] : '';
		$target = !empty( $instance['target'] ) ? $instance['target'] : '_blank';
		$nofollow = $instance['nofollow'] ? 'rel="nofollow"' : '';
?>
		<ul class="mks_themeforest_widget_ul">
		    <?php foreach ( $items as $item ) : ?>
		    <li><a href="<?php echo esc_url( $item['url'].$ref ); ?>" title="<?php echo esc_attr( $item['item'] ); ?>" target="<?php echo $target; ?>" <?php echo $nofollow; ?>><img width="80" height="80" src="<?php echo esc_url( $item['thumbnail'] );?>" alt="<?php echo esc_attr( $item['item'] ); ?> "/></a></li>
		    <?php endforeach; ?>
		</ul>

		<?php if ( !empty( $instance['more_link_url'] ) ): ?>
			 <?php $more_text = isset( $instance['more_link_txt'] ) && !empty( $instance['more_link_txt'] ) ? $instance['more_link_txt'] : __( 'View more', 'meks-themeforest-smart-widget' ); ?>
			  <p class="mks_read_more"><a href="<?php echo esc_url( $instance['more_link_url'] ); ?>" target="_blank" class="more" <?php echo $nofollow; ?>><?php echo  esc_html( $more_text ); ?></a></p>
			 <?php endif; ?>
		<?php endif; ?>
		
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['description'] = strip_tags( $new_instance['description'] );
		$instance['user'] = strip_tags( $new_instance['user'] );
		$instance['num_items'] = absint( $new_instance['num_items'] );
		$instance['exclude'] = strip_tags( $new_instance['exclude'] );
		$instance['ref'] = strip_tags( $new_instance['ref'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['more_link_url'] = $new_instance['more_link_url'];
		$instance['more_link_txt'] = $new_instance['more_link_txt'];
		$instance['order'] = $new_instance['order'];
		$instance['items_type'] = $new_instance['items_type'];
		$instance['items_from'] = $new_instance['items_from'];
		$instance['target'] = $new_instance['target'];
		$instance['nofollow'] = isset( $new_instance['nofollow'] ) ? 1 : 0;
		$instance['token'] = strip_tags( $new_instance['token'] );
		return $instance;
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults ); ?>

		<p>
		    <label for="<?php echo $this->get_field_id( 'title' ); ?>">
		        <?php _e( 'Title', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'description' ); ?>">
		        <?php _e( 'Description', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <textarea id="<?php echo $this->get_field_id( 'description' ); ?>" rows="5" name="<?php echo $this->get_field_name( 'description' ); ?>" class="widefat"><?php echo $instance['description']; ?></textarea>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'token' ); ?>">
		        <?php _e( 'Envato API token', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'token' ); ?>" type="text" name="<?php echo $this->get_field_name( 'token' ); ?>" value="<?php echo esc_attr( strip_tags( $instance['token'] ) ); ?>" class="widefat" />
		    <small class="howto"><?php _e( 'It is highly recommended to provide your Envato API token as using the service without it will be discontinued soon.', 'meks-themeforest-smart-widget' ); ?></small>
		    <small class="howto"><?php echo '<a href="https://build.envato.com/create-token/" target="_blank">'.__( 'Generate token', 'meks-themeforest-smart-widget' ).'</a>'; ?></small>
		    
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'num_items' ); ?>">
		        <?php _e( 'Number of items to show', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'num_items' ); ?>" type="number" name="<?php echo $this->get_field_name( 'num_items' ); ?>" value="<?php echo absint( $instance['num_items'] ); ?>" class="widefat" />
		</p>
		<p>
		    <label>
		        <?php _e( 'Select items from', 'meks-themeforest-smart-widget' ); ?>:</label><br />
		    <input id="<?php echo $this->get_field_id( 'select_from_popular' ); ?>" type="radio" name="<?php echo $this->get_field_name( 'items_from' ); ?>" value="popular" <?php checked( $instance['items_from'], 'popular' );?> class="meks-themeforest-smart-widget-type" /> <label for="<?php echo $this->get_field_id( 'select_from_popular' ); ?>">
		        <?php _e( 'Popular Items (WordPress Only)', 'meks-themeforest-smart-widget' ); ?></label><br />
		    <input id="<?php echo $this->get_field_id( 'select_from_latest' ); ?>" type="radio" name="<?php echo $this->get_field_name( 'items_from' ); ?>" value="latest" <?php checked( $instance['items_from'], 'latest' );?> class="meks-themeforest-smart-widget-type" /> <label for="<?php echo $this->get_field_id( 'select_from_latest' ); ?>">
		        <?php _e( 'Latest Items', 'meks-themeforest-smart-widget' ); ?></label><br />
		    <input id="<?php echo $this->get_field_id( 'select_from_user' ); ?>" type="radio" name="<?php echo $this->get_field_name( 'items_from' ); ?>" value="user" <?php checked( $instance['items_from'], 'user' );?> class="meks-themeforest-smart-widget-type" /> <label for="<?php echo $this->get_field_id( 'select_from_user' ); ?>">
		        <?php _e( 'Specific User(s)', 'meks-themeforest-smart-widget' ); ?></label>
		</p>
		<p class="meks-themeforest-smart-widget-user" <?php echo $this->selected( $instance['items_from'], 'user', 'style="display:none;"', true ); ?>>
		    <label for="<?php echo $this->get_field_id( 'user' ); ?>">
		        <?php _e( 'ThemeForest username(s)', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'user' ); ?>" type="text" name="<?php echo $this->get_field_name( 'user' ); ?>" value="<?php echo strip_tags( $instance['user'] ); ?>" class="widefat" />
		    <small class="howto"><?php _e( 'For multiple users, separate by comma: i.e. user1,user2,user3', 'meks-themeforest-smart-widget' ); ?></small>
		</p>
		<p class="meks-themeforest-smart-widget-cat" <?php echo $this->selected( $instance['items_from'], 'popular', 'style="display:none;"'); ?>>
		    <label>
		        <?php _e( 'Item categories to show', 'meks-themeforest-smart-widget' ); ?>:</label><br />
		    <?php foreach ( $this->tf_cats as $cat ) : ?>
		    <input id="<?php echo $this->get_field_id( $cat['name'].'_id' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'items_type' ); ?>[]" value="<?php echo esc_attr( $cat['name'] ); ?>" <?php echo in_array( $cat['name'], $instance['items_type'] ) ? 'checked' : '' ; ?> /> <label for="<?php echo $this->get_field_id( $cat['name'].'_id' ); ?>">
		        <?php echo $cat['title']; ?></label><br />
		    <?php endforeach; ?>
		</p>
		<p class="meks-themeforest-smart-widget-order" <?php echo $this->selected( $instance['items_from'], 'user', 'style="display:none;"', true ); ?>>
		    <label>
		        <?php _e( 'Order by', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" value="<?php echo esc_attr( $instance['orderby'] ); ?>" class="widefat">
		        <option value="uploaded_on" <?php selected( $instance['orderby'], 'uploaded_on' );?>>
		            <?php _e( 'Upload date', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		        <option value="last_update" <?php selected( $instance['orderby'], 'last_update' );?>>
		            <?php _e( 'Last update', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		        <option value="sales" <?php selected( $instance['orderby'], 'sales' );?>>
		            <?php _e( 'Number of sales', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		        <option value="cost" <?php selected( $instance['orderby'], 'cost' );?>>
		            <?php _e( 'Price', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		        <option value="random" <?php selected( $instance['orderby'], 'random' );?>>
		            <?php _e( 'Random', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		    </select>
		</p>
		<p class="meks-themeforest-smart-widget-order" <?php echo $this->selected( $instance['items_from'], 'user', 'style="display:none;"', true ); ?>>
		    <input id="<?php echo $this->get_field_id( 'order_asc' ); ?>" type="radio" name="<?php echo $this->get_field_name( 'order' ); ?>" value="asc" <?php checked( $instance['order'], 'asc' );?> /> <label for="<?php echo $this->get_field_id( 'order_asc' ); ?>">
		        <?php _e( 'Ascending', 'meks-themeforest-smart-widget' ); ?></label>
		    <input id="<?php echo $this->get_field_id( 'order_desc' ); ?>" type="radio" name="<?php echo $this->get_field_name( 'order' ); ?>" value="desc" <?php checked( $instance['order'], 'desc' );?> /> <label for="<?php echo $this->get_field_id( 'order_desc' ); ?>">
		        <?php _e( 'Descending', 'meks-themeforest-smart-widget' ); ?></label>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'exclude' ); ?>">
		        <?php _e( 'Exclude item(s)', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'exclude' ); ?>" type="text" name="<?php echo $this->get_field_name( 'exclude' ); ?>" value="<?php echo strip_tags( $instance['exclude'] ); ?>" class="widefat" />
		    <small class="howto"><?php _e( 'Specify item ID to exclude specific item (separate by comma for multiple items): i.e. 8134834,7184572', 'meks-themeforest-smart-widget' ); ?></small>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'ref' ); ?>">
		        <?php _e( 'Referral user', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'ref' ); ?>" type="text" name="<?php echo $this->get_field_name( 'ref' ); ?>" value="<?php echo strip_tags( $instance['ref'] ); ?>" class="widefat" />
		    <small class="howto"><?php _e( 'Specify username if you want to use items as ThemeForest affiliate links', 'meks-themeforest-smart-widget' ); ?></small>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'more_link_url' ); ?>">
		        <?php _e( 'More link URL', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'more_link_url' ); ?>" type="text" name="<?php echo $this->get_field_name( 'more_link_url' ); ?>" value="<?php echo esc_attr( $instance['more_link_url'] ); ?>" class="widefat" />
		    <small class="howto"><?php _e( 'Specify URL if you want to show "more" link under the items list', 'meks-themeforest-smart-widget' ); ?></small>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'more_link_txt' ); ?>">
		        <?php _e( 'More link text', 'meks-themeforest-smart-widget' ); ?>:</label>
		    <input id="<?php echo $this->get_field_id( 'more_link_txt' ); ?>" type="text" name="<?php echo $this->get_field_name( 'more_link_txt' ); ?>" value="<?php echo esc_attr( $instance['more_link_txt'] ); ?>" class="widefat" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'target' ); ?>">
		        <?php _e( 'Open items in', 'meks-themeforest-smart-widget' ); ?>: </label>
		    <select id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>">
		        <option value="_blank" <?php selected( '_blank' , $instance['target'] ); ?>>
		            <?php _e( 'New Window', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		        <option value="_self" <?php selected( '_self' , $instance['target'] ); ?>>
		            <?php _e( 'Same Window', 'meks-themeforest-smart-widget' ); ?>
		        </option>
		    </select>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'nofollow' ); ?>"><input id="<?php echo $this->get_field_id( 'nofollow' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'nofollow' ); ?>" value="1" <?php checked( $instance['nofollow'], 1 ); ?> class="widefat" />
		        <?php _e( 'Add rel="nofollow" to item links', 'meks-themeforest-smart-widget' ); ?>
		    </label>
		</p>

	<?php
	}

	function envato_request( $query ) {

		$args = array();

		//If token is provided we are using
		if ( $this->has_token() ) {
			$headers = array();
			$headers[] = 'Content-length: 0';
			$headers['Content-type'] = 'application/json';
			$headers['Authorization'] = 'Bearer '.$this->token;
			$args = array(
				'headers' => $headers
			);

			$url = 'https://api.envato.com/v1/market/';

		} else {
			$url = 'http://marketplace.envato.com/api/v3/';
		}

		$url .= $query;

		//print_r( $url );

		$response = wp_remote_get( $url,  $args );

		//print_r( $response );

		//print_r( $response );

		if ( is_wp_error( $response ) || ( wp_remote_retrieve_response_code( $response ) != 200 ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );

	}

	function has_token() {
		return isset( $this->token ) && !empty( $this->token );
	}

	function get_items_from_users( $users = array(), $type = array( 'wordpress' ) ) {

		$items = array();

		foreach ( $users as $user ) {
			$cached = get_transient( $this->id_base.'_'.$user );
			$item_data_ready = array();

			if ( empty( $cached ) ) {

				$query = 'new-files-from-user:'.$user.',themeforest.json';

				$response = $this->envato_request( $query );

				if ( !isset( $response['new-files-from-user'] ) || empty( $response['new-files-from-user'] ) ) {
					continue;
				}

				$item_data_ready = $response['new-files-from-user'];
				set_transient( $this->id_base.'_'.$user, $item_data_ready, DAY_IN_SECONDS );

			} else {
				$item_data_ready = $cached;
			}

			$type_check = count( $type ) == count( $this->tf_cats ) ? false : true;

			foreach ( $item_data_ready as $item ) {
				if ( !in_array( $item['id'], $this->exclude ) ) {
					if ( $type_check ) {
						if ( $this->item_type_check( trim( $item['category'] ), $type ) ) {
							$items[] = $item;
						}
					} else {
						$items[] = $item;
					}
				}
			}
		}

		return $items;

	}

	function get_popular_items( $type = array( 'wordpress' ) ) {

		$type = array( 'wordpress' ); //Hardcoded to WordPres only, due to current ThemeForest API limitations

		$items = array();
		$cached = get_transient( $this->id_base.'_popular' );

		$item_data_ready = array();

		if ( empty( $cached ) ) {

			$query = 'popular:themeforest.json';

			$response = $this->envato_request( $query );

			if ( !isset( $response['popular']['items_last_week'] ) || empty( $response['popular']['items_last_week'] ) ) {
				return $items;
			}

			$item_data_ready = $response['popular']['items_last_week'];
			set_transient( $this->id_base.'_popular', $item_data_ready, DAY_IN_SECONDS );

		} else {
			$item_data_ready = $cached;
		}

		$type_check = count( $type ) == count( $this->tf_cats ) ? false : true;

		foreach ( $item_data_ready as $item ) {
			if ( !in_array( $item['id'], $this->exclude ) ) {
				if ( $type_check ) {
					if ( $this->item_type_check( trim( $item['category'] ), $type ) ) {
						$items[] = $item;
					}
				} else {
					$items[] = $item;
				}
			}
		}

		return $items;

	}


	function get_latest_items( $types = array( 'wordpress' ) ) {

		$items = array();

		foreach ( $types as $type ) {
			$cached = get_transient( $this->id_base.'_'.$type );
			$item_data_ready = array();
			if ( empty( $cached ) ) {

				$query = 'new-files:themeforest,'.$type.'.json';

				$response = $this->envato_request( $query );

				//print_r( $response['new-files'] );

				if ( !isset( $response['new-files'] ) || empty( $response['new-files'] ) ) {
					continue;
				}

				$item_data_ready = $response['new-files'];
				set_transient( $this->id_base.'_'.$type, $item_data_ready, DAY_IN_SECONDS );

			} else {
				$item_data_ready = $cached;
			}

			foreach ( $item_data_ready as $item ) {
				if ( !in_array( $item['id'], $this->exclude ) ) {
					$items[] = $item;
				}
			}
		}

		return $items;

	}

	function item_type_check( $category, $types ) {

		foreach ( $types as $type ) {
			if ( strpos( 'mks'.$category, $type ) ) {
				return true;
			}
		}

		return false;
	}

	function enqueue_styles() {
		wp_register_style( 'meks-themeforest-widget', MTW_PLUGIN_URI . 'css/style.css', false, MKS_TF_WIDGET_VER );
		wp_enqueue_style( 'meks-themeforest-widget' );
	}

	function enqueue_scripts() {
		global $pagenow, $typenow;

		if( $pagenow == 'widgets.php' ){
			wp_enqueue_script( 'meks-themeforest-widget', MTW_PLUGIN_URI . 'js/admin.js', array('jquery') , MKS_TF_WIDGET_VER );
		}	
	}

	function selected( $a, $b, $output, $reverse = false ) {
		if($reverse){
			return (string) $a !== (string) $b ? $output : '';
		} else {
		   return (string) $a === (string) $b ? $output : '';
		}
	}

	function cmp( $a, $b ) {
		if ( $this->orderby == 'last_update' || $this->orderby == 'uploaded_on' ) {
			if ( $this->items_order == 'desc' ) {
				return strcmp( strtotime( $b[$this->orderby] ), strtotime( $a[$this->orderby] ) );
			} else {
				return strcmp( strtotime( $a[$this->orderby] ), strtotime( $b[$this->orderby] ) );
			}
		} else {
			if ( $this->items_order == 'desc' ) {
				return $b[$this->orderby] > $a[$this->orderby] ? true : false;
			} else {
				return $b[$this->orderby] > $a[$this->orderby] ? false : true;
			}

		}
	}

}

?>
