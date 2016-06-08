<?php

class CreativeCommons_Resource_Homepage_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __('CC Homepage Resource Widget', 'creativecommons') );

    $this->default_category  = 'featured-inside-page';
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  function form( $instance ) {
    $title = isset( $instance['title' ] ) ? $instance['title'] : NULL;
    $textarea = isset($instance['textarea']) ? esc_textarea($instance['textarea']) : NULL;

    if ( false === $title ) {
      $title = $this->default_title;
    }
    ?>

    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'The Growing Commons' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id('textarea') ); ?>"><?php _e('Introduction:', 'wp_widget_plugin'); ?></label>
      <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id('textarea') ); ?>" name="<?php echo esc_attr( $this->get_field_name('textarea') ); ?>"><?php echo esc_html( $textarea ); ?></textarea>
    </p>
    <?php
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title']     = wp_kses( $new_instance['title'],     array() );

    if ( $this->default_title === $instance['title'] ) {
      $instance['title'] = false; // Store as false in case of language change
    }

    if ( current_user_can('unfiltered_html') )
      $instance['textarea'] =  $new_instance['textarea'];
    else
      $instance['textarea'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['textarea']) ) );

    return $instance;
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  function widget( $args, $instance ) {
    wp_enqueue_script(
      'cc-resource',
      plugin_dir_url( __FILE__ ) . 'js/cc-resource.js', array('cc-common'),
      '20160608',
      true
    );

    wp_localize_script('cc-resource', 'CC_RESOURCE', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'initial' => cc_get_resources(0, 24)
    ));

    $title = isset( $instance['title' ] ) ? $instance['title'] : NULL;
    $textarea = apply_filters( 'widget_textarea', empty( $instance['textarea'] ) ? '' : $instance['textarea'], $instance );

    if (! $title ){
      $title = $this->default_title;
    }

    $title = apply_filters( 'widget_title', $title );

    echo $args['before_widget'];

    echo '<div class="homepage-resources-widget">';
    echo '<div class="widget-header">';
    if (!empty($title)) {
      echo $args['before_title'];
      echo $title;
      echo $args['after_title'];
    }
    echo '<div class="intro">'.wpautop($textarea).'</div>';
    echo '</div>';
    echo '<div class="cc-footer-float-from"></div>';
    echo '<div class="resource-list"></div>';
    // TODO: It would be nice to put a no-js fallback here.
    echo '</div>';

    echo $args['after_widget'];
  }
}

function cc_resource_homepage_widget_init() {
  register_widget( 'CreativeCommons_Resource_Homepage_Widget' );
}

add_action( 'widgets_init', 'cc_resource_homepage_widget_init' );
