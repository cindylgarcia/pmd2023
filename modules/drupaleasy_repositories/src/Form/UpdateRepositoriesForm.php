<?php

namespace Drupal\drupaleasy_repositories\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a DrupalEasy Repositories form.
 */
class UpdateRepositoriesForm extends FormBase {

  /**
   * The DrupalEasy repositories service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $repositoriesService;

  /**
   * The DrupalEasy repositories batch service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch
   */
  protected DrupaleasyRepositoriesBatch $drupaleasyRepositoriesBatch;

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): UpdateRepositoriesForm {
    return new static(
      $container->get('drupaleasy_repositories.service'),
      $container->get('drupaleasy_repositories.batch'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Class constructor.
   */
  public function __construct(DrupaleasyRepositoriesService $repositories_service, DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch, EntityTypeManagerInterface $entity_type_manager) {
    $this->repositoriesService = $repositories_service;
    $this->drupaleasyRepositoriesBatch = $drupaleasy_repositories_batch;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupaleasy_repositories_update_repositories';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['uid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Username'),
      '#description' => $this->t('Leave blank to update all repository nodes for all users.'),
      '#required' => FALSE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (!is_null($form_state->getValue('uid')) && ($form_state->getValue('uid') == 0)) {
      $form_state->setErrorByName('uid', $this->t('You may not select the Anonymous user.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($uid = $form_state->getValue('uid')) {
      /** @var \Drupal\user\UserStorageInterface $user_storage */
      $user_storage = $this->entityTypeManager->getStorage('user');
      $account = $user_storage->load($uid);
      if ($account) {
        if ($this->repositoriesService->updateRepositories($account)) {
          $this->messenger()->addMessage($this->t('Repositories updated.'));
        }
      }
      else {
        $this->messenger()->addMessage($this->t('User does not exist.'));
      }
    }
    else {
      $this->drupaleasyRepositoriesBatch->updateAllUserRepositories();
    }
  }

}
