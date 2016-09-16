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
   * Drupal\Core\Cache\ChainedFastBackend definition.
   *
   * @var \Drupal\Core\Cache\ChainedFastBackend
   */
  protected $cacheDiscovery;

  /**
   * Cached definitions array.
   *
   * @var array
   */
  protected $definitions;

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
   * Plugin definitions are cached using the provided cache backend. The
   * interface language is added as a suffix to the cache key.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param string $cache_key
   *   Cache key prefix to use, the language code will be appended
   *   automatically.
   * @param array $cache_tags
   *   (optional) When providing a list of cache tags, the cached plugin
   *   definitions are tagged with the provided cache tags. These cache tags can
   *   then be used to clear the corresponding cached plugin definitions. Note
   *   that this should be used with care! For clearing all cached plugin
   *   definitions of a plugin manager, call that plugin manager's
   *   clearCachedDefinitions() method. Only use cache tags when cached plugin
   *   definitions should be cleared along with other, related cache entries.
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
  public function getDefinitions() {
    $definitions = $this->getCachedDefinitions();
    if (!isset($definitions)) {
      $definitions = $this->findDefinitions();
      $this->setCachedDefinitions($definitions);
    }
    return $definitions;
  }

  public function getGroupedIcons() {
    $icons = [];
    foreach ($this->getDefinitions() as $iconify) {
      $icons[$iconify['id']] = $iconify['icons'];
    }
    return $icons;
  }

  public function getMergedIcons() {
    $icons = [];
    foreach ($this->getDefinitions() as $iconify) {
      $icons = array_merge($icons, array_values($iconify['icons']));
    }
    return $icons;
  }

  /**
   * Returns the cached plugin definitions of the decorated discovery class.
   *
   * @return array|null
   *   On success this will return an array of plugin definitions. On failure
   *   this should return NULL, indicating to other methods that this has not
   *   yet been defined. Success with no values should return as an empty array
   *   and would actually be returned by the getDefinitions() method.
   */
  protected function getCachedDefinitions() {
    if (!isset($this->definitions) && $cache = $this->cacheGet($this->cacheKey)) {
      $this->definitions = $cache->data;
    }
    return $this->definitions;
  }

  /**
   * Sets a cache of plugin definitions for the decorated discovery class.
   *
   * @param array $definitions
   *   List of definitions to store in cache.
   */
  protected function setCachedDefinitions($definitions) {
    $this->cacheSet($this->cacheKey, $definitions, Cache::PERMANENT, $this->cacheTags);
    $this->definitions = $definitions;
  }

  /**
   * Finds plugin definitions.
   *
   * @return array
   *   List of definitions to store in cache.
   */
  protected function findDefinitions() {
    $definitions = [];
    $storage = $this->entityManager->getStorage('iconify');
    foreach ($storage->loadMultiple() as $iconify) {
      if ($iconify->isPublished()) {
        $definitions[$iconify->id()] = [
          'id' => $iconify->id(),
          'label' => $iconify->label(),
          'icons' => $iconify->getIcons(),
        ];
      }
    }
    return $definitions;
  }

}
