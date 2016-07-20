<?php
/**
 * Plugin Name: Creative Commons Widgets
 * Plugin URI: http://creativecommons.org
 * Description: A plugin for widgets needed on the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 *
 * --------------------------------------------------------------------
 *
 * Creative Commons Widgets - Widgets for the Creative Commons site.
 * Copyright (C) 2016 Creative Commons
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
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

/*
 * function cc_widgets_get_featured_post_ids()
 *
 * Get the featured/hero post_ids so we can reduce repetitive post displays.
 *
 * return @array post_ids
 *
 */

function cc_widgets_get_featured_post_ids(){

  $all_widgets = wp_get_sidebars_widgets();
  $featured_post_ids = array();
  $suppress_featured_posts = FALSE;

  foreach ($all_widgets as $region => $widgets){
    if (is_array($widgets) && count($widgets)){
      foreach ($widgets as $i => $widget_title){
        if (strpos($widget_title, 'creativecommons_news_features_widget') !== FALSE){
          $suppress_featured_posts = TRUE;
          break;
        }
      }
    }
    if ($suppress_featured_posts == TRUE){
      break;
    }
  }

  if ($suppress_featured_posts == TRUE){
    $the_query = cc_widgets_get_homepage_features_query('hero', 1);
    if ( $the_query->have_posts() ){
      $posts = $the_query->get_posts();
      foreach ( $posts as $post ) {
        $featured_post_ids[] += $post->ID;
        $hero_post_id = $post->ID;
      }
    }
    $the_query = cc_widgets_get_homepage_features_query('featured', 5);
    if ( $the_query->have_posts() ){
      $posts = $the_query->get_posts();
      foreach ( $posts as $post ) {
        if ($post->ID == $hero_post_id){
          continue;
        }
        $featured_post_ids[] += $post->ID;
        if (count($featured_post_ids) == 5){
          break;
        }
      }
    }
    wp_reset_postdata();
  }

  return $featured_post_ids;
}



