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
              $hero_post_id = get_the_ID();
              if ( class_exists( 'coauthors_plus' ) ) { // Get the Co-Authors for the post
                $hero_authors = get_coauthors($hero_post_id);
              } else {
                $hero_authors = array();
              }
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
                          if (count($hero_authors)){
                            foreach ($hero_authors as $an_author){
                              echo '<a href="' . get_author_posts_url(get_the_author_meta( 'ID', $an_author->ID ), get_the_author_meta( 'user_nicename', $an_author->ID )) . '">' . get_avatar( get_the_author_meta( 'user_email', $an_author->ID ), $author_bio_avatar_size ) . '</a>';
                            }
                          } else {
                            echo '<a href="' . get_author_posts_url(get_the_author_meta( 'ID' ), get_the_author_meta( 'user_nicename' )) . '">' . get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size ) . '</a>';
                          }
                        ?>
                      </div>
                      <div class="author-info-group">
                        <div class="author-date"><?php the_date(); ?></div>
                        <div class="author-name"><h4>
                          <?php
                            if ( function_exists( 'coauthors_posts_links' ) ) {
                                coauthors_posts_links();
                            } else {
                                the_author_posts_link();
                            }
                          ?>
                        </h4></div>
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
            $posts_displayed = 0;
            $the_query = cc_widgets_get_homepage_features_query('featured', 5);
            while ( $the_query->have_posts() ){
              $the_query->the_post();
              if (get_the_ID() == $hero_post_id){
                continue;
              } else {
                $posts_displayed++;
              }
              $featured_post_id = get_the_ID();
              if ( class_exists( 'coauthors_plus' ) ) { // Get the Co-Authors for the post
                $featured_authors = get_coauthors($featured_post_id);
              } else {
                $featured_authors = array();
              }
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
                          if (count($featured_authors)){
                            foreach ($featured_authors as $an_author){
                              echo '<a href="' . get_author_posts_url(get_the_author_meta( 'ID', $an_author->ID ), get_the_author_meta( 'user_nicename', $an_author->ID )) . '">' . get_avatar( get_the_author_meta( 'user_email', $an_author->ID ), $author_bio_avatar_size ) . '</a>';
                            }
                          } else {
                            echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size );
                          }
	                        ?>
	                      </div>
	                      <div class="author-info-group">
	                        <div class="author-date"><?php the_date(); ?></div>
                          <div class="author-name"><h4> <?php
                            if ( function_exists( 'coauthors_posts_links' ) ) {
                                coauthors_posts_links();
                            } else {
                                the_author_posts_link();
                            }
                          ?></h4></div>
	                      </div>
	                    </div>
	                    <div class="excerpt"><?php print the_excerpt(); ?></div>
	                    <div class="category"><?php print $category_link; ?></div>
										</div>
                  </div>
                </div>
                <?php if ($posts_displayed == 4) break; ?>
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
