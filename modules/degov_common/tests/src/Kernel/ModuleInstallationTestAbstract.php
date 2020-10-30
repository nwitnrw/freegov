<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\Core\Extension\Extension;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class ModuleInstallationTestAbstract.
 */
abstract class ModuleInstallationTestAbstract extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(self::$modules);
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
