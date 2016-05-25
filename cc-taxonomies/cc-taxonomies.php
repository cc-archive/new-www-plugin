<?php
/**
 * Plugin Name: Creative Commons Taxonomies
 * Plugin URI: http://creativecommons.org
 * Description: A plugin for taxonomies needed on the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 */

add_action( 'init', 'cc_taxonomy_highlight', 0 );

// Register Custom Taxonomy
function cc_taxonomy_highlight() {

  $labels = array(
    'name'                       => _x( 'Highlights', 'Taxonomy General Name', 'text_domain' ),
    'singular_name'              => _x( 'Highlight', 'Taxonomy Singular Name', 'text_domain' ),
    'menu_name'                  => __( 'Highlight', 'text_domain' ),
    'all_items'                  => __( 'All Highlights', 'text_domain' ),
    'parent_item'                => __( 'Parent Highlights', 'text_domain' ),
    'parent_item_colon'          => __( 'Parent Highlight:', 'text_domain' ),
    'new_item_name'              => __( 'New Highlight Name', 'text_domain' ),
    'add_new_item'               => __( 'Add New Highlight', 'text_domain' ),
    'edit_item'                  => __( 'Edit Highlight', 'text_domain' ),
    'update_item'                => __( 'Update Highlight', 'text_domain' ),
    'view_item'                  => __( 'View Highlight', 'text_domain' ),
    'separate_items_with_commas' => __( 'Separate Highlight with commas', 'text_domain' ),
    'add_or_remove_items'        => __( 'Add or remove Highlights', 'text_domain' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
    'popular_items'              => __( 'Popular Highlight', 'text_domain' ),
    'search_items'               => __( 'Search Highlight', 'text_domain' ),
    'not_found'                  => __( 'Not Found', 'text_domain' ),
    'no_terms'                   => __( 'No Highlight', 'text_domain' ),
    'items_list'                 => __( 'Highlight list', 'text_domain' ),
    'items_list_navigation'      => __( 'Highlight list navigation', 'text_domain' ),
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => false,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
  );
  register_taxonomy('cc_highlight', array('post'), $args);
}

function cc_add_taxonomies_to_pages() {
 register_taxonomy_for_object_type( 'category', 'page' );
 }
add_action( 'init', 'cc_add_taxonomies_to_pages' );