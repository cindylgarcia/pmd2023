<?php

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents book module from being uninstalled under certain conditions.
 *
 * These conditions are when any book nodes exist or there are any book outline
 * stored.
 */
class DrupaleasyRepositoriesUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new DrupaleasyRepositoriesUninstallValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager, TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'book') {
      if ($this->hasRepositoryUrlData()) {
        // The book node type is provided by the Book module. Prevent uninstall
        // if there are any nodes of that type.
        $reasons[] = $this->t('To uninstall Drupaleasy Repositories, delete all Repository URL values from user accounts.');
      }
    }
    return $reasons;
  }

  /**
   * Determines if there is any Repository URL data or not.
   *
   * @return bool
   *   TRUE if there is Repository URL data, FALSE otherwise.
   */

  /**
   *
   */
  protected function hasRepositoryUrlData() {
    $users = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('field_repository_url', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($users);
  }

}
