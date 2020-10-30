<?php

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class ConfigAdderTest.
 */
class ConfigAdderTest extends KernelTestBase {

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
   * Config adder.
   *
   * @var \Drupal\degov_common\Entity\ConfigAdder
   */
  private $configAdder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['taxonomy']);
    $this->configAdder = $this->container->get('degov_common.config_adder');
  }

  /**
   * Test add list item from configuration.
   */
  public function testAddListItemFromConfiguration() {
    $configFactory = $this->container->get('config.factory');
    $originalConfigList = $configFactory
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayNotHasKey('saschi', array_flip($originalConfigList->get('dependencies.module')));

    $this->configAdder->addListItemFromConfiguration('core.entity_view_mode.taxonomy_term.full', 'dependencies.module', 'saschi');

    $updatedConfigList = $configFactory
      ->getEditable('core.entity_view_mode.taxonomy_term.full');

    self::assertArrayHasKey('saschi', (!empty($updatedConfigList->get('dependencies.module'))) ? array_flip($updatedConfigList->get('dependencies.module')) : [], 'The dependencies.module config key must contain the "saschi" value in the list.');
  }

}
