<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_common\Kernel;

use Drupal\Core\Config\ConfigImporterException;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class DegovConfigUpdateTest.
 *
 * @package Drupal\Tests\degov_common\Kernel
 */
class DegovConfigUpdateTest extends KernelTestBase {
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
   * Config update.
   *
   * @var \Drupal\degov_common\DegovConfigUpdate
   */
  private $configUpdate;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp() : void {
    parent::setUp();
    $this->configUpdate = $this->container->get('degov_config.updater');
    $this->installConfig(['field', 'system']);
  }

  /**
   * Test the import validation of degov_module/config/install.
   *
   * @throws \Exception
   */
  public function testConfigPartialImport() {
    $this->enableModules([self::TEST_MODULE]);
    $moduleHandler = $this->container->get('module_handler');
    self::assertTrue($moduleHandler->moduleExists(self::TEST_MODULE));
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $config_name = 'field.storage.media.field_title';
    $config = $config_factory->get($config_name);
    self::assertTrue($config->isNew());
    self::assertEmpty($config->getRawData());
    $this->installConfig([self::TEST_MODULE]);

    try {
      $this->configUpdate->configPartialImport(self::TEST_MODULE);
    }
    catch (ConfigImporterException $e) {
      $expected = <<<EOF
There were errors validating the config synchronization.
Configuration <em class="placeholder">$config_name</em> depends on the <em class="placeholder">non_existing</em> module that will not be installed after import.
Configuration <em class="placeholder">$config_name</em> depends on the <em class="placeholder">foobar</em> configuration that will not exist after import.
EOF;
      $this->assertEqual($e->getMessage(), $expected);
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Test the import validation  of degov_module/config/install/config_name.yml.
   *
   * @throws \Exception
   */
  public function testImportConfigFile() {
    $this->enableModules([self::TEST_MODULE]);
    $moduleHandler = $this->container->get('module_handler');
    self::assertTrue($moduleHandler->moduleExists(self::TEST_MODULE));
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $config_name = 'field.storage.media.field_title';
    $config = $config_factory->get($config_name);
    self::assertTrue($config->isNew());
    self::assertEmpty($config->getRawData());
    $this->installConfig([self::TEST_MODULE]);

    try {
      $this->configUpdate->importConfigFile(self::TEST_MODULE, $config_name);
    }
    catch (ConfigImporterException $e) {
      $expected = <<<EOF
There were errors validating the config synchronization.
Configuration <em class="placeholder">$config_name</em> depends on the <em class="placeholder">non_existing</em> module that will not be installed after import.
Configuration <em class="placeholder">$config_name</em> depends on the <em class="placeholder">foobar</em> configuration that will not exist after import.
EOF;
      $this->assertEqual($e->getMessage(), $expected);
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

}
