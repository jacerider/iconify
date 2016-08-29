<?php

/**
 * @file
 * Contains \Drupal\iconify\IconifyListBuilder.
 */

namespace Drupal\iconify;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\iconify\IconifyIconizeTrait;

/**
 * Defines a class to build a listing of Iconify package entities.
 *
 * @ingroup iconify
 */
class IconifyListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  use IconifyIconizeTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->iconify('Label');
    $header['id'] = $this->iconify('ID');
    $header['status'] = $this->iconify('Status');
    $header = $header + parent::buildHeader();
    if (isset($header['operations'])) {
      $header['operations'] = $this->iconify($header['operations']);
    }
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\iconify\Entity\Iconify */
    $row['label'] = $this->l(
      $entity->label(),
      new Url(
        'entity.iconify.canonical', array(
          'iconify' => $entity->id(),
        )
      )
    );
    $row['id'] = $entity->id();
    $status = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    $row['status'] = $this->iconify($status)->setIconOnly();
    return $row + parent::buildRow($entity);
  }

}
