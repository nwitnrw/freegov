<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\Core\Config\ConfigImporterException;
use Drupal\Core\Extension\Extension;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class DegovModuleUpdaterTest.
 *
 * @package Drupal\Tests\degov_common\Kernel
 */
class DegovModuleUpdaterTest extends KernelTestBase {

  const TEST_MODULE = 'degov_common_missing_dependency';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'language',
    'field',
    'language',
    'degov_common',
    'media',
    'video_embed_field',
    'block',
    'config_replace'
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp() : void {
    parent::setUp();
    $this->installConfig(['field', 'system']);
    $this->setInstallProfile('degov');

    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
    $themeInstaller = $this->container->get('theme_installer');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    /** @var \Drupal\Core\Theme\ThemeManagerInterface $themeManager */
    $themeManager = $this->container->get('theme.manager');

    $themeInstaller->install(['degov_theme']);
    $configFactory
      ->getEditable('system.theme')
      ->set('default', 'degov_theme')
      ->save();
    $themeManager->resetActiveTheme();
  }

  /**
   * Test apply updates.
   *
   * Test config import validation of degov_module/config/update_1234/install
   * and degov_module/config/update_5678/block.
   *
   * @throws \Exception
   */
  public function testApplyUpdates(): void {
    $this->enableModules([self::TEST_MODULE]);
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $this->container->get('module_handler');
    $this->assertInstanceOf(Extension::class, $moduleHandler->getModule(self::TEST_MODULE));

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $config_name = 'field.storage.media.field_title';
    $config = $configFactory->get($config_name);
    self::assertTrue($config->isNew());
    self::assertEmpty($config->getRawData());

    // Drupal do not check the config dependencies here? All is fine via UI.
    // TODO: Find out why.
    $this->installConfig([self::TEST_MODULE]);
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = $this->container->get('config.factory');
    $config = $configFactory->get($config_name);
    self::assertFalse($config->isNew());
    self::assertNotEmpty($config->getRawData());

    /** @var \Drupal\degov_common\DegovModuleUpdater $moduleUpdater */
    $moduleUpdater = $this->container->get('degov_config.module_updater');

    try {
      $moduleUpdater->applyUpdates(self::TEST_MODULE, '1234');
    }
    catch (ConfigImporterException $e) {
      $expected = <<<EOF
There were errors validating the config synchronization.
Configuration <em class="placeholder">$config_name</em> depends on the <em class="placeholder">non_existing</em> module that will not be installed after import.
EOF;
      $this->assertEqual($e->getMessage(), $expected);
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    /** @var \Drupal\degov_common\DegovBlockInstallerInterface $block_installer */
    $block_installer = $this->container->get('degov_config.block_installer');
    $block_installer->placeBlockConfig(self::TEST_MODULE);
    try {
      $moduleUpdater->applyUpdates(self::TEST_MODULE, '5678');
    }
    catch (ConfigImporterException $e) {
      $expected = <<<EOF
There were errors validating the config synchronization.
Configuration <em class="placeholder">block.block.sidebarparagraphsfromnodeentity</em> depends on the <em class="placeholder">non_existing</em> module that will not be installed after import.
EOF;
      $this->assertEqual($e->getMessage(), $expected);
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

}
