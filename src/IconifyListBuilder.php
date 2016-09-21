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
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
    $header['preview'] = $this->iconify('Preview');
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
    $preview = '';
    $icons = $entity->getIcons();
    foreach (array_rand(array_combine($icons, $icons), 12) as $selector) {
      $icon = ['#theme' => 'iconify_icon', '#icon' => $selector];
      $preview .= drupal_render($icon);
    }

    $row['label'] = $this->l(
      new TranslatableMarkup(' <strong>@label</strong> <small>(@machine)</small>', ['@label' => $entity->label(), '@machine' => $entity->id()]),
      new Url(
        'entity.iconify.canonical', array(
          'iconify' => $entity->id(),
        )
      )
    );
    $row['preview'] = ['data' => ['#markup' => $preview]];
    $status = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    $row['status'] = $this->iconify($status)->setIconOnly();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $render = parent::render();
    foreach ($this->load() as $iconify) {
      $render['#attached']['library'][] = 'iconify/iconify.' . $iconify->id();
    }
    return $render;
  }

}
