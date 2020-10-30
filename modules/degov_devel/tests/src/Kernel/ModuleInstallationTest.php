<?php

namespace Drupal\Tests\degov_devel\Kernel;

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
  ];

  /**
   * Test module can be installed and uninstalled.
   */
  public function testModuleCanBeInstalledAndUninstalled(): void {
    /**
     * @var \Drupal\Core\Extension\ModuleHandler $moduleHandler
     */
    $this->container->get('module_installer')->install(['degov_devel']);
    self::assertTrue($this->container->get('module_handler')->moduleExists('degov_devel'));
    self::assertTrue($this->container->get('module_handler')->moduleExists('devel'));
    self::assertTrue($this->container->get('module_handler')->moduleExists('webprofiler'));

    $this->container->get('module_installer')->uninstall([
      'degov_devel',
      'webprofiler',
      'devel',
    ]);
    self::assertFalse($this->container->get('module_handler')->moduleExists('degov_devel'));
    self::assertFalse($this->container->get('module_handler')->moduleExists('devel'));
    self::assertFalse($this->container->get('module_handler')->moduleExists('webprofiler'));
  }

}
