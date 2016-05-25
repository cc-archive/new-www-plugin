<?php
/**
 * Plugin Name: Creative Commons Donation
 * Plugin URI: http://creativecommons.org
 * Description: A plugin for donations on the Creative Commons site.
 * Version: 1.0.0
 * Author: Creative Commons
 * Author URI: http://creativecommons.org
 * License: GPL2
 */


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
            $html = '$' . htmlentities(number_format(floatval($value), 2)) . ' (monthly)';
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
            $html = '$' . htmlentities(number_format(floatval($value), 2)) . ' (monthly)';
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

/* -- Homepage donate widget -- */
// Wrap the form (2) in a wrapper div.

add_filter( 'gform_get_form_filter_2', function ( $form_string, $form ) {
    return '<div class="widget-inner">' . $form_string . '</div>';
}, 10, 2 );

