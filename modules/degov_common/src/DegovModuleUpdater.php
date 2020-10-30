<?php

declare(strict_types=1);

namespace Drupal\degov_common;

use Drupal\config_replace\ConfigReplacer;

/**
 * Class DegovModuleUpdater.
 *
 * @package Drupal\degov_common
 */
final class DegovModuleUpdater extends ConfigReplacer {

  /** @var \Drupal\degov_common\DegovConfigUpdate*/
  private $degovConfigUpdate;

  /** @var \Drupal\degov_common\DegovBlockInstallerInterface*/
  private $degovBlockInstaller;

  /**
   * @param \Drupal\degov_common\DegovBlockInstallerInterface $degov_block_installer
   */
  public function setDegovBlockInstaller(DegovBlockInstallerInterface $degov_block_installer): void {
    $this->degovBlockInstaller = $degov_block_installer;
  }

  /**
   * @param \Drupal\degov_common\DegovConfigUpdate $degov_config_update
   */
  public function setDegovConfigUpdate(DegovConfigUpdate $degov_config_update): void {
    $this->degovConfigUpdate = $degov_config_update;
  }

  /**
   * Applies all config updates for the version.
   *
   * @param string $module
   *   The module name.
   * @param string $version
   *   The update version.
   */
  public function applyUpdates(string $module, string $version): void {
    $source_dir = drupal_get_path('module', $module) . '/config/update_' . $version;
    $this->manageConfig($module, $source_dir);
  }

  /**
   * Imports a single config file.
   *
   * @param string $config_name
   *   The configuration name without extention.
   * @param string $module
   *   The module name.
   * @param string $config_type
   *   The configuration type, this could be install, optional or block.
   * @param bool $force
   *   Set to TRUE to import the configuration without checking if the module
   *   installed.
   */
  public function reImport(string $config_name, string $module, string $config_type, bool $force = FALSE): void {
    if ($force || $this->moduleHandler->moduleExists($module)) {
      $this->degovConfigUpdate->importConfigFile($module, $config_name, $config_type);
    }
  }

  /**
   * On module installed.
   */
  public function onModuleInstalled($module, $installed_module): void {
    $source_dir = drupal_get_path('module', $module) . '/config/' . $installed_module;
    $this->manageConfig($module, $source_dir);
  }

  /**
   * Dispatcher to import configuration.
   *
   * @param string $module
   *   The module name.
   * @param string $source_dir
   *   A directory path to use for reading of configuration files.
   */
  private function manageConfig(string $module, string $source_dir): void {
    if (file_exists($source_dir)) {
      // Are there any new installs?
      $install_dir = $source_dir . '/install';
      if (file_exists($install_dir)) {
        $this->degovConfigUpdate->importConfigFiles($install_dir);
      }
      // Are there any optional?
      $optional_dir = $source_dir . '/optional';
      if (file_exists($optional_dir)) {
        $this->degovConfigUpdate->checkOptional($optional_dir);
      }
      // Are there any blocks?
      $blocks_dir = $source_dir . '/block';
      if (file_exists($blocks_dir)) {
        $this->degovBlockInstaller->installBlockConfig($blocks_dir);
      }
      // Are there any rewrites?
      $updates_dir = $source_dir . '/rewrite';
      if (file_exists($updates_dir)) {
        $extension = $this->moduleHandler->getModule($module);
        $this->rewriteDirectoryConfig($extension, $updates_dir);
      }
    }
  }

  /**
   * Apply rewrites.
   */
  public function applyRewrites(string $module, string $version): void {
    $rewriteDir = drupal_get_path('module', $module) . '/config/update_' . $version . '/rewrite';
    if (file_exists($rewriteDir)) {
      $extension = $this->moduleHandler->getModule($module);
      $this->rewriteDirectoryConfig($extension, $rewriteDir);
    }
  }

}
