<?php

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents DrupalEasy Repositories from being uninstalled if data exists.
 *
 * If any Repository URL field values exist, do not allow the module to be
 * uninstalled.
 */
class DrupaleasyRepositoriesUninstallValidator implements ModuleUninstallValidatorInterface {
  use StringTranslationTrait;

  /**
   * Constructs a new DrupaleasyRepositoriesUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
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
    if ($module == 'drupaleasy_repositories') {
      if ($this->hasRepositoryUrlData()) {
        $reasons[] = $this->t('To uninstall DrupalEasy Repositories, delete all Repository URL values in user profiles.');
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
  protected function hasRepositoryUrlData() {
    $users = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('field_repository_url', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($users);
  }

}
