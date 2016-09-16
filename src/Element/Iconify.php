<?php

namespace Drupal\iconify\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;

/**
 * Provides a one-line text field form element.
 *
 * Properties:
 * - #maxlength: Maximum number of characters of input allowed.
 * - #size: The size of the input element in characters.
 * - #autocomplete_route_name: A route to be used as callback URL by the
 *   autocomplete JavaScript library.
 * - #autocomplete_route_parameters: An array of parameters to be used in
 *   conjunction with the route name.
 *
 * Usage example:
 * @code
 * $form['title'] = array(
 *   '#type' => 'iconify',
 *   '#title' => $this->t('Subject'),
 *   '#default_value' => $node->title,
 *   '#size' => 60,
 *   '#maxlength' => 128,
 * '#required' => TRUE,
 * );
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Color
 * @see \Drupal\Core\Render\Element\Email
 * @see \Drupal\Core\Render\Element\MachineName
 * @see \Drupal\Core\Render\Element\Number
 * @see \Drupal\Core\Render\Element\Password
 * @see \Drupal\Core\Render\Element\PasswordConfirm
 * @see \Drupal\Core\Render\Element\Range
 * @see \Drupal\Core\Render\Element\Tel
 * @see \Drupal\Core\Render\Element\Url
 *
 * @FormElement("iconify")
 */
class Iconify extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#input' => TRUE,
      '#process' => array(
        array($class, 'processSelect'),
        array($class, 'processAjaxForm'),
      ),
      '#pre_render' => array(
        array($class, 'preRenderSelect'),
      ),
      '#theme' => 'select',
      '#theme_wrappers' => array('form_element'),
    );
  }

  /**
   * Processes a select list form element.
   *
   * This process callback is mandatory for select fields, since all user agents
   * automatically preselect the first available option of single (non-multiple)
   * select lists.
   *
   * @param array $element
   *   The form element to process. Properties used:
   *   - #multiple: (optional) Indicates whether one or more options can be
   *     selected. Defaults to FALSE.
   *   - #default_value: Must be NULL or not set in case there is no value for the
   *     element yet, in which case a first default option is inserted by default.
   *     Whether this first option is a valid option depends on whether the field
   *     is #required or not.
   *   - #required: (optional) Whether the user needs to select an option (TRUE)
   *     or not (FALSE). Defaults to FALSE.
   *   - #empty_option: (optional) The label to show for the first default option.
   *     By default, the label is automatically set to "- Select -" for a required
   *     field and "- None -" for an optional field.
   *   - #empty_value: (optional) The value for the first default option, which is
   *     used to determine whether the user submitted a value or not.
   *     - If #required is TRUE, this defaults to '' (an empty string).
   *     - If #required is not TRUE and this value isn't set, then no extra option
   *       is added to the select control, leaving the control in a slightly
   *       illogical state, because there's no way for the user to select nothing,
   *       since all user agents automatically preselect the first available
   *       option. But people are used to this being the behavior of select
   *       controls.
   *       @todo Address the above issue in Drupal 8.
   *     - If #required is not TRUE and this value is set (most commonly to an
   *       empty string), then an extra option (see #empty_option above)
   *       representing a "non-selection" is added with this as its value.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   *
   * @see _form_validate()
   */
  public static function processSelect(&$element, FormStateInterface $form_state, &$complete_form) {
    // For proper validation we need to override the type as a select field.
    $element['#type'] = 'select';
    $element['#options'] = [];

    // If the element is set to #required through #states, override the
    // element's #required setting.
    $required = isset($element['#states']['required']) ? TRUE : $element['#required'];
    // If the element is required and there is no #default_value, then add an
    // empty option that will fail validation, so that the user is required to
    // make a choice. Also, if there's a value for #empty_value or
    // #empty_option, then add an option that represents emptiness.
    if (($required && !isset($element['#default_value'])) || isset($element['#empty_value']) || isset($element['#empty_option'])) {
      $element += array(
        '#empty_value' => '',
        '#empty_option' => $required ? t('- Select -') : t('- None -'),
      );
      // The empty option is prepended to #options and purposively not merged
      // to prevent another option in #options mistakenly using the same value
      // as #empty_value.
      $empty_option = array($element['#empty_value'] => $element['#empty_option']);
      $element['#options'] = $empty_option + $element['#options'];
    } else {
      $element['#options'][''] = t('- None -');
    }

    // Add icon packages as options.
    $iconifyDefinitions = \Drupal::service('iconify.manager')->getDefinitions();
    foreach ($iconifyDefinitions as $id => $data) {
      foreach ($data['icons'] as $class) {
        $element['#options'][$data['label']][$class] = $class;
      }
    }

    $element['#attached']['library'][] = 'iconify/iconify.element';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE) {
      if (isset($element['#empty_value']) && $input === (string) $element['#empty_value']) {
        return $element['#empty_value'];
      }
      else {
        return $input;
      }
    }
  }

  /**
   * Prepares a select render element.
   */
  public static function preRenderSelect($element) {
    Element::setAttributes($element, array('id', 'name', 'size'));
    static::setAttributes($element, array('form-iconify'));
    return $element;
  }

}
