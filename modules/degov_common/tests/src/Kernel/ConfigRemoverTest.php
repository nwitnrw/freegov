<?php

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class ConfigRemoverTest.
 */
class ConfigRemoverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'system',
    'language',
    'node',
    'paragraphs',
    'language',
    'degov_common',
    'config_replace',
    'video_embed_field',
    'paragraphs',
    'file',
    'text',
    'taxonomy',
  ];

  /**
   * Config remover.
   *
   * @var \Drupal\degov_common\Entity\ConfigRemover
   */
  private $configRemover;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['taxonomy']);
    $this->configRemover = $this->container->get('degov_common.config_remover');
  }

  /**
   * Test remove list item from configuration.
   */
  public function testRemoveListItemFromConfiguration() {
    $originalConfigList = $this->container->get('config.factory')
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayHasKey('taxonomy', array_flip($originalConfigList->get('dependencies.module')));

    $this->configRemover->removeListItemFromConfiguration('core.entity_view_mode.taxonomy_term.full', 'dependencies.module', 'taxonomy');

    $updatedConfigList = $this->container->get('config.factory')
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayNotHasKey('taxonomy', (!empty($updatedConfigList->get('dependencies.module'))) ? array_flip($updatedConfigList->get('dependencies.module')) : [], 'The dependencies.module config key must not contain the "taxonomy" key in the list.');
  }

  /**
   * Test remove value from configuration.
   */
  public function testRemoveValueFromConfiguration() {
    $originalConfigList = $this->container->get('config.factory')
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayHasKey('taxonomy', array_flip($originalConfigList->get('dependencies.module')));

    $this->configRemover->removeValueFromConfiguration('core.entity_view_mode.taxonomy_term.full', 'dependencies', 'module');

    $updatedConfigList = $this->container->get('config.factory')
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayNotHasKey('module', (!empty($updatedConfigList->get('dependencies'))) ? array_flip($updatedConfigList->get('dependencies')) : [], 'The dependencies config key must not contain the "module" child-key as a parent array element.');
  }

}
