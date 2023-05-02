<?php

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;
use Gitlab\Client;

/**
 * Plugin implementation of the drupaleasy_repositories.
 *
 * @DrupaleasyRepositories(
 *   id = "gitlab",
 *   label = @Translation("Gitlab"),
 *   description = @Translation("Gitlab.com")
 * )
 */
class Gitlab extends DrupaleasyRepositoriesPluginBase {

  /**
   * Authenticate with Gitlab.
   */
  protected function setAuthentication(): void {
    $this->client = new Client();
    $gitlab_key = $this->keyRepository->getKey('gitlab')->getKeyValues();
    $this->client->authenticate($gitlab_key['personal_access_token'], Client::AUTH_HTTP_TOKEN);
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Parse the URI.
    $all_parts = parse_url($uri);
    $parts = explode('/', $all_parts['path']);

    // Set up authentication with the Gitlab API.
    $this->setAuthentication();

    try {
      $project = $this->client->projects()->show($parts[1] . '/' . $parts[2]);
      if (empty($project)) {
        return [];
      }
    }
    catch (\Throwable $th) {
      $this->messenger->addMessage($this->t('Gitlab error: @error', [
        '@error' => $th->getMessage(),
      ]));
      return [];
    }

    return $this->mapToCommonFormat($project['path'], $project['name'], $project['description'], $project['open_issues_count'], $project['web_url']);
  }

  /**
   * {@inheritdoc}
   */
  public function validate($uri): bool {
    $pattern = '/^(https:\/\/)gitlab.com\/[a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+/';

    if (preg_match($pattern, $uri) == 1) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateHelpText(): string {
    return 'https://gitlab.com/vendor/name';
  }

}
