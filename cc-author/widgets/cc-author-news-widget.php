<?php

class CreativeCommons_Author_News_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __("CC Author News Widget", 'creativecommons'));
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

    $show_widget = FALSE;

    global $wp_query;

    if (is_author()){

      $author = get_userdata( $wp_query->get('author'));

      $the_query = cc_author_get_author_news_query($author->ID, 4);
      if ($the_query->have_posts()) {
        $show_widget = TRUE;
      }
    }

    if (! $show_widget){
      return NULL;
    }

    echo $args['before_widget'];

    ?>

      <div class="author-news-widget">
        <div class="author-news-widget-inner">
          <h2><?php print $author->first_name ?>'s News</h2>
          <div class="features">
            <?php
            // The four related news stories features
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
                    <div class="title"><a href="<?php print $url; ?>"><?php print get_the_title() ?></a></div>
                    <div class="read-more"><a class="moretag" href="<?php print get_permalink(); ?>">Learn more</a></div>
                  </div>
                </div>
             <?php } ?>
          </div>
        </div>
      </div>

    <?php

    echo $args['after_widget'];
  }
}

function cc_author_news_widget_init() {
  register_widget( 'CreativeCommons_Author_News_Widget' );
}

add_action( 'widgets_init', 'cc_author_news_widget_init' );
