<?php

/**
* Class MKS_AdsBlocker_Widget
*/
class MKS_AdsBlocker_Widget extends WP_Widget {

    /**
    * Constructs the new widget.
    *
    * @see WP_Widget::__construct()
    */
    function __construct() {
        parent::__construct(
            'mks_widget_hidden',
            esc_html__( 'Ad Blocker Message', 'meks-easy-ads-widget' ), // Name
            array(
                'description' => esc_html__( 'By default, this widget will be hidden. If the user has the AdBlocker plugin installed on their browser this widget will be displayed.', 'meks-easy-ads-widget' ),
            ) // Args
        );

    }


    /**
    * The widget's HTML output.
    *
    * @see WP_Widget::widget()
    *
    * @param array args     Display arguments including before_title, after_title,
    *                        before_widget, and after_widget.
    * @param array instance The settings for the particular instance of the widget.
    */
    function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        if ( ! empty( $instance['message'] ) ) { 
            ?>
                <div style="background:red; color:#FFF; padding:20px;"><?php echo wp_kses( $instance['message'], wp_kses_allowed_html( 'post' ) ); ?></div>
            <?php 
        }

        echo $args['after_widget'];
    }


    /**
    * The widget update handler.
    *
    * @see WP_Widget::update()
    *
    * @param array new_instance The new instance of the widget.
    * @param array old_instance The old instance of the widget.
    * @return array The updated instance of the widget.
    */
    function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['message'] = ( ! empty( $new_instance['message'] ) ) ? sanitize_text_field( $new_instance['message'] ) : '';
        return $new_instance;
    }


    /**
    * Output the admin widget options form HTML.
    *
    * @param array instance The current widget settings.
    * @return string The HTML markup for the form.
    */
    function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'AdBlocker Message', 'meks-easy-ads-widget' );
        $message = ! empty( $instance['message'] ) ? $instance['message'] : __( 'Our website is made possible by displaying online advertisements to our visitors. Please consider supporting us by disabling your ad blocker.', 'meks-easy-ads-widget' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'meks-easy-ads-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>"><?php esc_attr_e( 'Message:', 'meks-easy-ads-widget' ); ?></label>
            <textarea rows="5" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'message' ) ); ?>"><?php echo wp_kses( $message, wp_kses_allowed_html('post') ); ?></textarea>
        </p>
        <?php
    }

}
