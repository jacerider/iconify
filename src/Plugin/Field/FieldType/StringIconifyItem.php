<?php

namespace Drupal\iconify\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string_iconify' field type.
 *
 * @FieldType(
 *   id = "string_iconify",
 *   label = @Translation("Icon"),
 *   description = @Translation("A field containing an icon."),
 *   default_widget = "string_iconify",
 *   default_formatter = "string_iconify"
 * )
 */
class StringIconifyItem extends StringItem {

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();
    return $element;
  }

}
