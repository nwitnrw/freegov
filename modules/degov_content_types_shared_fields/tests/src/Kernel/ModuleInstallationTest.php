<?php

namespace Drupal\Tests\degov_content_types_shared_fields\Kernel;

use Drupal\Core\Extension\Extension;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;

/**
 * Class ModuleInstallationTest.
 */
class ModuleInstallationTest extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'degov_content_types_shared_fields',
    'media',
    'image',
    'node',
    'lightning_core',
    'link',
    'paragraphs',
    'entity_reference_revisions',
    'taxonomy',
    'views',
    'views_parity_row',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installConfig(['degov_content_types_shared_fields']);
  }

  /**
   * Tests that the module can be installed and is available.
   */
  public function testSetup(): void {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $this->container->get('module_handler');
    $this->assertInstanceOf(Extension::class, $moduleHandler->getModule(reset(self::$modules)));
  }

}
