<?php
/**
 * Plugin Name: Creative Commons Author
 * Plugin URI: http://creativecommons.org
 * Description: Customizations of the Author for the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 */

include_once 'cc-author-group-taxonomy.php';
include_once 'widgets/cc-author-news-widget.php';
include_once 'widgets/cc-author-navigation-sidebar-widget.php';
include_once 'shortcodes/cc-author-shortcode-team.php';


add_action( 'show_user_profile', 'cc_author_add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'cc_author_add_custom_user_profile_fields' );

add_action( 'personal_options_update',  'cc_author_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'cc_author_save_custom_user_profile_fields' );

add_action('bcn_after_fill', 'cc_author_customize_breadcrumb');
function cc_author_customize_breadcrumb($breadcrumb_trail) {
  global $wp_query;
  if ( is_author() ){
    $home = array_pop($breadcrumb_trail->breadcrumbs);

    $breadcrumb_trail->add(new bcn_breadcrumb('Team', NULL, array('about'), '/about/team'));
    $breadcrumb_trail->add(new bcn_breadcrumb('About CC', NULL, array('about'), '/about'));
    $breadcrumb_trail->add($home);
  }
}



function cc_author_get_author_news_query($author_id, $count) {
  $args = array(
    'author'        =>  $author_id,
    'orderby'       =>  'post_date',
    'order'         =>  'ASC',
    'posts_per_page' => $count
  );
  return new WP_Query( $args );
}



function cc_author_add_custom_user_profile_fields( $user ) {
?>
  <h3><?php _e('Extra Profile Information', 'creativecommons'); ?></h3>

  <table class="form-table">
    <tr>
      <th>
        <label for="cc_position"><?php _e('Position', 'creativecommons'); ?>
      </label></th>
      <td>
        <input type="text" name="cc_position" id="cc_position" value="<?php echo esc_attr( get_the_author_meta( 'cc_position', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('Job title', 'cc_position'); ?></p>
      </td>
    </tr>
    <?php print cc_author_group_select_groups_row($user); ?>
    <tr>
      <th>
        <label for="cc_location"><?php _e('Location', 'creativecommons'); ?>
      </label></th>
      <td>
        <input type="text" name="cc_location" id="cc_phone" value="<?php echo esc_attr( get_the_author_meta( 'cc_location', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('City, State/Province', 'cc_location'); ?></p>
      </td>
    </tr>
    <tr>
      <th>
        <label for="cc_since"><?php _e('Since', 'creativecommons'); ?>
      </label></th>
      <td>
        <input type="text" name="cc_since" id="cc_phone" value="<?php echo esc_attr( get_the_author_meta( 'cc_since', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('With Creative Commons since', 'cc_location'); ?></p>
      </td>
    </tr>
    <tr>
      <th>
        <label for="cc_twitter"><?php _e('Twitter Handle', 'creativecommons'); ?>
      </label></th>
      <td>
        @<input type="text" name="cc_twitter" id="cc_twitter" value="<?php echo esc_attr( get_the_author_meta( 'cc_twitter', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('Your twitter handle', 'cc_twitter'); ?></p>
      </td>
    </tr>
    <tr>
      <th>
        <label for="cc_linkedin"><?php _e('LinkedIn', 'creativecommons'); ?>
      </label></th>
      <td>
        <input type="text" name="cc_linkedin" id="cc_linkedin" value="<?php echo esc_attr( get_the_author_meta( 'cc_linkedin', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('A link to your LinkedIn public profile', 'cc_linkedin'); ?></p>
      </td>
    </tr>
    <tr>
      <th>
        <label for="cc_facebook"><?php _e('Facebook Profile', 'creativecommons'); ?>
      </label></th>
      <td>
        <input type="text" name="cc_facebook" id="cc_facebook" value="<?php echo esc_attr( get_the_author_meta( 'cc_facebook', $user->ID ) ); ?>" class="regular-text" /><br />
        <p class="description"><?php _e('Your facebook profile URL', 'cc_facebook'); ?></p>
      </td>
    </tr>
    <tr>
      <th>
        <label for="cc_biography"><?php _e('Full Biography', 'creativecommons'); ?>
      </label></th>
      <td>
        <?php wp_editor(htmlspecialchars_decode(get_the_author_meta( 'cc_biography', $user->ID )), 'cc_biography', array('textarea_name'=>'cc_biography')); ?>
      </td>
    </tr>
  </table>
<?php }

function cc_author_save_custom_user_profile_fields( $user_id ) {

  if ( !current_user_can( 'edit_user', $user_id ) ){
    return FALSE;
  }
  update_usermeta( $user_id, 'cc_position', $_POST['cc_position'] );
  update_usermeta( $user_id, 'cc_group', $_POST['cc_group'] );
  update_usermeta( $user_id, 'cc_twitter', $_POST['cc_twitter'] );
  update_usermeta( $user_id, 'cc_linkedin', $_POST['cc_linkedin'] );
  update_usermeta( $user_id, 'cc_facebook', $_POST['cc_facebook'] );
  update_usermeta( $user_id, 'cc_since', $_POST['cc_since'] );
  update_usermeta( $user_id, 'cc_location', $_POST['cc_location'] );
  update_usermeta( $user_id, 'cc_biography', htmlspecialchars($_POST['cc_biography']));

  cc_author_group_save_terms($user_id);

}
