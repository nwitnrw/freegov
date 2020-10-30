<?php

namespace Drupal\Tests\degov_behat_extension\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\RoleInterface;

/**
 * Class NodeUrisFetcherTest.
 */
class NodeUrisFetcherTest extends KernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }
  use UserCreationTrait {
    createUser as drupalCreateUser;
    createRole as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
  }
  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'datetime',
    'user',
    'system',
    'filter',
    'field',
    'text',
    'degov_behat_extension',
  ];

  /**
   * Access handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', 'sequences');
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('path_alias');
    $this->installConfig('filter');
    $this->installConfig('node');
    $this->accessHandler = $this->container->get('entity_type.manager')
      ->getAccessControlHandler('node');
    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();

    // Create user 1 who has special permissions.
    $this->drupalCreateUser();

    // Create a node type.
    $this->drupalCreateContentType([
      'type'              => 'page',
      'name'              => 'Basic page',
      'display_submitted' => FALSE,
    ]);
  }

  public function testProvide(): void {
    Node::create([
      'type'  => 'page',
      'title' => 'Example node title 1',
    ])->save();

    Node::create([
      'type'  => 'page',
      'title' => 'Example node title 2',
    ])->save();

    /**
     * @var \Drupal\Core\Entity\EntityStorageInterface $aliasStorage
     */
    $aliasStorage = $this->container->get('entity_type.manager')->getStorage('path_alias');
    $aliasStorage->save(PathAlias::create([
      'path'     => '/node/1',
      'alias'    => '/test-node-1',
      'langcode' => 'und',
      'status'   => 1,
    ]));
    $aliasStorage->save(PathAlias::create([
      'path'     => '/node/2',
      'alias'    => '/test-node-2',
      'langcode' => 'und',
      'status'   => 1,
    ]));

    /**
     * @var \Drupal\degov_behat_extension\PerformanceCheck\StaticUrisFetcher $staticUrisFetcher
     */
    $staticUrisFetcher = $this->container->get('degov_behat_extension.static_uris_fetcher');

    self::assertSame([
      0 => '/test-node-1',
      1 => '/test-node-2',
    ], $staticUrisFetcher->provideUrisByEntityTypeStorage('node'));
  }

}
