<?php

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\Core\Extension\Extension;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class ModuleInstallationTest.
 */
class ModuleInstallationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'views',
    'filter',
    'field',
    'image',
    'file',
    'node',
    'taxonomy',
    'text',
    'language',
    'media',
    'degov_common',
    'video_embed_field',
    'entity_browser',
    'lightning_media',
    'config_replace',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['image', 'degov_common']);
  }

  /**
   * Test setup.
   */
  public function testSetup(): void {
    /**
     * @var \Drupal\Core\Extension\ModuleHandler $moduleHandler
     */
    $moduleHandler = $this->container->get('module_handler');
    self::assertInstanceOf(Extension::class, $moduleHandler->getModule('degov_common'));
  }

}
