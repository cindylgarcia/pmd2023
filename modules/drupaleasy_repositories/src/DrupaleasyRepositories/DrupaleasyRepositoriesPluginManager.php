<?php

namespace Drupal\drupaleasy_repositories\DrupaleasyRepositories;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * DrupaleasyRepositories plugin manager.
 */
class DrupaleasyRepositoriesPluginManager extends DefaultPluginManager {

  /**
   * Constructs DrupaleasyRepositoriesPluginManager object.
   *
   * @param \Traversable<mixed> $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/DrupaleasyRepositories',
      $namespaces,
      $module_handler,
      'Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface',
      'Drupal\drupaleasy_repositories\Annotation\DrupaleasyRepositories'
    );
    $this->alterInfo('drupaleasy_repositories_info');
    $this->setCacheBackend($cache_backend, 'drupaleasy_repositories_plugins');
  }

}
