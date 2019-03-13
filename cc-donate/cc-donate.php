<?php
/**
 * Plugin Name: Creative Commons Donation
 * Plugin URI: http://creativecommons.org
 * Description: A plugin for donations on the Creative Commons site.
 * Version: 1.0.1
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 *
 * --------------------------------------------------------------------
 *
 * Creative Commons Donation - Customizations to the Gravityforms powered donation form.
 * Copyright (C) 2016, 2017 Creative Commons
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

define( 'CC_DONATE_VERSION', '20171011' );

add_filter( 'gform_form_settings', 'cc_donate_edit_form_setting', 10, 2 );
function cc_donate_edit_form_setting( $settings, $form ) {
    $settings['Form Button']['is_cc_donation_form'] = '
        <tr>
            <th><label for="is_cc_donation_form">CC Donation Form</label></th>
            <td><input type="checkbox" value="1" ' . (rgar($form, 'is_cc_donation_form') == 1 ? 'checked="checked"' : '') . ' name="is_cc_donation_form"> Apply donation form behaviours</td>
        </tr>';
    return $settings;
}

add_filter( 'gform_pre_form_settings_save', 'cc_donate_save_form_setting' );
function cc_donate_save_form_setting($form) {
    $form['is_cc_donation_form'] = rgpost( 'is_cc_donation_form' );
    return $form;
}


add_action( 'wp_enqueue_scripts', 'cc_donate_register_plugin_styles' );

/**
 * Register style sheet.
 */
function cc_donate_register_plugin_styles() {
  wp_enqueue_style( 'cc-donate', plugins_url( 'cc-donate/css/cc-donate.css' ), array(), CC_DONATE_VERSION );
  wp_enqueue_script('cc-donate-js', plugin_dir_url( __FILE__ ) . 'js/cc-donate.js', array('cc-common'), CC_DONATE_VERSION, true);
}

add_filter("gform_pre_render", "populate_previous_page_data");
function populate_previous_page_data($form){

  $is_donation_form = rgar($form, 'is_cc_donation_form');

  if ($is_donation_form == true){
    $pageNumber = rgpost("gform_target_page_number_{$form["id"]}");
    $html = NULL;


    // First loop and get the IDs of the fields we need.
    foreach($form['fields'] as $field) {
      switch ($field->adminLabel){
        case 'cc_amount':
          $field_amount_id = $field->id;
          break;
        case 'cc_frequency':
          $cc_frequency = $field->id;
          break;
        case 'cc_amount_onetime':
          $field_amount_onetime_id = $field->id;
          break;
        case 'cc_amount_monthly':
          $cc_amount_monthly_id = $field->id;
          break;
      }
    }

    foreach($form['fields'] as $field) {

      if($field->id == $field_amount_id){

        if (rgpost('input_' . $cc_frequency) == 'Monthly'){
          if (rgpost('input_' . $cc_amount_monthly_id) == 'gf_other_choice'){
            $value = rgpost('input_' . $cc_amount_monthly_id . '_other');
            $field['defaultValue'] = $value;
            $_POST['input_' . $field_amount_id] = $value;
            $html = '$' . htmlentities(number_format(floatval(ltrim($value, ' $')), 2)) . ' (monthly)';
          } else {
            $value = rgpost('input_' . $cc_amount_monthly_id);
            $field['defaultValue'] = $value;
            $_POST['input_' . $field_amount_id] = $value;
            $html = htmlentities($value) . ' (monthly)';
          }
         } elseif (rgpost('input_' . $cc_frequency) == 'One Time'){
          if (rgpost('input_' . $field_amount_onetime_id) == 'gf_other_choice'){
            $value = rgpost('input_' . $field_amount_onetime_id . '_other');
            $field['defaultValue'] = $value;
            $_POST['input_' . $field_amount_id] = $value;
            $html = '$' . htmlentities(number_format(floatval(ltrim($value, ' $')), 2)) . ' (one-time)';
          } else {
            $value = rgpost('input_' . $field_amount_onetime_id);
            $field['defaultValue'] = $value;
            $_POST['input_' . $field_amount_id] = $value;
            $html = htmlentities($value) . ' (one-time)';
          }
        }
      }
    }

    if ($html){
      foreach( $form['fields'] as &$field ) {
        //get html field
        if (stripos($field->content, 'cc_display_amount')) {
          //set the field content to the html
          $field->content = '<p>Donation Amount: ' . $html . '</p>';
        }
      }
    }
  }
  return $form;
}

/* --- --- */


add_filter( 'gform_field_validation', 'cc_donate_price_validation', 12, 4);
function cc_donate_price_validation( $result, $value, $form, $field ) {

  $is_donation_form = rgar($form, 'is_cc_donation_form');

  if (! $is_donation_form){
    return $result;
  }

  if ($field->adminLabel == 'cc_amount_monthly' || $field->adminLabel == 'cc_amount_onetime'){
    if (intval(ltrim($value, ' $')) < 1){
      $result['is_valid'] = false;
      $result['message']  = 'Donation amount must at least $1.';
    }
  }

  return $result;
}

add_filter( 'gform_field_validation', 'cc_donate_address_validation', 10, 4 );
function cc_donate_address_validation( $result, $value, $form, $field ) {

  $is_donation_form = rgar($form, 'is_cc_donation_form');

  if (! $is_donation_form){
    return $result;
  }

  if ($field->type != 'address'){
    return $result;
  }

  //address field will pass $value as an array with each of the elements as an item within the array, the key is the field id
  if ( ! $result['is_valid'] && $result['message'] == 'This field is required. Please enter a complete address.' ) {
    //address failed validation because of a required item not being filled out
    //do custom validation
    $street  = rgar( $value, $field->id . '.1' );
    $street2 = rgar( $value, $field->id . '.2' );
    $city    = rgar( $value, $field->id . '.3' );
    $state   = rgar( $value, $field->id . '.4' );
    $zip     = rgar( $value, $field->id . '.5' );
    $country = rgar( $value, $field->id . '.6' );

    if ($country == 'United States' || $country == 'Canada'){
      if ( empty( $street ) || empty( $city ) || empty( $state ) ) {
        $result['is_valid'] = false;
        $result['message']  = 'Address required. Please enter at least a street, city, and state/province.';
      } else {
        $result['is_valid'] = true;
        $result['message']  = '';
      }
    } else {
      if ( empty( $street ) || empty( $city ) ) {
        $result['is_valid'] = false;
        $result['message']  = 'Address required. Please enter at least a street and city.';
      } else {
        $result['is_valid'] = true;
        $result['message']  = '';
      }
    }
  }
  return $result;
}

/* -- Countries -- */
// Replace the country list with the list from CiviCRM.

add_filter( 'gform_countries', 'cc_donate_modify_countries' );
function cc_donate_modify_countries( $countries ){
  $civi_countries = array('Afghanistan', 'Åland Islands', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, The Democratic Republic of the', 'Cook Islands', 'Costa Rica', 'Côte d\'Ivoire', 'Croatia', 'Cuba', 'Curaçao', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island and McDonald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran, Islamic Republic of', 'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'s Republic of', 'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', 'Lao People\'s Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, Republic of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of', 'Moldova', 'Moldova, Republic of', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory, Occupied', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Serbia and Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania, United Republic of', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.S.', 'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe');
  return $civi_countries;
}



/* -- Homepage donate widget -- */
// Wrap the form (2) in a wrapper div.

add_filter( 'gform_get_form_filter_2', function ( $form_string, $form ) {
    return '<div class="widget-inner">' . $form_string . '</div>';
}, 10, 2 );

