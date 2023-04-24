<?php

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Test description.
 *
 * @group drupaleasy_repositories
 */
class DrupaleasyRepositoriesServiceTest extends KernelTestBase {
  use RepositoryContentTypeTrait;

  /**
   * {@inheritdoc}
   *
   * @var array<int, string> $modules
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'node',
    'system',
    'field',
    'text',
    'link',
    'user',
    'key',
  ];

  /**
   * The drupaleasy_repositories service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * The Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupaleasyRepositoriesService = $this->container->get('drupaleasy_repositories.service');
    $this->createRepositoryContentType();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $aquaman_repo = $this->getRepo('aquaman');
    $repo = reset($aquaman_repo);

    $this->adminUser = User::create([
      'name' => $this->randomString(),
    ]);
    $this->adminUser->save();

    // $this->container->get('current_user')->setAccount($this->adminUser);
    $node = Node::create([
      'type' => 'repository',
      'title' => $repo['label'],
      'field_machine_name' => array_key_first($aquaman_repo),
      'field_url' => $repo['url'],
      'field_hash' => '06ec2efe7005ae32f624a9c2d28febd5',
      'field_number_of_issues' => $repo['num_open_issues'],
      'field_source' => $repo['source'],
      'field_description' => $repo['description'],
      'uid' => $this->adminUser->id(),
    ]);
    $node->save();

    // Enable the .yml repository plugin.
    $config = $this->config('drupaleasy_repositories.settings');
    $config->set('repositories', ['yml_remote' => 'yml_remote']);
    $config->save();
  }

  /**
   * Data provider for testIsUnique().
   *
   * @return array<int, array<int, bool|array<string, array<string, array<string, mixed>>>>>
   *   Test data and expected results.
   */
  public function providerTestIsUnique(): array {
    return [
      [FALSE, $this->getRepo('aquaman')],
      [TRUE, $this->getRepo('superman')],
    ];
  }

  /**
   * Test the DrupaleasyRepositoriesService::isUnique method.
   *
   * @param bool $expected
   *   The expected test result.
   * @param array<string, array<string, mixed>> $repo
   *   The respository metadata to be tested.
   *
   * @covers \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService::isUnique
   * @dataProvider providerTestIsUnique
   * @test
   */
  public function testIsUnique(bool $expected, array $repo): void {
    // Use reflection to make isUnique() public.
    $reflection_is_unique = new \ReflectionMethod($this->drupaleasyRepositoriesService, 'isUnique');

    // Not necessary for PHP 8.1 or later.
    $reflection_is_unique->setAccessible(TRUE);
    $actual = $reflection_is_unique->invokeArgs(
      $this->drupaleasyRepositoriesService,
      // Use $uid = 999 to ensure it is different from $this->adminUser.
      [$repo, 999]
    );

    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testValidateRepositoryUrls().
   *
   * @return array<int, array<int, array<int, array<string, string>>>>
   *   Test data and expected results.
   */
  public function providerValidateRepositoryUrls(): array {
    // This is run before setup() and other things so $this->container
    // isn't available here!
    return [
      ['', [['uri' => '/tests/assets/batman-repo.yml']]],
      ['is not valid', [['uri' => '/tests/assets/batman-repo.ym']]],
    ];
  }

  /**
   * Test the ability for the service to ensure repositories are valid.
   *
   * @param string $expected
   *   The expected result.
   * @param array<string, array<string, string>> $urls
   *   The URLs to be tested.
   *
   * @covers \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService::validateRepositoryUrls
   * @dataProvider providerValidateRepositoryUrls
   * @test
   */
  public function testValidateRepositoryUrls(string $expected, array $urls): void {
    // Get the full path to the test .yml file.
    $this->moduleHandler = \Drupal::service('module_handler');
    /** @var \Drupal\Core\Extension\Extension $module */
    $module = $this->moduleHandler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    foreach ($urls as $key => $url) {
      if (isset($url['uri'])) {
        $urls[$key]['uri'] = $module_full_path . $url['uri'];
      }
    }

    $actual = $this->drupaleasyRepositoriesService->validateRepositoryUrls($urls, 999);
    if ($expected) {
      $this->assertTrue((bool) mb_stristr($actual, $expected), "The URLs' validation does not match the expected value. Actual: {$actual}, Expected: {$expected}");
    }
    else {
      $this->assertEquals($expected, $actual, "The URLs' validation does not match the expected value. Actual: {$actual}, Expected: {$expected}");
    }
  }

  /**
   * Returns sample repository info.
   *
   * @return array<string, array<string, mixed>>
   *   The sample repository info.
   */
  protected function getRepo(string $repo_name): array {
    switch ($repo_name) {
      case 'aquaman':
        return [
          'aquaman-repository' =>
          [
            'label' => 'The Aquaman repository',
            'description' => 'This is where Aquaman keeps all his crime-fighting code.',
            'num_open_issues' => 6,
            'source' => 'yml',
            'url' => 'http://example.com/aquaman-repo.yml',
          ],
        ];

      default:
        return [
          'superman-repository' =>
          [
            'label' => 'The Superman repository',
            'description' => 'This is where Superman keeps all his crime-fighting code.',
            'num_open_issues' => 0,
            'source' => 'yml',
            'url' => 'https://example.com/superman-repo.yml',
          ],
        ];
    }
  }

}
