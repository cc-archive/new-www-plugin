<?php
/**
 * Plugin Name: Creative Commons Resources
 * Plugin URI: http://creativecommons.org
 * Description: This plugin provides a Resource post type to the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 *
 * --------------------------------------------------------------------
 *
 * Creative Commons Resources - The Resource post type for the Creative Commons site.
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

include_once ('widgets/cc-resource-homepage-widget.php');

add_action( 'init', 'cc_resource_cpt' );
add_action( 'init', 'cc_resource_taxonomy_platform', 0 );
add_action( 'init', 'cc_resource_taxonomy_type', 0 );

function cc_resource_cpt() {

  register_post_type('resource', array(
    'label' => __('Resource'),
    'labels' => array(
      'name' => 'Resources',
      'singular_name' => 'Resource',
      'add_new' => _x('Add New', 'Resource'),
      'add_new_item' => __('Add New Resource'),
      'edit_item' => __('Edit Resource'),
      'new_item' => __('New Resource'),
      'view_item' => __('View Resource'),
      'search_items' => __('Search Resources'),
      'not_found' =>  __('No Resources found'),
      'not_found_in_trash' => __('No Resources found in trash'),
      'parent_item_colon' => '',
     ),
    'description' => 'Resources in the commons.',
    'public' => true,
    'has_archive'         => true,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
    'can_export' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 20,
    'supports' => array('title', 'thumbnail'),
    'rewrite' => array('slug' => 'resource')
  ));


}

// ------------- Platform ------------
//

function cc_resource_taxonomy_platform() {

  $labels = array(
    'name'                       => _x( 'Platforms', 'Taxonomy General Name', 'text_domain' ),
    'singular_name'              => _x( 'Platform', 'Taxonomy Singular Name', 'text_domain' ),
    'menu_name'                  => __( 'Platform', 'text_domain' ),
    'all_items'                  => __( 'All Platforms', 'text_domain' ),
    'parent_item'                => __( 'Parent Platforms', 'text_domain' ),
    'parent_item_colon'          => __( 'Parent Platform:', 'text_domain' ),
    'new_item_name'              => __( 'New Platform Name', 'text_domain' ),
    'add_new_item'               => __( 'Add New Platform', 'text_domain' ),
    'edit_item'                  => __( 'Edit Platform', 'text_domain' ),
    'update_item'                => __( 'Update Platform', 'text_domain' ),
    'view_item'                  => __( 'View Platform', 'text_domain' ),
    'separate_items_with_commas' => __( 'Separate Platform with commas', 'text_domain' ),
    'add_or_remove_items'        => __( 'Add or remove Platforms', 'text_domain' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
    'popular_items'              => __( 'Popular Platforms', 'text_domain' ),
    'search_items'               => __( 'Search Platform', 'text_domain' ),
    'not_found'                  => __( 'Not Found', 'text_domain' ),
    'no_terms'                   => __( 'No Platform', 'text_domain' ),
    'items_list'                 => __( 'Platform list', 'text_domain' ),
    'items_list_navigation'      => __( 'Platform list navigation', 'text_domain' ),
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
  register_taxonomy( 'cc_resource_platform', array('resource'), $args );

//
// Attach logo to Platform
//

function cc_resource_add_post_enctype( ) {
    echo ' enctype="multipart/form-data"';
}
add_action( 'cc_resource_platform_term_edit_form_tag' , 'cc_resource_add_post_enctype' );

add_action( 'cc_resource_platform_edit_form_fields', 'cc_resource_platform_edit_meta_fields', 10, 2 );
function cc_resource_platform_edit_meta_fields($term) {
    $logo =  get_term_meta($term->term_id, 'cc_resource_logo');
        ?>
    <tr class="form-field term-group">
      <th scope="row">
        <label for="cc_resource_logo"><?php _e('Logo', 'cc-resource'); ?></label>
      </th>
      <td>
        <?php
          wp_nonce_field(basename( __FILE__ ), 'cc_resource_logo_nonce');
        ?>
        <?php if (is_array($logo[0]) && array_key_exists('url', $logo[0])): ?>
          <img src="<?php print $logo[0]['url']; ?>" style="max-width: 200px"><br />
        <?php endif; ?>

        <input type="file" id="cc_resource_logo" name="cc_resource_logo" value="" size="25" />
        <p class="description">Upload logo.</p>
      </td>
    </tr><?php
}

add_action( 'edited_cc_resource_platform', 'cc_resource_platform_update_meta_fields', 10, 2 );
function cc_resource_platform_update_meta_fields( $term_id, $tt_id ){

 /* --- security verification --- */
    if(!wp_verify_nonce($_POST['cc_resource_logo_nonce'], basename( __FILE__ ))) {
      return $id;
    }// end if

    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
    } // end if

    // Make sure the file array isn't empty
    if(!empty($_FILES['cc_resource_logo']['name'])) {
      // Setup the array of supported file types. In this case, it's just PDF.
      $supported_types = array('image/jpeg', 'image/png', 'image/gif');
      // Get the file type of the upload
      $arr_file_type = wp_check_filetype(basename($_FILES['cc_resource_logo']['name']));
      $uploaded_type = $arr_file_type['type'];
      // Check if the type is supported. If not, throw an error.
      if(in_array($uploaded_type, $supported_types)) {
        // Use the WordPress API to upload the file
        $upload = wp_upload_bits($_FILES['cc_resource_logo']['name'], null, file_get_contents($_FILES['cc_resource_logo']['tmp_name']));
        if(isset($upload['error']) && $upload['error'] != 0) {
          wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
        } else {
          delete_term_meta ($term_id, 'cc_resource_logo');
          add_term_meta($term_id, 'cc_resource_logo', $upload);
          update_term_meta($term_id, 'cc_resource_logo', $upload);
        } // end if/else
      } else {
          wp_die("The file type that you've uploaded is not valid.");
      } // end if/else
    } // end if
}

//
// ------------- End Platform ------------

// Register Custom Type Taxonomy
function cc_resource_taxonomy_type() {

  $labels = array(
    'name'                       => _x( 'Types', 'Taxonomy General Name', 'text_domain' ),
    'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', 'text_domain' ),
    'menu_name'                  => __( 'Type', 'text_domain' ),
    'all_items'                  => __( 'All Types', 'text_domain' ),
    'parent_item'                => __( 'Parent Types', 'text_domain' ),
    'parent_item_colon'          => __( 'Parent Type:', 'text_domain' ),
    'new_item_name'              => __( 'New Type Name', 'text_domain' ),
    'add_new_item'               => __( 'Add New Type', 'text_domain' ),
    'edit_item'                  => __( 'Edit Type', 'text_domain' ),
    'update_item'                => __( 'Update Type', 'text_domain' ),
    'view_item'                  => __( 'View Type', 'text_domain' ),
    'separate_items_with_commas' => __( 'Separate Type with commas', 'text_domain' ),
    'add_or_remove_items'        => __( 'Add or remove Types', 'text_domain' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
    'popular_items'              => __( 'Popular Types', 'text_domain' ),
    'search_items'               => __( 'Search Type', 'text_domain' ),
    'not_found'                  => __( 'Not Found', 'text_domain' ),
    'no_terms'                   => __( 'No Type', 'text_domain' ),
    'items_list'                 => __( 'Type list', 'text_domain' ),
    'items_list_navigation'      => __( 'Type list navigation', 'text_domain' ),
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
  register_taxonomy( 'cc_resource_type', array('resource'), $args );
}}


//
// Attach icon to Type
//

add_action( 'cc_resource_type_term_edit_form_tag' , 'cc_resource_add_post_enctype' );

add_action( 'cc_resource_type_edit_form_fields', 'cc_resource_type_edit_meta_fields', 10, 2 );
function cc_resource_type_edit_meta_fields($term) {
    $logo  =  get_term_meta($term->term_id, 'cc_resource_icon');
    $color =  get_term_meta($term->term_id, 'cc_resource_color');
        ?>
    <tr class="form-field term-group">
      <th scope="row">
        <label for="cc_resource_icon"><?php _e('Color', 'cc-resource'); ?></label>
      </th>
      <td>
        #<input type="text" id="cc_resource_color" name="cc_resource_color" size="6" maxlength="6" value="<?php print $color[0]; ?>" size="25" />
        <p class="description">Hex color, for example "FC99D3".</p>
      </td>
    </tr>
    <tr class="form-field term-group">
      <th scope="row">
        <label for="cc_resource_icon"><?php _e('Icon', 'cc-resource'); ?></label>
      </th>
      <td>
        <?php
          wp_nonce_field(basename( __FILE__ ), 'cc_resource_icon_nonce');
        ?>
        <?php if (is_array($logo[0]) && array_key_exists('url', $logo[0])): ?>
          <img src="<?php print $logo[0]['url']; ?>" style="max-width: 200px"><br />
        <?php endif; ?>

        <input type="file" id="cc_resource_icon" name="cc_resource_icon" value="" size="25" />
        <p class="description">Upload icon.</p>
      </td>
    </tr><?php
}

add_action( 'edited_cc_resource_type', 'cc_resource_type_update_meta_fields', 10, 2 );
function cc_resource_type_update_meta_fields( $term_id, $tt_id ){

  if (isset($_POST['cc_resource_color'])){
    update_term_meta($term_id, 'cc_resource_color', strtoupper(substr($_POST['cc_resource_color'], 0,6)));
  }

 /* --- security verification --- */
    if(!wp_verify_nonce($_POST['cc_resource_icon_nonce'], basename( __FILE__ ))) {
      return $id;
    }// end if

    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $id;
    } // end if

    // Make sure the file array isn't empty
    if(!empty($_FILES['cc_resource_icon']['name'])) {
      // Setup the array of supported file types. In this case, it's just PDF.
      $supported_types = array('image/jpeg', 'image/png', 'image/gif');
      // Get the file type of the upload
      $arr_file_type = wp_check_filetype(basename($_FILES['cc_resource_icon']['name']));
      $uploaded_type = $arr_file_type['type'];
      // Check if the type is supported. If not, throw an error.
      if(in_array($uploaded_type, $supported_types)) {
        // Use the WordPress API to upload the file
        $upload = wp_upload_bits($_FILES['cc_resource_icon']['name'], null, file_get_contents($_FILES['cc_resource_icon']['tmp_name']));
        if(isset($upload['error']) && $upload['error'] != 0) {
          wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
        } else {
          delete_term_meta ($term_id, 'cc_resource_icon');
          add_term_meta($term_id, 'cc_resource_icon', $upload);
          update_term_meta($term_id, 'cc_resource_icon', $upload);
        } // end if/else
      } else {
          wp_die("The file type that you've uploaded is not valid.");
      } // end if/else
    } // end if
}

//
// ------------- End Type ------------



/* Add URL and Attribution fields */

add_action( 'add_meta_boxes', 'cc_resource_meta' );
function cc_resource_meta() {
  add_meta_box('cc_resource_metabox_url', 'Resource URL', 'cc_resource_meta_url', 'resource', 'normal','high');
  add_meta_box('description', 'Description', 'cc_resource_description', 'resource', 'normal','high');
}

function cc_resource_meta_url( $post ) {
  $cc_resource_meta_url = get_post_meta($post->ID, 'cc_resource_meta_url', true);
  echo '<input type="url" name="cc_resource_meta_url" value="' . esc_attr($cc_resource_meta_url) . '" />' . "\n";
}

function cc_resource_description( $post ) {
  $cc_resource_description =  $post->post_content;
  wp_editor(htmlspecialchars_decode($cc_resource_description), 'content', array('textarea_name'=>'content'));
}

add_action( 'save_post', 'cc_resource_meta_save' );
function cc_resource_meta_save( $post_ID ) {
  global $post;
  if(!is_null($post) && $post->post_type == "resource") {
    if (isset($_POST)) {
      update_post_meta($post_ID, 'cc_resource_meta_url', strip_tags($_POST['cc_resource_meta_url']));
    }
  }
}

add_action( 'after_setup_theme', 'cc_resource_image_sizes' );
function cc_resource_image_sizes() {
  add_image_size( 'cc_large_tile', 350, 350, array('center', 'center') );

}

add_action( 'wp_ajax_get_resources', 'cc_ajax_get_resources' );
add_action( 'wp_ajax_nopriv_get_resources', 'cc_ajax_get_resources' );
function cc_ajax_get_resources() {
  $request_start = (int)$_POST['start'];
  $request_count = (int)$_POST['count'];

  $resources_obj = cc_get_resources($request_start, $request_count);
  wp_send_json($resources_obj);
}

function cc_get_resources($request_start, $request_count) {
  $resources = array();

  $the_query = new WP_Query(array(
    'post_type' => 'resource',
    'offset' => $request_start,
    'posts_per_page' => $request_count
  ));
  $total_resources = $the_query->found_posts;
  while ( $the_query->have_posts() ): $the_query->the_post();
    $post = get_post(get_the_ID());

    $resource = array();

    $resource['id'] = $post->ID;
    $resource['url'] = get_post_meta($post->ID ,'cc_resource_meta_url', true);

    $resource['title'] = get_the_title($post);
    $resource['descriptionHtml'] = get_the_content();

    if (has_post_thumbnail()) {
      $attachment_id = get_post_thumbnail_id($post->ID);
      $attachment = get_post($attachment_id);
      $image_attrs = wp_get_attachment_image_src($attachment_id, 'cc_large_tile');
      $resource['imageURL'] = $image_attrs[0];
      $resource['imageTitleHtml'] = $attachment->post_title;
      $resource['imageCaptionHtml'] = $attachment->post_excerpt;
      $resource['imageDescriptionHtml'] = $attachment->post_content;
    }

    $types = get_the_terms($post->ID ,'cc_resource_type');
    $type = is_array($types) ? array_pop($types) : NULL;

    if ($type) {
      $icon = get_term_meta($type->term_id, 'cc_resource_icon', true);
      $color = get_term_meta($type->term_id, 'cc_resource_color', true);

      $resource['type'] = $type->slug;
      $resource['typeName'] = $type->name;
      $resource['typeColor'] = $color;

      if (is_array($icon) && isset($icon['url'])) {
        $resource['typeIcon'] = $icon['url'];
      }
    }

    $platforms = get_the_terms($post->ID ,'cc_resource_platform');
    $platform = is_array($platforms) ? array_pop($platforms) : NULL;

    if ($platform) {
      $logo = get_term_meta($platform->term_id, 'cc_resource_logo', true);

      $resource['platform'] = $platform->slug;
      // TODO: Platform name

      if (is_array($logo) && isset($logo['url'])){
        $resource['platformIcon'] = $logo['url'];
      }
    }

    $resources[] = $resource;
  endwhile;

  return array(
    'resources' => $resources,
    'total' => $total_resources,
    'remaining' => $total_resources - ($request_start + $the_query->post_count)
  );
}
