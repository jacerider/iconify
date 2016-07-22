<?php

/**
 * @file
 * Contains \Drupal\iconify\IconifyAccessControlHandler.
 */

namespace Drupal\iconify;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Iconify package entity.
 *
 * @see \Drupal\iconify\Entity\Iconify.
 */
class IconifyAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\iconify\IconifyInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished iconify package entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published iconify package entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit iconify package entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete iconify package entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add iconify package entities');
  }

}
