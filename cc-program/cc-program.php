<?php
/**
 * Plugin Name: Creative Commons Program
 * Plugin URI: http://creativecommons.org
 * Description: This plugin provides customizations for the Program post type.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 */


function cc_program_add_meta_boxes( $post ) {

  $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

  if ('page_program.php' == $page_template) {
    add_meta_box('cc-programs-left-metabox','Left Column','cc_program_page_template_left_metabox','page','normal','default');
    add_meta_box('cc-programs-right-metabox','Right Column','cc_program_page_template_right_metabox','page','normal','default');
    add_meta_box('cc-programs-get-involved-metabox','Get Involved','cc_program_page_template_get_involved_metabox','page','normal','default');
  }
}
// Make sure to use "_" instead of "-"
add_action( 'add_meta_boxes_page', 'cc_program_add_meta_boxes' );


function cc_program_page_template_left_metabox($post) {
  $cc_program_left =  $post->cc_program_left;
  wp_editor(htmlspecialchars_decode($cc_program_left), 'cc_program_left', array('textarea_name'=>'cc_program_left','media_buttons'=>true,'tinymce'=>true,'textarea_rows'=>10,'wpautop'=>false));
}

function cc_program_page_template_right_metabox($post) {
  $cc_program_right =  $post->cc_program_right;
  wp_editor(htmlspecialchars_decode($cc_program_right), 'cc_program_right', array('textarea_name'=>'cc_program_right','media_buttons'=>true,'tinymce'=>true,'textarea_rows'=>10,'wpautop'=>false));
}

function cc_program_page_template_get_involved_metabox($post) {
  $cc_program_get_involved =  $post->cc_program_get_involved;
  wp_editor(htmlspecialchars_decode($cc_program_get_involved), 'cc_program_get_involved', array('textarea_name'=>'cc_program_get_involved','media_buttons'=>true,'tinymce'=>true,'textarea_rows'=>10,'wpautop'=>false));
}

function cc_program_page_save_custom_post_meta($post_ID) {
  global $post;

  if (!is_null($post)) {
    $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

    if ( 'page_program.php' == $page_template ) {
      if (isset($_POST)) {
        update_post_meta($post_ID, 'cc_program_left', htmlspecialchars($_POST['cc_program_left']));
        update_post_meta($post_ID, 'cc_program_right', htmlspecialchars($_POST['cc_program_right']));
        update_post_meta($post_ID, 'cc_program_get_involved', htmlspecialchars($_POST['cc_program_get_involved']));
      }
    }
  }
}

add_action( 'save_post', 'cc_program_page_save_custom_post_meta' );
add_action( 'publish_page', 'cc_program_page_save_custom_post_meta' );
add_action( 'draft_page', 'cc_program_page_save_custom_post_meta' );
add_action( 'future_page', 'cc_program_page_save_custom_post_meta' );
