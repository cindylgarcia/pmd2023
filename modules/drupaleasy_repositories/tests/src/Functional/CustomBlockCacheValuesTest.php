<?php

namespace Drupal\Tests\drupaleasy_repositories\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests custom block cache values.
 *
 * @group drupaleasy_repositories
 */
class CustomBlockCacheValuesTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['drupaleasy_repositories', 'block', 'system'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('drupaleasy_repositories_my_repositories_stats',
      [
        'region' => 'content',
        'id' => 'drupaleasy_repositories_my_repositories_stats',
      ]);
  }

  /**
   * Tests to ensure custom cache values are present.
   *
   * @test
   */
  public function testCustomCacheValues(): void {
    // Load the home page.
    $this->drupalGet('');

    // Set the cacheability settings of the block to the following for these
    // test to pass:
    //
    // $build['#cache'] = [
    //   'max-age' => 123,
    //   'tags' => ['node_list:repository', 'drupaleasy_repositories'],
    //   'contexts' => ['user.roles'],
    // ];.
    // This demonstrates that max-age does not bubble up.
    $this->assertSession()->responseHeaderNotContains('Cache-Control', '123');

    // These demonstrate that the cache tags were added and did bubble up.
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'drupaleasy_repositories');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'node_list:repository');

    // This demonstrates that the cache context was added and did bubble up.
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Contexts', 'user.roles');
  }

}
