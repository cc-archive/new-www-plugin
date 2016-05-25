<?php

class CreativeCommons_News_Features_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __("CC News Features Widget", 'creativecommons'));
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

    echo $args['before_widget'];

    ?>

      <div class="news-features-widget feature-widget">
        <div class="news-features-widget-inner feature-widget-inner">
          <h2 class="txt-hero">News</h2>
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
                    <div class="title"><a href="<?php print $url; ?>"><?php print get_the_title() ?></a></div>
                    <div class="author-wrapper">
                      <div class="author-image">
                      <?php
                        $author_bio_avatar_size = apply_filters( 'twentysixteen_author_bio_avatar_size', 38 );
                        echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
                        ?>
                      </div>
                      <div class="author-info-group">
                        <div class="author-date"><?php the_date(); ?></div>
                        <div class="author-name"><h4><?php echo get_the_author(); ?></h4></div>
                      </div>
                    </div>
                    <div class="excerpt"><?php print the_excerpt(); ?></div>
                    <div class="category"><?php print $category_link; ?></div>
                  </div>
                </div>
             <?php } ?>
          </div>
          <div class="features posts-featured">
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
                  <div class="title"><a href="<?php print $url; ?>"><?php print get_the_title() ?></a></div>
                  <div class="teaser">
	                  <div class="thumbnail">
	                    <a href="<?php print $url; ?>"><?php print the_post_thumbnail('cc_list_post_thumbnail'); ?></a>
	                  </div>
										<div class="right-side-wrapper">
	                    <div class="author-wrapper">
	                      <div class="author-image">
	                      <?php
	                        $author_bio_avatar_size = apply_filters( 'twentysixteen_author_bio_avatar_size', 38 );
	                        echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
	                        ?>
	                      </div>
	                      <div class="author-info-group">
	                        <div class="author-date"><?php the_date(); ?></div>
	                        <div class="author-name"><h4><?php echo get_the_author(); ?></h4></div>
	                      </div>
	                    </div>
	                    <div class="excerpt"><?php print the_excerpt(); ?></div>
	                    <div class="category"><?php print $category_link; ?></div>
										</div>
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

function cc_news_features_widget_init() {
  register_widget( 'CreativeCommons_News_Features_Widget' );
}

add_action( 'widgets_init', 'cc_news_features_widget_init' );
