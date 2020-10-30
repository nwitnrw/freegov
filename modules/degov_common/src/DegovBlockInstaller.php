<?php

declare(strict_types=1);

namespace Drupal\degov_common;

use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\FileStorage;

/**
 * Class DegovBlockInstaller.
 *
 * @package Drupal\degov_common
 */
class DegovBlockInstaller extends DegovConfigManagerBase implements DegovBlockInstallerInterface {

  /**
   * {@inheritdoc}
   */
  public function placeBlockConfig(string $module) : void {
    // Load the module extension.
    $extension = $this->moduleHandler->getModule($module);
    // Block configs are stored in 'modulename/config/block'.
    $dir_base = $extension->getPath() . DIRECTORY_SEPARATOR . self::BLOCK_CONFIG_DIRECTORY;
    // Rewrite configuration for the default language.
    $this->installBlockConfig($dir_base);
  }

  /**
   * Finds files in a given directory and uses them to rewrite active config.
   *
   * @param string $config_dir
   *   The directory that contains config rewrites.
   */
  public function installBlockConfig(string $config_dir) : void {
    $source_storage = new StorageReplaceDataWrapper($this->activeStorage);
    $file_storage = new FileStorage($config_dir);

    foreach ($file_storage->listAll() as $config_name) {
      $block = $file_storage->read($config_name);
      $this->addUuid($config_name, $block);
      // Check if the theme from the configuration exists.
      if (!$this->themeHandler->themeExists($block['theme'])) {
        // If not, set the theme to currently active.
        $currentActiveThemeName = $this->themeHandler->getDefault();
        $block['theme'] = $currentActiveThemeName;
        $block['dependencies']['theme'] = [$currentActiveThemeName];
      }
      // Get the list of all the regions provided from the theme.
      $regions = system_region_list($block['theme']);
      if (empty($regions[$block['region']])) {
        if (!empty($regions['content'])) {
          $block['region'] = 'content';
        }
        else {
          $region_ids = array_keys($regions);
          $block['region'] = $region_ids[0];
        }
      }
      // Try to set new data to active configuration.
      $source_storage->replaceData($config_name, $block);
      $this->configImport($source_storage);
    }
  }

}
