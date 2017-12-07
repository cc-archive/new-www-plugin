<?php

class CreativeCommons_EOY_2017_Donation_Box_Widget extends WP_Widget {
  var $default_title, $default_size;

  /**
   * Registers the widget with WordPress.
   */
  function __construct() {
    parent::__construct(false, $name = __("CC 2017 EOY Donation Widget", 'creativecommons'));
    $this->default_category  = 'news';
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  function widget( $args, $instance ) {

    echo $args['before_widget'];

    ?>
<div class="cc-eoy-2017-donation-box-widget">
  <script src="/wp-content/themes/cc/js/yearend2017.js"></script>
  <div class="content-container">
      <div class="yellow-ribbon">
          <span class="support">Support the Movement</span><span class="message"><img src="/wp-content/themes/cc/images/yearend-takeover/accelerate.svg" width="124" alt="accelerate"> free and open access to knowledge!</span>
      </div>
      <div class="centre-box">
          <div class="person-box"><img class="person" src="/wp-content/themes/cc/images/yearend-takeover/CCYE_People_100/conversation_01_100.png" alt="a person"></div>
          <div class="motto-box">
              <div class="motto-wrapper">
                  <div class="accelerate"><img src="/wp-content/themes/cc/images/yearend-takeover/accelerate.svg" alt="accelerate"></div><span class="motto">free and open<br />access to knowledge.</span></div>
          </div>
      </div>

      <div class="mobile-donate-box">
        <form method='get' id='eoy-mobile-donate-box' action='/donate'>
          <div class="amount-input-wrapper">
            <span><input id="eoy-2017-donate-amount" class="amount-input" name="amount_other" type="text" value="Amount" aria-label="Amount" onfocus="if(jQuery(this).val() == &quot;Amount&quot;) { jQuery(this).val(&quot;&quot;); }" onblur="if(jQuery(this).val().replace(&quot; &quot;, &quot;&quot;) == &quot;&quot;) { jQuery(this).val(&quot;Amount&quot;); }" tabindex="0"></span>
          </div>
          <div>
            <input type="submit" class="donate-button" value="Donate Now">
          </div>
        </form>
      </div>

      <div class="donate-box" id="gform_widget-3">
          <div class='gform_wrapper' id='gform_wrapper_12'>
              <form method='get' id='gform_12' action='/donate'>
                  <div class='gform_body'>
                      <h2>
                        <span class="accelerate-yes"><span class="contribute-today">Yes! <span class="no-wrap">I want to be a</span> <span class="no-wrap">Creative Commons</span> <img src="/wp-content/themes/cc/images/yearend-takeover/accelerator.svg" alt="accelerator"></span></span>
                        <span class="message"><img src="/wp-content/themes/cc/images/yearend-takeover/accelerate.svg" width="124" alt="accelerate"> free and open access to knowledge.</span>
                      </h2>
                      <ul id="gform_fields_12" class="gform_fields top_label form_sublabel_below description_below">
                          <li id="field_12_1" class="gfield field_sublabel_below field_description_below gfield_visibility_visible">
                              <div class="ginput_container ginput_container_radio">
                                  <ul class="gfield_radio" id="input_12_1">
                                      <li class="gchoice_12_1_0">
                                          <input name="input_1" type="radio" value="$75" id="choice_0" tabindex="0">
                                          <label for="choice_0" id="label_12_1_0">$75 One time</label>
                                      </li>
                                      <li class="gchoice_12_1_1">
                                          <input name="input_1" type="radio" value="$5" checked="checked" id="choice_1" tabindex="0">
                                          <label for="choice_1" id="label_12_1_1">$5 Monthly</label>
                                      </li>
                                      <li class="gchoice_12_1_4">
                                          <input name="input_1" type="radio" value="gf_other_choice" id="choice_4" tabindex="0" onfocus="jQuery(this).next('input').focus();">
                                          <input id="input_other" name="input_1_other" type="text" value="Other amount" aria-label="Other amount" onfocus="jQuery(this).prev(&quot;input&quot;)[0].click(); if(jQuery(this).val() == &quot;Other amount&quot;) { jQuery(this).val(&quot;&quot;); }" onblur="if(jQuery(this).val().replace(&quot; &quot;, &quot;&quot;) == &quot;&quot;) { jQuery(this).val(&quot;Other amount&quot;); }" tabindex="0">
                                      </li>
                                  </ul>
                              </div>
                          </li>
                          <li class="donate-list-item">
                              <center>
                                  <input type="submit" id="gform_submit_button_2" class="gform_button button" value="Donate Now" tabindex="6" onclick="if(window[&quot;gf_submitting_2&quot;]){return false;}  window[&quot;gf_submitting_2&quot;]=true;  " onkeypress="if( event.keyCode == 13 ){ if(window[&quot;gf_submitting_2&quot;]){return false;} window[&quot;gf_submitting_2&quot;]=true;  jQuery(&quot;#gform_2&quot;).trigger(&quot;submit&quot;,[true]); }">
                              </center>
                          </li>
                      </ul>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>


    <?php

    echo $args['after_widget'];
  }
}

function cc_2017_eoy_donation_widget_init() {
  register_widget( 'CreativeCommons_EOY_2017_Donation_Box_Widget' );
}

add_action( 'widgets_init', 'cc_2017_eoy_donation_widget_init' );
