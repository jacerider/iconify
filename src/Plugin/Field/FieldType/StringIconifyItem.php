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
  public static function defaultStorageSettings() {
    return array(
      'packages' => [],
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();
    $iconifyManager = \Drupal::service('iconify.manager');

    $element['packages'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => $iconifyManager->getActivePackageLabels(),
      '#disabled' => $has_data,
    );

    return $element;
  }

}
