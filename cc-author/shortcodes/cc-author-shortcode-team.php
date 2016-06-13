<?php

/*
 * Add a shortcode for cc-team listings
 *
 *
 *
 */

// [cc-team group="featured"]
function cc_team_listing_shortcode( $atts ) {
  $attributes    = shortcode_atts(array('group' => NULL, 'heading' => TRUE), $atts );
  $group_name    = $attributes['group'];
  $print_heading = $attributes['heading'];
  $output        = '';
  $users         = array();

  if ($group_name){
    $groups = get_terms( 'group', array(
      'hide_empty' => false,
    ));

    $the_group = NULL;

    foreach ($groups as $i => $group){
      if ($group->slug == $group_name){
        if ($group->count == 0){
          return NULL;
        } else {
          $the_group = $group;
          break;
        }
      }
    }
    if (! is_object($the_group)){
      return NULL;
    }

    // The 'WP_User_Query' arguments array
    $args = array(
      'meta_key'      => 'cc_group', // Is this the meta key you are using?
      'meta_value'    => $the_group->slug, // Based on however you store your meta data
      'meta_compare'  => 'LIKE',
    );
    // The User Query
    $user_query = new WP_User_Query( $args );

    // The User Loop
    if ( ! empty( $user_query->results ) ) {
        $output .= '<div class="cc-team-shortcode">';
        if ($print_heading){
          $output .= '<a name="' . $the_group->slug . '"></a> <h2 class="cc-team-heading">' . $the_group->name . '</h2>';
        }

        $output .= '<ul class="cc-team-listing">';

        foreach ( $user_query->results as $user ) {
          $users[$user->last_name . ', ' . $user->first_name] = $user;
        }
        ksort($users);

        foreach ($users as $user){

          $output .= '<li class="team-member">';
          $output .= '  <a class="user-profile" href="' . get_author_posts_url($user->ID) . '">';
          $output .= '  <div class="image">';
          $output .=      get_avatar($user->ID, '300');
          $output .= '  </div></a>';
          $output .= '  <div class="intro">';
          $output .= '  <a class="user-profile" href="' . get_author_posts_url($user->ID) . '"><div class="name">' . $user->first_name . ' ' . $user->last_name . '</div>';
          $output .= '  <div class="position">' . $user->cc_position . '</div></a>';
          $output .= '  <div class="description">' . wp_trim_words($user->description, 10) . ' <a class="more" href="' . get_author_posts_url($user->ID) . '">More</a></div>';
          $output .= '  <div class="social-links">';
          if ($user->twitter){
            $output .= '  <a href="https://twitter.com/' . $user->twitter . '"><span class="genericon genericon-twitter"></span></a>';
          }
          if (filter_var($user->facebook, FILTER_VALIDATE_URL)){
            $output .= '  <a href="' . $user->facebook . '"><span class="genericon genericon-facebook"></span></a>';
          }
          if (filter_var($user->cc_linkedin, FILTER_VALIDATE_URL)){
            $output .= '  <a href="' . $user->cc_linkedin . '"><span class="genericon genericon-linkedin"></span></a>';
          }
          /* if (filter_var($user->user_email, FILTER_VALIDATE_EMAIL)){
            $output .= '  <a href="mailto:' . $user->user_email . '"><span class="genericon genericon-mail"></span></a>';
          } */
          $output .= '  </div>';
          $output .= '</li>';

        }
        $output .= '</ul></div><br clear="all" />';
    }

    return $output;
  }
}

// [cc-blockquote image="url" quote="whatever" name="Adam Joe, CEO" ]
function cc_blockquote_shortcode( $atts ) {
  $attributes    = $atts;
  $image    = $attributes['image'];
  $quote = $attributes['quote'];
  $name = $attributes['name'];
  $url = $attributes['url'];

	$output = '';
  $output  .= '<blockquote class="cc-short-block">';
  if ($image) {
		$output .= '<div class="blockquote-image"><a href="'.$url.'"><img src="'.$image .'" alt="'.$name.'"></a></div>';
  }
	if($quote){
		$output .= '<div class="blockquote-text"><div class="blockquote-text-inner">'.$quote. '</div></div><div class="clear-all"></div>';
	}
	if($name){
		$output .= '<div class="blockqoute-user-title"><a href="'.$url.'">'.$name.'</a></div></blockquote>';
	}
  return $output;
}

// [cc-blockquote image="url" quote="whatever" name="Adam Joe, CEO" ]
function cc_choose_license_shortcode() {
	$output = '<div class="choose-license">';
	$output .= '<h2><a href="https://creativecommons.org/choose/"> Choose a license</a></h2><p>This chooser helps you determine which Creative Commons License is right for you in a few easy steps. If you are new to Creative Commons, you may also want to read <a href="/share-your-work/licensing-considerations/">Licensing Considerations</a> before you <a href="https://creativecommons.org/choose/" >get started.</a></p>';
  $output .= '<div class="image"><img src='.get_stylesheet_directory_uri ().'/images/choose_license.png /></div>';
  $output .=	'<a href="https://creativecommons.org/choose/" class="button choose-license-btn tertiary arrow">Get Started</a></div>';
	return $output;

}


add_shortcode( 'cc-choose-license', 'cc_choose_license_shortcode' );
add_shortcode( 'cc-team', 'cc_team_listing_shortcode' );
add_shortcode( 'cc-blockquote', 'cc_blockquote_shortcode' );