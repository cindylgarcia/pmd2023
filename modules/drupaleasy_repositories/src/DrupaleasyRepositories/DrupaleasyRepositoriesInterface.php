<?php

namespace Drupal\drupaleasy_repositories\DrupaleasyRepositories;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for drupaleasy_repositories plugins.
 */
interface DrupaleasyRepositoriesInterface extends PluginInspectionInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * URL validator.
   *
   * @param string $uri
   *   The URI to validate.
   *
   * @return bool
   *   Returns TRUE if the validation passes.
   */
  public function validate(string $uri): bool;

  /**
   * Returns help text for the plugin's URL pattern required.
   *
   * @return string
   *   The help text string.
   */
  public function validateHelpText(): string;

  /**
   * Queries the repository source for info about a repository.
   *
   * @param string $uri
   *   The URI of the repo.
   *
   * @return array<string, array<string, string>>
   *   The metadata of each repository.
   */
  public function getRepo(string $uri): array;

}
