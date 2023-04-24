<?php

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;
use Github\Client;
use Github\AuthMethod;

/**
 * Plugin implementation of the drupaleasy_repositories.
 *
 * @DrupaleasyRepositories(
 *   id = "github",
 *   label = @Translation("GitHub"),
 *   description = @Translation("GitHub.com")
 * )
 */
class Github extends DrupaleasyRepositoriesPluginBase {

  /**
   * {@inheritdoc}
   */
  public function validate($uri): bool {
    $pattern = '|^https://(www\.)?github.com/[a-zA-Z0-9_-]+/[a-zA-Z0-9_-]+|';

    if (preg_match($pattern, $uri) === 1) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateHelpText(): string {
    return 'https://github.com/vendor/name';
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Parse the URI for the vendor name and repo name.
    $all_parts = parse_url($uri);
    $parts = explode('/', $all_parts['path']);

    // Set up authentication.
    $this->setAuthentication();

    // Git the repository metadata from the Github API.
    try {
      $repo = $this->client->api('repo')->show($parts[1], $parts[2]);
    }
    catch (\Throwable $th) {
      $this->messenger->addError($this->t('GitHub error: @error', [
        '@error' => $th->getMessage(),
      ]));

      return [];
    }

    // Map repository data to our common format.
    return $this->mapToCommonFormat($repo['full_name'], $repo['name'], $repo['description'], $repo['open_issues_count'], $repo['html_url']);
  }

  /**
   * Authenticate with GitHub.
   */
  protected function setAuthentication(): void {
    $this->client = new Client();
    $github_key = $this->keyRepository->getKey('github')->getKeyValues();

    // The authenticate() method does not actually call the Github API,
    // rather it only stores the authentication info in $client for use when
    // $client makes an API call that requires authentication.
    $this->client->authenticate($github_key['username'], $github_key['personal_access_token'], AuthMethod::CLIENT_ID);

    // // Test the credentials with the following code block.
    //    try {
    //      $emails = $this->client->currentUser()->emails()->allPublic();
    //    }
    //    catch (\Throwable $th) {
    //      print_r($th->getMessage());
    //    }
    //
    //    $blah = $emails;
  }

}
