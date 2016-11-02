<?php

namespace Drupal\iconify\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string_iconify' widget.
 *
 * @FieldWidget(
 *   id = "string_iconify",
 *   label = @Translation("Icon"),
 *   field_types = {
 *     "string_iconify"
 *   }
 * )
 */
class StringIconifyWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'packages' => [],
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $iconifyManager = \Drupal::service('iconify.manager');
    $element = parent::settingsForm($form, $form_state);

    $element['packages'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Icon Packages'),
      '#default_value' => $this->getSetting('packages'),
      '#description' => t('The icon packages that should be made available in this field. If no packages are selected, all will be made available.'),
      '#options' => $iconifyManager->getActivePackageLabels(),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#type' => 'iconify',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#packages' => $this->getSetting('packages'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $enabled_packages = array_filter($this->getSetting('packages'));
    if ($enabled_packages) {
      $packages = \Drupal::service('iconify.manager')->getActivePackageLabels();
      $enabled_packages = array_intersect_key($packages, $enabled_packages);
      $summary[] = $this->t('With icon packages: @packages', array('@packages' => implode(', ', $enabled_packages)));
    } else {
      $summary[] = $this->t('With icon packages: @packages', array('@packages' => 'All'));
    }
    return $summary;
  }

}
