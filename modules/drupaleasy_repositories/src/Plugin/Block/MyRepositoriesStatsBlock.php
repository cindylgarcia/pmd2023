<?php

namespace Drupal\drupaleasy_repositories\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a "my repositories stats" block.
 *
 * @Block(
 *   id = "drupaleasy_repositories_my_repositories_stats",
 *   admin_label = @Translation("My repositories stats"),
 *   category = @Translation("DrupalEasy")
 * )
 */
class MyRepositoriesStatsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $account;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Constructs a new MyRepositoriesStatsBlock instance.
   *
   * @param array<int, mixed> $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): MyRepositoriesStatsBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['content'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [
        $this->t('Current user: @name', ['@name' => $this->account->getAccountName()]),
        $this->t('Current timestamp: @timestamp', ['@timestamp' => $this->time->getCurrentTime()]),
        $this->t('Total number of issues in all repository nodes: @all', ['@all' => $this->calculateTotalIssues()]),
        $this->t('Total number of issues in my repository nodes: @my', ['@my' => $this->calculateTotalIssues($this->account->id())]),
      ],
    ];

    $build['#cache'] = [
      'max-age' => Cache::PERMANENT,
      'tags' => ['node_list:repository', 'drupaleasy_repositories'],
      'contexts' => ['user.roles'],
    ];
    return $build;
  }

  /**
   * Calculates the total number of issues for a user's repositories.
   *
   * @param int|null $uid
   *   An (optional) user to filter on.
   *
   * @return int
   *   The total number of issues.
   */
  protected function calculateTotalIssues(int $uid = NULL): int {
    $return = 0;
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'repository')
      ->condition('status', 1);
    if (!is_null($uid)) {
      $query->condition('uid', $uid);
    }
    $results = $query->accessCheck(FALSE)->execute();

    foreach ($results as $nid) {
      /** @var \Drupal\node\Entity\Node $node */
      $node = $node_storage->load($nid);
      if ($number_of_issues = $node->field_number_of_issues->value) {
        $return += $number_of_issues;
      }
    }

    return $return;
  }

}
