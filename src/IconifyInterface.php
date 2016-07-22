<?php

/**
 * @file
 * Contains \Drupal\iconify\IconifyInterface.
 */

namespace Drupal\iconify;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Iconify package entities.
 *
 * @ingroup iconify
 */
interface IconifyInterface extends ContentEntityInterface, EntityChangedInterface {
  // Add get/set methods for your configuration properties here.
  /**
   * Gets the Iconify package label.
   *
   * @return string
   *   Label of the Iconify package.
   */
  public function getLabel();

  /**
   * Sets the Iconify package label.
   *
   * @param string $label
   *   The Iconify package label.
   *
   * @return \Drupal\iconify\IconifyInterface
   *   The called Iconify package entity.
   */
  public function setLabel($label);

  /**
   * Gets the Iconify package creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Iconify package.
   */
  public function getCreatedTime();

  /**
   * Sets the Iconify package creation timestamp.
   *
   * @param int $timestamp
   *   The Iconify package creation timestamp.
   *
   * @return \Drupal\iconify\IconifyInterface
   *   The called Iconify package entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Iconify package published status indicator.
   *
   * Unpublished Iconify package are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Iconify package is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Iconify package.
   *
   * @param bool $published
   *   TRUE to set this Iconify package to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\iconify\IconifyInterface
   *   The called Iconify package entity.
   */
  public function setPublished($published);

}
