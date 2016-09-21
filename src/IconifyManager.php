<?php

namespace Drupal\iconify;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\UseCacheBackendTrait;
use Drupal\Core\Cache\Cache;

/**
 * Class IconifyManager.
 *
 * @package Drupal\iconify
 */
class IconifyManager implements IconifyManagerInterface {

  use UseCacheBackendTrait;

  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * Cached packages array.
   *
   * @var array
   */
  protected $packages;

  /**
   * Constructor.
   */
  public function __construct(EntityManager $entity_manager, CacheBackendInterface $cache_backend) {
    $this->entityManager = $entity_manager;
    $this->setCacheBackend($cache_backend, 'iconify.icons', array('iconify.icons'));
  }

  /**
   * Initialize the cache backend.
   *
   * Plugin packages are cached using the provided cache backend. The
   * interface language is added as a suffix to the cache key.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param string $cache_key
   *   Cache key prefix to use, the language code will be appended
   *   automatically.
   * @param array $cache_tags
   *   (optional) When providing a list of cache tags, the cached Iconify
   *   packages are tagged with the provided cache tags. These cache tags can
   *   then be used to clear the corresponding cached Iconify packages. Note
   *   that this should be used with care! For clearing all cached Iconify
   *   packages of a Iconify manager, call that Iconify manager's
   *   clearCachedDefinitions() method. Only use cache tags when cached Iconify
   *   packages should be cleared along with other, related cache entries.
   */
  public function setCacheBackend(CacheBackendInterface $cache_backend, $cache_key, array $cache_tags = array()) {
    assert('\Drupal\Component\Assertion\Inspector::assertAllStrings($cache_tags)', 'Cache Tags must be strings.');
    $this->cacheBackend = $cache_backend;
    $this->cacheKey = $cache_key;
    $this->cacheTags = $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackages() {
    $packages = $this->getCachedDefinitions();
    if (!isset($packages)) {
      $packages = $this->loadPackages();
      $this->setCachedPackages($packages);
    }
    return $packages;
  }

  /**
   * {@inheritdoc}
   */
  public function getActivePackages() {
    return array_filter($this->getPackages(), function($package) {
      return $package['status'] == 1;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getActivePackageLabels() {
    return array_map(function($package){
      return $package['label'];
    }, $this->getActivePackages());
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupedIcons() {
    $icons = [];
    foreach ($this->getPackages() as $iconify) {
      $icons[$iconify['id']] = $iconify['icons'];
    }
    return $icons;
  }

  /**
   * {@inheritdoc}
   */
  public function getMergedIcons() {
    $icons = [];
    foreach ($this->getPackages() as $iconify) {
      $icons = array_merge($icons, array_values($iconify['icons']));
    }
    return $icons;
  }

  /**
   * Returns the cached Iconify packages.
   *
   * @return array|null
   *   On success this will return an array of Iconify packages. On failure
   *   this should return NULL, indicating to other methods that this has not
   *   yet been defined. Success with no values should return as an empty array
   *   and would actually be returned by the getPackages() method.
   */
  protected function getCachedDefinitions() {
    if (!isset($this->packages) && $cache = $this->cacheGet($this->cacheKey)) {
      $this->packages = $cache->data;
    }
    return $this->packages;
  }

  /**
   * Sets a cache of Iconify packages.
   *
   * @param array $packages
   *   List of packages to store in cache.
   */
  protected function setCachedPackages($packages) {
    $this->cacheSet($this->cacheKey, $packages, Cache::PERMANENT, $this->cacheTags);
    $this->packages = $packages;
  }

  /**
   * Load iconify packages..
   *
   * @return array
   *   List of packages to store in cache.
   */
  protected function loadPackages() {
    $definitions = [];
    $storage = $this->entityManager->getStorage('iconify');
    foreach ($storage->loadMultiple() as $iconify) {
      $definitions[$iconify->id()] = [
        'id' => $iconify->id(),
        'label' => $iconify->label(),
        'icons' => $iconify->getIcons(),
        'status' => $iconify->isPublished(),
      ];
    }
    return $definitions;
  }

}
