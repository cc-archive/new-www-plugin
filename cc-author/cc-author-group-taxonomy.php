<?php

/**
 * Registers the 'group' taxonomy for users.  This is a taxonomy for the 'user' object type rather than a
 * post being the object type.
 */
function cc_author_register_user_group_taxonomy() {

   register_taxonomy(
    'group',
    'user',
    array(
      'public' => true,
      'labels' => array(
        'name' => __( 'Groups' ),
        'singular_name' => __( 'Group' ),
        'menu_name' => __( 'Groups' ),
        'search_items' => __( 'Search Groups' ),
        'popular_items' => __( 'Popular Groups' ),
        'all_items' => __( 'All Groups' ),
        'edit_item' => __( 'Edit Group' ),
        'update_item' => __( 'Update Group' ),
        'add_new_item' => __( 'Add New Group' ),
        'new_item_name' => __( 'New Group Name' ),
        'separate_items_with_commas' => __( 'Separate groups with commas' ),
        'add_or_remove_items' => __( 'Add or remove groups' ),
        'choose_from_most_used' => __( 'Choose from the most popular groups' ),
      ),
      'rewrite' => array(
        'with_front' => true,
        'slug' => 'author/group' // Use 'author' (default WP user slug).
      ),
      'capabilities' => array(
        'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
        'edit_terms'   => 'edit_users',
        'delete_terms' => 'edit_users',
        'assign_terms' => 'read',
      ),
      'update_count_callback' => 'cc_author_update_group_count' // Use a custom function to update the count.
    )
  );
}

add_action( 'init', 'cc_author_register_user_group_taxonomy', 0 );


/**
 * Function for updating the 'group' taxonomy count.  What this does is update the count of a specific term
 * by the number of users that have been given the term.  We're not doing any checks for users specifically here.
 * We're just updating the count with no specifics for simplicity.
 *
 * See the _update_post_term_count() function in WordPress for more info.
 *
 * @param array $terms List of Term taxonomy IDs
 * @param object $taxonomy Current taxonomy object of terms
 */
function cc_author_update_group_count( $terms, $taxonomy ) {
  global $wpdb;

  foreach ( (array) $terms as $term ) {

    $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );

    do_action( 'edit_term_taxonomy', $term, $taxonomy );
    $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
    do_action( 'edited_term_taxonomy', $term, $taxonomy );
  }
}

/* Adds the taxonomy page in the admin. */
add_action( 'admin_menu', 'cc_author_group_admin_page' );

/**
 * Creates the admin page for the 'group' taxonomy under the 'Users' menu.  It works the same as any
 * other taxonomy page in the admin.  However, this is kind of hacky and is meant as a quick solution.  When
 * clicking on the menu item in the admin, WordPress' menu system thinks you're viewing something under 'Posts'
 * instead of 'Users'.  We really need WP core support for this.
 */
function cc_author_group_admin_page() {

  $tax = get_taxonomy( 'group' );

  add_users_page(
    esc_attr( $tax->labels->menu_name ),
    esc_attr( $tax->labels->menu_name ),
    $tax->cap->manage_terms,
    'edit-tags.php?taxonomy=' . $tax->name
  );
}


/* Create custom columns for the manage group page. */
add_filter( 'manage_edit-group_columns', 'cc_author_group_user_column' );

/**
 * Unsets the 'posts' column and adds a 'users' column on the manage group admin page.
 *
 * @param array $columns An array of columns to be shown in the manage terms table.
 */
function cc_author_group_user_column( $columns ) {

  unset( $columns['posts'] );

  $columns['users'] = __( 'Users' );

  return $columns;
}

/* Customize the output of the custom column on the manage groups page. */
add_action( 'manage_group_custom_column', 'cc_author_group_column', 10, 3 );

/**
 * Displays content for custom columns on the manage groups page in the admin.
 *
 * @param string $display WP just passes an empty string here.
 * @param string $column The name of the custom column.
 * @param int $term_id The ID of the term being displayed in the table.
 */
function cc_author_group_column( $display, $column, $term_id ) {

  if ( 'users' === $column ) {
    $term = get_term( $term_id, 'group' );
    echo $term->count;
  }
}

/**
 * Adds an additional settings section on the edit user/profile page in the admin.  This section allows users to
 * select groups from a checkbox of terms from the profession taxonomy.  This is just one example of
 * many ways this can be handled.
 *
 * @param object $user The user object currently being edited.
 */
function cc_author_group_select_groups_row( $user ) {

  $tax = get_taxonomy( 'group' );

  /* Make sure the user can assign terms of the profession taxonomy before proceeding. */
  if ( !current_user_can( $tax->cap->assign_terms ) )
    return;

  /* Get the terms of the 'profession' taxonomy. */
  $terms = get_terms( 'group', array( 'hide_empty' => false ) ); 
  ob_start();
  ?>
    <tr>
      <th><label for="cc_group"><?php _e( 'Groups' ); ?></label></th>

      <td><?php

      /* If there are any profession terms, loop through them and display checkboxes. */
      if ( !empty( $terms ) ) {

        foreach ( $terms as $term ) { ?>
          <input type="checkbox" name="cc_group[]" id="group-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, is_object_in_term( $user->ID, 'group', $term ) ); ?> /> <label for="group-<?php echo esc_attr( $term->slug ); ?>"><?php echo $term->name; ?></label> <br />
        <?php }
      }

      /* If there are no group terms, display a message. */
      else {
        _e( 'There are no groups available.' );
      }

      ?></td>
    </tr>
<?php

  return ob_get_clean();
}


/**
 * Saves the term selected on the edit user/profile page in the admin. This function is triggered when the page
 * is updated.  We just grab the posted data and use wp_set_object_terms() to save it.
 *
 * @param int $user_id The ID of the user to save the terms for.
 */
function cc_author_group_save_terms( $user_id ) {

  $tax = get_taxonomy( 'group' );

  /* Make sure the current user can edit the user and assign terms before proceeding. */
  if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) ){
    return false;
  }

  $terms = $_POST['cc_group'];

  /* Sets the terms for the user. */
  wp_set_object_terms( $user_id, $terms, 'group', false);

  clean_object_term_cache( $user_id, 'group' );
}
