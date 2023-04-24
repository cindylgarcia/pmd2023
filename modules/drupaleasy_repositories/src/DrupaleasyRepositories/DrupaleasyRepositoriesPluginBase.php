<?php

namespace Drupal\drupaleasy_repositories\DrupaleasyRepositories;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\key\KeyRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for drupaleasy_repositories plugins.
 */
abstract class DrupaleasyRepositoriesPluginBase extends PluginBase implements DrupaleasyRepositoriesInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * The repository client - this is used to connect with the remote API.
   *
   * @var Object
   */
  protected Object $client;

  /**
   * The Drupal core messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The Key repository service.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected KeyRepositoryInterface $keyRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('messenger'),
      $container->get('key.repository'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition, MessengerInterface $messenger, KeyRepositoryInterface $key_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
    $this->keyRepository = $key_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * Default implementation to validate repository URLs.
   *
   * @param string $uri
   *   The URI to validate.
   *
   * @return bool
   *   Returns FALSE is not valid.
   */
  public function validate(string $uri): bool {
    return FALSE;
  }

  /**
   * Build array of a single repository.
   *
   * @param string $machine_name
   *   The full name of the repository.
   * @param string $label
   *   The short name of the repository.
   * @param string|null $description
   *   The description of the repository.
   * @param int $num_open_issues
   *   The number of open issues.
   * @param string $url
   *   The URI of the repository.
   *
   * @return array<string, array<string, string>>
   *   An array containing info about a single repository.
   */
  protected function mapToCommonFormat(string $machine_name, string $label, string|null $description, int $num_open_issues, string $url): array {
    $repo_info[$machine_name] = [
      'label' => $label,
      'description' => $description,
      'num_open_issues' => $num_open_issues,
      'source' => $this->getPluginId(),
      'url' => $url,
    ];
    return $repo_info;
  }

}
