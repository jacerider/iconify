<?php

namespace Drupal\iconify\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Component\Serialization\Json;

/**
 * Defines the Iconify entity.
 *
 * @ConfigEntityType(
 *   id = "iconify",
 *   label = @Translation("Iconify"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\iconify\IconifyListBuilder",
 *     "form" = {
 *       "add" = "Drupal\iconify\Form\IconifyForm",
 *       "edit" = "Drupal\iconify\Form\IconifyForm",
 *       "delete" = "Drupal\iconify\Form\IconifyDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\iconify\IconifyHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "iconify",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/iconify/{iconify}",
 *     "add-form" = "/admin/structure/iconify/add",
 *     "edit-form" = "/admin/structure/iconify/{iconify}/edit",
 *     "delete-form" = "/admin/structure/iconify/{iconify}/delete",
 *     "collection" = "/admin/structure/iconify"
 *   }
 * )
 */
class Iconify extends ConfigEntityBase implements IconifyInterface {

  /**
   * The Iconify ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Iconify label.
   *
   * @var string
   */
  protected $label;

  /**
   * The info of this package.
   *
   * @var array
   */
  protected $info = [];

  /**
   * The available icons in this package.
   *
   * @var array
   */
  protected $icons = [];

  /**
   * The folder where Iconifys exist.
   */
  protected $directory = 'public://iconify';

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->get('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? 1 : 0);
    return $this;
  }

  /**
   * Set the archive as base64 encoded string.
   */
  public function setArchive($zip_path) {
    $data = strtr(base64_encode(addslashes(gzcompress(serialize(file_get_contents($zip_path)),9))), '+/=', '-_,');
    $parts = str_split($data, 200000);
    $this->set('archive', $parts);
  }

  /**
   * Get the archive from base64 encoded string.
   */
  public function getArchive() {
    $data = implode('', $this->get('archive'));
    return unserialize(gzuncompress(stripslashes(base64_decode(strtr($data, '-_,', '+/=')))));
  }

  /**
   * Return the location where Iconifys exist.
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
    if (empty($this->info)) {
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
    if (empty($this->icons) && $info = $this->getInfo()) {
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
   * Load all active Iconify packages.
   *
   * @return static[]
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadAll() {
    return Iconify::loadMultiple();
  }

  /**
   * Load all Iconify IDs.
   *
   * @return static[]
   *   An array of entity IDs indexed by their IDs.
   */
  public static function loadAllIds() {
    $query = \Drupal::entityQuery('iconify');
    return $query->execute();
  }

  /**
   * Load all Iconify packages.
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
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if (!$this->isNew()) {
      $original = $storage->loadUnchanged($this->getOriginalId());
    }

    if (!$this->get('archive')) {
      throw new EntityMalformedException('IcoMoon icon package is required.');
    }
    if ($this->isNew() || $original->get('archive') !== $this->get('archive')) {
      $this->archiveDecode();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    foreach ($entities as $entity) {
      file_unmanaged_delete_recursive($entity->getDirectory());
      // Clean up empty directory. Will fail silently if it is not empty.
      @rmdir($entity->directory);
    }
  }

  /**
   * Take base64 encoded archive and save it to a temporary file for extraction.
   */
  protected function archiveDecode() {
    $data = $this->getArchive();
    $zip_path = 'temporary://' . $this->id() . '.zip';
    file_put_contents($zip_path, $data);
    $this->archiveExtract($zip_path);
  }

  /**
   * Properly extract and store an IcoMoon zip file.
   *
   * @param [string] $zip_path
   *   The absolute path to the zip file.
   */
  public function archiveExtract($zip_path) {
    $archiver = archiver_get_archiver($zip_path);
    if (!$archiver) {
      throw new Exception(t('Cannot extract %file, not a valid archive.', array('%file' => $zip_path)));
    }

    $directory = $this->getDirectory();
    file_unmanaged_delete_recursive($directory);
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $archiver->extract($directory);

    // Clean up
    file_unmanaged_delete_recursive($directory . '/demo-files');
    file_unmanaged_delete($directory . '/demo.html');
    file_unmanaged_delete($directory . '/Read Me.txt');

    // The style.css file provided by IcoMoon contains query parameters where it
    // loads in the font files. Drupal CSS aggregation doesn't handle this will
    // so we need to remove it.
    $file_path = $directory . '/style.css';
    $file_contents = file_get_contents($file_path);
    $file_contents = preg_replace('(\?[a-zA-Z0-9#\-\_]*)', '', $file_contents);
    file_put_contents($file_path, $file_contents);

    drupal_set_message(t('iconifyIcon %name package has been successfully %op.', ['iconifyIcon' => '<i class="fa-drupal"></i>', '%name' => $this->label(), '%op' => ($this->isNew() ? t('added') : t('updated'))]));
  }

}
