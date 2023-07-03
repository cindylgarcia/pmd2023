<?php

namespace Drupal\drupaleasy_repositories\Commands;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drush\Commands\DrushCommands;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch;
use Drush\Attributes as CLI;

/**
 * DrupalEasy Repositories custom Drush commandfile.
 *
 * Provides command to updated Respository nodes.
 */
class DrupaleasyRepositoriesCommands extends DrushCommands {

  /**
   * The DrupalEasy repositories service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $repositoriesService;

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The DrupalEasy repositories batch service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch
   */
  protected DrupaleasyRepositoriesBatch $drupaleasyRepositoriesBatch;

  /**
   * Cache invalidator interface.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected CacheTagsInvalidatorInterface $cacheInvalidator;

  /**
   * Constructs a DrupaleasyRepositories object.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositories_service
   *   The DrupalEasyRepositories service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity_type.manager service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch
   *   The DrupalEasy repositories batch service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_invalidator
   *   The cache invalidator service.
   */
  public function __construct(DrupaleasyRepositoriesService $repositories_service, EntityTypeManagerInterface $entity_type_manager, DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch, CacheTagsInvalidatorInterface $cache_invalidator) {
    parent::__construct();
    $this->repositoriesService = $repositories_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->drupaleasyRepositoriesBatch = $drupaleasy_repositories_batch;
    $this->cacheInvalidator = $cache_invalidator;
  }

  /**
 *
 */
  #[CLI\Command(name: 'der:update-repositories', aliases: ['der:ur'])]
  #[CLI\Option(name: 'uid', description: 'The User ID of the user whose repositories to update.')]
  #[CLI\Help(description: 'Update user repositories.', synopsis: 'This command will update all user repositories or all repositories for a single user.')]
  #[CLI\Usage(name: 'der:update-repositories --uid=2', description: 'Update a single user\'s repositories.')]
  #[CLI\Usage(name: 'der:update-repositories', description: 'Update all user repositories.')]
  public function updateRepositories(array $options = ['uid' => NULL]): void {
    if (!empty($options['uid'])) {
      /** @var \Drupal\user\UserStorageInterface $user_storage */
      $user_storage = $this->entityTypeManager->getStorage('user');
      $account = $user_storage->load($options['uid']);
      if ($account) {
        if ($this->repositoriesService->updateRepositories($account)) {
          $this->logger()->notice(dt('Repositories updated.'));
          // $this->output()->
        }
      }
      else {
        $this->logger()->critical(dt('User does not exist.'));
      }
    }
    else {
      if (!is_null($options['uid'])) {
        $this->logger()->critical(dt('You may not use the Anonymous user.'));
        return;
      }
      $this->drupaleasyRepositoriesBatch->updateAllUserRepositories(TRUE);
    }

    // Invalidate the cache for anything cache item that is tagged with
    // drupaleasy_repositories.
    $this->cacheInvalidator->invalidateTags(['drupaleasy_repositories']);
  }

}
