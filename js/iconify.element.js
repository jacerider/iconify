/**
 * @file toolbar.js
 *
 * Defines the behavior of the Drupal administration toolbar.
 */
(function ($) {

  'use strict';

  /**
   * Set up and bind Valet.
   */
  Drupal.behaviors.iconifyElement = {

    attach: function (context) {
      $('select.form-iconify').once().fontIconPicker();
    }
  };

}(jQuery));
