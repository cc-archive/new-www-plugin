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
    $logo_id =  get_term_meta($term->term_id, 'cc_resource_logo', true);
        ?>
    <tr class="form-field term-group">
      <th scope="row">
        <label for="cc_resource_logo"><?php _e('Logo', 'cc-resource'); ?></label>
      </th>
      <td>
        <div>
          <?php if (is_numeric($logo_id)) echo wp_get_attachment_image($logo_id, 'cc_resource_logo'); ?>
        </div>

        <?php wp_nonce_field(basename( __FILE__ ), 'cc_resource_logo_nonce'); ?>
        <input type="file" id="cc_resource_logo" name="cc_resource_logo" value="" size="25" />
        <p class="description">Upload logo.</p>
      </td>
    </tr><?php
}

add_action( 'edited_cc_resource_platform', 'cc_resource_platform_update_meta_fields', 10, 2 );
function cc_resource_platform_update_meta_fields( $term_id, $tt_id ){
  $term = get_term($term_id);

  /* --- security verification --- */

  if(!wp_verify_nonce($_POST['cc_resource_logo_nonce'], basename( __FILE__ ))) {
    return $id;
  }// end if

  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $id;
  } // end if

  if (isset($_FILES['cc_resource_logo']) && ($_FILES['cc_resource_logo']['size'] > 0)) {
    // Get the type of the uploaded file. This is returned as "type/extension"
    $arr_file_type = wp_check_filetype(basename($_FILES['cc_resource_logo']['name']));
    $uploaded_file_type = $arr_file_type['type'];

    // We only allow PNG uploads here
    $allowed_file_types = array('image/png');

    if (in_array($uploaded_file_type, $allowed_file_types)) {
      // The uploaded image is a valid format...
      $uploaded_file = wp_handle_upload($_FILES['cc_resource_logo'], array('test_form' => false));
      if (isset($uploaded_file['file'])) {
        $file_path = $uploaded_file['file'];

        $attachment_data = array(
            'post_mime_type' => $uploaded_file_type,
            'post_title' => "Resource Platform: " . $term->name,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment_data, $file_path);
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attachment_meta = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id,  $attachment_meta);

        $existing_attachment_id = (int) get_term_meta($term_id, 'cc_resource_logo', true);
        if (is_numeric($existing_attachment_id)) {
            wp_delete_attachment($existing_attachment_id);
        }
        update_term_meta($term_id, 'cc_resource_logo', $attachment_id);
      } else {
        // wp_handle_upload returned some kind of error
        wp_die('There was an error uploading your file: ' . $upload['error']);
      }
    } else {
      wp_die("Please upload the platform logo in one of these formats: " . implode(' ', $allowed_file_types));
    }
  }
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
  $icon_id = get_term_meta($term->term_id, 'cc_resource_icon', true);
  $color =  get_term_meta($term->term_id, 'cc_resource_color', true);
      ?>
  <tr class="form-field term-group">
    <th scope="row">
      <label for="cc_resource_icon"><?php _e('Color', 'cc-resource'); ?></label>
    </th>
    <td>
      #<input type="text" id="cc_resource_color" name="cc_resource_color" size="6" maxlength="6" value="<?php print $color; ?>" size="25" />
      <p class="description">Hex color, for example "FC99D3".</p>
    </td>
  </tr>
  <tr class="form-field term-group">
    <th scope="row">
      <label for="cc_resource_icon"><?php _e('Icon', 'cc-resource'); ?></label>
    </th>
    <td>
      <div>
        <?php if (is_numeric($icon_id)) echo wp_get_attachment_image($icon_id, 'cc_resource_icon'); ?>
      </div>

      <?php wp_nonce_field(basename( __FILE__ ), 'cc_resource_icon_nonce'); ?>
      <input type="file" id="cc_resource_icon" name="cc_resource_icon" value="" size="25" />
      <p class="description">Upload icon.</p>
    </td>
  </tr><?php
}

add_action( 'edited_cc_resource_type', 'cc_resource_type_update_meta_fields', 10, 2 );
function cc_resource_type_update_meta_fields( $term_id, $tt_id ){
  $term = get_term($term_id);

  if (isset($_POST['cc_resource_color'])){
    update_term_meta($term_id, 'cc_resource_color', strtoupper(substr($_POST['cc_resource_color'], 0,6)));
  }

  /* --- security verification --- */
  if (!wp_verify_nonce($_POST['cc_resource_icon_nonce'], basename( __FILE__ ))) {
    return $id;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $id;
  }

  if (isset($_FILES['cc_resource_icon']) && ($_FILES['cc_resource_icon']['size'] > 0)) {
    // Get the type of the uploaded file. This is returned as "type/extension"
    $arr_file_type = wp_check_filetype(basename($_FILES['cc_resource_icon']['name']));
    $uploaded_file_type = $arr_file_type['type'];

    // We only allow PNG uploads here
    $allowed_file_types = array('image/png');

    if (in_array($uploaded_file_type, $allowed_file_types)) {
      // The uploaded image is a valid format...
      $uploaded_file = wp_handle_upload($_FILES['cc_resource_icon'], array('test_form' => false));
      if (isset($uploaded_file['file'])) {
        $file_path = $uploaded_file['file'];

        $attachment_data = array(
            'post_mime_type' => $uploaded_file_type,
            'post_title' => "Resource Type: " . $term->name,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment_data, $file_path);
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attachment_meta = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id,  $attachment_meta);

        $existing_attachment_id = (int) get_term_meta($term_id, 'cc_resource_icon', true);
        if (is_numeric($existing_attachment_id)) {
            wp_delete_attachment($existing_attachment_id);
        }
        update_term_meta($term_id, 'cc_resource_icon', $attachment_id);
      } else {
        // wp_handle_upload returned some kind of error
        wp_die('There was an error uploading your file: ' . $upload['error']);
      }
    } else {
      wp_die("Please upload the icon in one of these formats: " . implode(' ', $allowed_file_types));
    }
  }
}

//
// ------------- End Type ------------



/* Add URL and Attribution fields */

add_action( 'add_meta_boxes', 'cc_resource_meta' );
function cc_resource_meta() {
  add_meta_box('cc_resource_metabox_url', 'Resource URL', 'cc_resource_meta_url', 'resource', 'normal', 'high');
  add_meta_box('description', 'Caption', 'cc_resource_description', 'resource', 'normal', 'high');
  // Move the default postimagediv (Featured Image) to somewhere slightly more central
  add_meta_box('postimagediv', 'Image', 'post_thumbnail_meta_box', 'resource', 'side', 'high');
}

add_action( 'do_meta_boxes', 'cc_resource_remove_meta' );
function cc_resource_remove_meta() {
  // Remove some unnecessary meta boxes for Resources
  remove_meta_box('slugdiv', 'resource', 'normal');
  remove_meta_box('sharing_meta', 'resource', 'advanced');
  remove_meta_box('wpseo_meta', 'resource', 'normal');
}

function cc_resource_meta_url( $post ) {
  $cc_resource_meta_url = get_post_meta($post->ID, 'cc_resource_meta_url', true);
  echo '<input type="url" name="cc_resource_meta_url" class="large-text" value="' . esc_attr($cc_resource_meta_url) . '" />' . "\n";
}

function cc_resource_description( $post ) {
  $cc_resource_description =  $post->post_content;
  wp_editor(
    htmlspecialchars_decode($cc_resource_description),
    'content',
    array(
      'media_buttons' => false,
      'textarea_name' => 'content',
      'textarea_rows' => 8,
      'teeny' => true
    )
  );
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
  add_image_size( 'cc_resource_logo', 140, 40, false );
  add_image_size( 'cc_resource_icon', 50, 50, false );
  add_image_size( 'cc_large_tile', 350, 350, array('center', 'center') );
}

add_action( 'wp_ajax_get_resources', 'cc_ajax_get_resources' );
add_action( 'wp_ajax_nopriv_get_resources', 'cc_ajax_get_resources' );
function cc_ajax_get_resources() {
  $request_start = (int)$_GET['start'];
  $request_count = (int)$_GET['count'];

  if (!is_user_logged_in()) {
    // Tell anonymous users to cache these for one hour
    $expires = 60 * 60;
    $expires_date = gmdate('D, d M Y H:i:s', time() + $expires);
    header("Pragma: public");
    header("Cache-Control: maxage=$expires");
    header("Expires: $expires_date GMT");
  }

  $resources_obj = cc_get_resources($request_start, $request_count);
  wp_send_json($resources_obj);
}

function _attachment_image_attrs($attachment_id, $size) {
  if (!is_numeric($attachment_id)) return null;

  $attrs = array();

  $image_src = wp_get_attachment_image_src($attachment_id, $size);
  $image_srcset = wp_get_attachment_image_srcset($attachment_id, $size);
  $image_sizes = wp_get_attachment_image_sizes($attachment_id, $size);

  if ($image_src) {
    $attrs['src'] = $image_src[0];
    $attrs['width'] = $image_src[1];
    $attrs['height'] = $image_src[2];
  }

  if ($image_srcset) {
    $attrs['srcset'] = $image_srcset;
  }

  if ($image_srcset && $image_sizes) {
    $attrs['sizes'] = $image_sizes;
  }

  return $attrs;
}

function cc_get_resources($request_start, $request_count) {
  $resources = array();

  // Get all published resources. Note that this is different behaviour from
  // the default, where post_status varies depending on the current user and
  // whether this call is happening over AJAX.

  $the_query = new WP_Query(array(
    'post_type' => 'resource',
    'post_status' => 'publish',
    'offset' => $request_start,
    'posts_per_page' => $request_count
  ));
  $total_resources = $the_query->found_posts;
  while ($the_query->have_posts()): $the_query->the_post();
    $post = get_post(get_the_ID());

    $resource = array();

    $resource['id'] = $post->ID;

    $resource['url'] = get_post_meta($post->ID ,'cc_resource_meta_url', true);
    $resource['title'] = get_the_title($post);
    $resource['descriptionHtml'] = get_the_content();

    if (is_user_logged_in()) {
      $resource['editURL'] = get_edit_post_link($post->ID, null);
    }

    if (has_post_thumbnail()) {
      $attachment_id = get_post_thumbnail_id($post->ID);
      $resource['image'] = _attachment_image_attrs($attachment_id, 'cc_large_tile');

      $attachment = get_post($attachment_id);
      $resource['imageTitleHtml'] = $attachment->post_title;
      $resource['imageCaptionHtml'] = $attachment->post_excerpt;
      $resource['imageDescriptionHtml'] = $attachment->post_content;
    }

    $types = get_the_terms($post->ID ,'cc_resource_type');
    $type = is_array($types) ? array_pop($types) : NULL;

    if ($type) {
      $icon_id = get_term_meta($type->term_id, 'cc_resource_icon', true);
      $color = get_term_meta($type->term_id, 'cc_resource_color', true);

      $resource['type'] = $type->slug;
      $resource['typeName'] = $type->name;
      $resource['typeColor'] = $color;
      $resource['typeIcon'] = _attachment_image_attrs($icon_id, 'cc_resource_icon');
    }

    $platforms = get_the_terms($post->ID ,'cc_resource_platform');
    $platform = is_array($platforms) ? array_pop($platforms) : NULL;

    if ($platform) {
      $logo_id = get_term_meta($platform->term_id, 'cc_resource_logo', true);

      $resource['platform'] = $platform->slug;
      $resource['platformName'] = $platform->name;
      $resource['platformLogo'] = _attachment_image_attrs($logo_id, 'cc_resource_logo');
    }

    $resources[] = $resource;
  endwhile;

  return array(
    'resources' => $resources,
    'total' => $total_resources,
    'remaining' => $total_resources - ($request_start + $the_query->post_count)
  );
}
