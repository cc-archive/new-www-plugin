<?php

class CreativeCommons_Author_Navigation_Sidebar_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __("CC Author Navigation Sidebar Widget", 'creativecommons'));
    $this->default_category  = 'news';
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

    $about_page_id = 16;

    $children = wp_list_pages( array(
        'title_li' => NULL,
        'child_of' => $about_page_id,
        'echo'     => 0,
        'depth'    => 1,
        'sort_column' => 'menu_order'
    ) );

    if ( $children ){
      // Add the active class to the Team item.
      $children = str_replace('page-item-266', 'page-item-266 current_page_item', $children);

      echo $args['before_widget'];
      $title = apply_filters( 'widget_title', $instance['title'] );
      if ( ! empty( $title ) ){
        echo $args['before_title'] . $title . $args['after_title'];
      }
    ?>

          <div class="author-navigation-sidebar-widget">
        <ul>
            <?php echo $children; ?>
        </ul>

          </div>

    <?php
      echo $args['after_widget'];
    }
  }

  // Widget Backend
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    } else {
      $title = __( 'New title', 'wpb_widget_domain' );
    }
    ?>
    <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    return $instance;
  }
}



function cc_author_navigation_sidebar_widget_init() {
  register_widget( 'CreativeCommons_Author_Navigation_Sidebar_Widget' );
}

add_action( 'widgets_init', 'cc_author_navigation_sidebar_widget_init' );
