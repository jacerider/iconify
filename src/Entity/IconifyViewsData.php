<?php

/**
 * @file
 * Contains \Drupal\iconify\Entity\Iconify.
 */

namespace Drupal\iconify\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Iconify package entities.
 */
class IconifyViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['iconify']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Iconify package'),
      'help' => $this->t('The Iconify package ID.'),
    );

    return $data;
  }

}
