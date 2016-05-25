<?php
/**
 * Plugin Name: Creative Commons Widgets
 * Plugin URI: http://creativecommons.org
 * Description: A plugin for widgets needed on the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 */

include_once ('widgets/cc-homepage-whatshappening-widget.php');
include_once ('widgets/cc-news-features-widget.php');
include_once ('widgets/cc-related-news-widget.php');

function cc_widgets_image_sizes() {
  add_image_size( 'cc_feature_thumbnail', 270, 155, false );
  add_image_size( 'cc_list_post_thumbnail', 440, 250, array( 'right', 'bottom' ) );
	

}
add_action( 'after_setup_theme', 'cc_widgets_image_sizes' );


function cc_widgets_get_homepage_features_query($term, $count) {
  $args = array(
    'post_type'       => 'post',
    'posts_per_page'  => $count,
    'tax_query'       => array(
      array(
        'taxonomy'    => 'cc_highlight',
        'field'       => 'slug',
        'terms'       => array($term)
      )
    )
  );
  return new WP_Query( $args );
}

function cc_widgets_get_related_news_query($term, $count) {
  $args = array(
    'post_type'       => 'post',
    'posts_per_page'  => $count,
    'tax_query'       => array(
      array(
        'taxonomy'    => 'category',
        'field'       => 'slug',
        'terms'       => array($term)
      )
    )
  );
  return new WP_Query( $args );
}