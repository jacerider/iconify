<?php

/**
 * @file
 * Contains \Drupal\iconify\Entity\Iconify.
 */

namespace Drupal\iconify\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\iconify\IconifyInterface;
use Drupal\user\UserInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines the Iconify package entity.
 *
 * @ingroup iconify
 *
 * @ContentEntityType(
 *   id = "iconify",
 *   label = @Translation("Iconify package"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\iconify\IconifyListBuilder",
 *     "views_data" = "Drupal\iconify\Entity\IconifyViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\iconify\Form\IconifyForm",
 *       "add" = "Drupal\iconify\Form\IconifyForm",
 *       "edit" = "Drupal\iconify\Form\IconifyForm",
 *       "delete" = "Drupal\iconify\Form\IconifyDeleteForm",
 *     },
 *     "access" = "Drupal\iconify\IconifyAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\iconify\IconifyHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "iconify",
 *   admin_permission = "administer iconify package entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/media/iconify/{iconify}",
 *     "add-form" = "/admin/config/media/iconify/add",
 *     "edit-form" = "/admin/config/media/iconify/{iconify}/edit",
 *     "delete-form" = "/admin/config/media/iconify/{iconify}/delete",
 *     "collection" = "/admin/config/media/iconify",
 *   }
 * )
 */
class Iconify extends ContentEntityBase implements IconifyInterface {
  use EntityChangedTrait;

  protected $directory = 'public://iconify';
  protected $info = NULL;
  protected $icons = NULL;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    foreach ($entities as $entity) {
      $directory = 'public://iconify/' . $entity->id();
      file_unmanaged_delete_recursive($directory);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->get('label')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->set('label', $label);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * Return the location where Iconify packages exist.
   *
   * @return [string]
   */
  protected function getDirectory() {
    return $this->directory . '/' . $this->id();
  }

  /**
   * Return the stylesheet of the IcoMoon package if it exists.
   *
   * @return [string]
   */
  public function getStylesheet() {
    $path = $this->getDirectory() . '/style.css';
    return file_exists($path) ? $path : NULL;
  }

  /**
   * Get IcoMoon package information.
   *
   * @return [array]
   */
  public function getInfo() {
    if (is_null($this->info)) {
      $this->info = [];
      $path = $this->getDirectory() . '/selection.json';
      if (file_exists($path)) {
        $data = file_get_contents($path);
        $this->info = Json::decode($data);
      }
    }
    return $this->info;
  }

  /**
   * Get IcoMoon package icons.
   */
  public function getIcons() {
    if (is_null($this->icons) && $info = $this->getInfo()) {
      $this->icons = array();
      $prefix = $info['preferences']['fontPref']['prefix'];
      foreach ($info['icons'] as $icon) {
        foreach ($icon['icon']['tags'] as $tag) {
          $this->icons[$tag] = $prefix . $tag;
        }
      }
    }
    return $this->icons;
  }

  /**
   * Properly extract and store an IcoMoon zip file.
   *
   * @param [string] $zip_path
   *   The absolute path to the zip file.
   */
  public function setZipPackage($zip_path) {
    $archiver = archiver_get_archiver($zip_path);
    if (!$archiver) {
      throw new Exception(t('Cannot extract %file, not a valid archive.', array('%file' => $file)));
    }

    $directory = $this->getDirectory();
    file_unmanaged_delete_recursive($directory);
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $archiver->extract($directory);

    // Clean up
    file_unmanaged_delete_recursive($directory . '/demo-files');
    file_unmanaged_delete($directory . '/demo.html');
    file_unmanaged_delete($directory . '/Read Me.txt');
  }

  /**
   * Load all active Iconify packages.
   *
   * @return static[]
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadAll() {
    return Iconify::loadMultiple(Iconify::loadAllIds());
  }

  /**
   * Load all active Iconify IDs.
   *
   * @return static[]
   *   An array of entity IDs indexed by their IDs.
   */
  public static function loadAllIds() {
    $query = \Drupal::entityQuery('iconify');
    return $query->execute();
  }

  /**
   * Load all active Iconify packages.
   *
   * @return static[]
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadActive() {
    return Iconify::loadMultiple(Iconify::loadActiveIds());
  }

  /**
   * Load all active Iconify IDs.
   *
   * @return static[]
   *   An array of entity IDs indexed by their IDs.
   */
  public static function loadActiveIds() {
    $query = \Drupal::entityQuery('iconify')->condition('status', 1);
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Iconify package entity.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'max_length' => 64,
        'text_processing' => 0,
      ))
      ->addConstraint('UniqueField', []);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The label of this Iconify package.'))
      ->setRequired(TRUE)
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('view', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Iconify package entity.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Iconify package is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
