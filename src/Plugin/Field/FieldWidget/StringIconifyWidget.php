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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#type' => 'iconify',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#packages' => $this->getFieldSetting('packages'),
    ];

    return $element;
  }

}
