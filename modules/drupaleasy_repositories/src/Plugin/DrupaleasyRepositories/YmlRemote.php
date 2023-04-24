<?php

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\Component\Serialization\Yaml;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;

/**
 * Plugin implementation of the drupaleasy_repositories.
 *
 * @DrupaleasyRepositories(
 *   id = "yml_remote",
 *   label = @Translation("Remote .yml file"),
 *   description = @Translation("Remote .yml file that includes repository metadata.")
 * )
 */
class YmlRemote extends DrupaleasyRepositoriesPluginBase {

  /**
   * {@inheritdoc}
   */
  public function validate($uri): bool {
    $pattern = '|^https?://[a-zA-Z0-9.\-\:]+/[a-zA-Z0-9_\-.%/]+\.ya?ml$|';

    if (preg_match($pattern, $uri) === 1) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateHelpText(): string {
    return 'https://anything.anything/anything/anything.yml (or "http")';
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Temporarily set the PHP error handler to this custom one. If there are
    // any E_WARNINGs, then TRUE to disable default PHP error handler.
    set_error_handler(function () {
      // If FALSE is returned, then the default PHP error handler is run as
      // well.
      return TRUE;
    },
    E_WARNING
    );

    // file_exists doesn't work with files over http.
    if (file($uri)) {
      // Restore the default PHP error handler.
      restore_error_handler();
      if ($file_content = file_get_contents($uri)) {
        $repo_info = Yaml::decode($file_content);
        $machine_name = array_key_first($repo_info);
        $repo = reset($repo_info);
        return $this->mapToCommonFormat($machine_name, $repo['label'], $repo['description'], $repo['num_open_issues'], $uri);
      }
    }
    // Restore the default PHP error handler.
    restore_error_handler();
    return [];
  }

}
