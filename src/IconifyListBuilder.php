<?php

/**
 * @file
 * Contains \Drupal\iconify\IconifyListBuilder.
 */

namespace Drupal\iconify;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\iconify\Iconify;

/**
 * Defines a class to build a listing of Iconify package entities.
 *
 * @ingroup iconify
 */
class IconifyListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('ID');
    $header['status'] = $this->t('Published');
    return $header + parent::buildHeader();
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
    $row['status'] = Iconify::fromText($status)->setIconOnly();
    return $row + parent::buildRow($entity);
  }

}
