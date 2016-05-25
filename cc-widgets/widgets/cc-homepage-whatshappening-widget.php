<?php

class CreativeCommons_Homepage_WhatsHappening_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __("CC Homepage What's Happening Widget", 'creativecommons'));
    $this->default_category  = 'homepage';
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

    echo $args['before_widget'];

    ?>

      <div class="homepage-whatshappening-widget">
        <div class="homepage-whatshappening-widget-inner">
          <h2 class="txt-hero"> What's<br />Happening</h2>
          <div class="post-hero">
            <?php
            // The hero feature
            $the_query = cc_widgets_get_homepage_features_query('hero', 1);
            while ( $the_query->have_posts() ){
              $the_query->the_post();
              $url = get_permalink();
              $categories = get_the_category();
              if ( ! empty( $categories ) ) {
                $category_link  ='<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
              } else {
                $category_link = NULL;
              }
              ?>
                <div class="item">
                  <div class="thumbnail">
                    <a href="<?php print $url; ?>"><?php print the_post_thumbnail('large'); ?></a>
                  </div>
                  <div class="teaser">
                    <h3 class="title"><a href="<?php print $url; ?>"><?php print get_the_title() ?></a></h3>
                    <div class="excerpt"><?php print the_excerpt(); ?></div>
                    <div class="category"><?php print $category_link; ?></div>
                  </div>
                </div>
             <?php } ?>
          </div>
          <div class="posts-featured">
            <?php
            // The four other features
            $the_query = cc_widgets_get_homepage_features_query('featured', 4);
            while ( $the_query->have_posts() ){
              $the_query->the_post();
              $url = get_permalink();
              $categories = get_the_category();
              if ( ! empty( $categories ) ) {
                $category_link  ='<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
              } else {
                $category_link = NULL;
              }
              ?>
                <div class="item">
                  <div class="thumbnail">
                    <a href="<?php print $url; ?>"><?php print the_post_thumbnail(); ?></a>
                  </div>
                  <div class="teaser">
                    <h3 class="title"><a href="<?php print $url; ?>"><?php print get_the_title() ?></a></h3>
                    <div class="category"><?php print $category_link; ?></div>
                  </div>
                </div>
             <?php } ?>
          </div>
          <div class="more"><a href="/news">More News<i class="cc-icon-right-dir"></i></a></div>
        </div>
      </div>

    <?php

    echo $args['after_widget'];
  }
}

function cc_homepage_whatshappening_widget_init() {
  register_widget( 'CreativeCommons_Homepage_WhatsHappening_Widget' );
}

add_action( 'widgets_init', 'cc_homepage_whatshappening_widget_init' );
