<?php

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DruaplEasy Repositories Batch service class to integrate with Batch API.
 */
class DrupaleasyRepositoriesBatch {
  use StringTranslationTrait;

  /**
   * The drupaleasy_repositories.service service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $extensionListModule;

  /**
   * Constructs a DrupaleasyRepositoriesBatch object.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $drupaleasy_repositories_service
   *   The drupaleasy_repositories.service service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   */
  public function __construct(DrupaleasyRepositoriesService $drupaleasy_repositories_service, EntityTypeManagerInterface $entity_type_manager, ModuleExtensionList $extension_list_module) {
    $this->drupaleasyRepositoriesService = $drupaleasy_repositories_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->extensionListModule = $extension_list_module;
  }

  /**
   * Updates all user repositories using the Batch API.
   */
  public function updateAllUserRepositories(): void {
    $operations = [];

    // Get all active users.
    $user_storage = $this->entityTypeManager->getStorage('user');
    $query = $user_storage->getQuery();
    $query->condition('status', '1');
    $users = $query->accessCheck(FALSE)->execute();

    // Create a Batch API item for each user.
    foreach ($users as $uid => $user) {
      $operations[] = ['drupaleasy_update_repositories_batch_operation', [$uid]];
    }
    $batch = [
      'operations' => $operations,
      'finished' => 'drupaleasy_update_all_repositories_finished',
      'file' => $this->extensionListModule->getPath('drupaleasy_repositories') . '/drupaleasy_repositories.batch.inc',
    ];

    // Submit the batch for processing.
    batch_set($batch);
  }

  /**
   * Batch process callback from updating user repositories.
   *
   * @param int $uid
   *   User ID to update.
   * @param array|\ArrayAccess $context
   *   Context for operations. We do not want to type hint this as an array or
   *   an object as sometimes it is an array (when calling from a form) and
   *   sometimes it is an object (when calling from Drush).
   */
  public function updateRepositoriesBatch(int $uid, array|\ArrayAccess &$context): void {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $account = $user_storage->load($uid);
    $this->drupaleasyRepositoriesService->updateRepositories($account);
    $context['results'][] = $uid;
    $context['results']['num']++;
    $context['message'] = $this->t('Updated repositories belonging to "@username".',
      ['@username' => $account->label()]
    );
  }

}
