<?php

namespace Drupal\degov_common;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface DegovConfigManagerInterface.
 *
 * @package Drupal\degov_common
 */
interface DegovConfigManagerInterface {

  /**
   * Imports all the changes for the block configuration with batch.
   *
   * @param \Drupal\Core\Config\StorageInterface $sourceStorage
   *   Source storage.
   */
  public function configImport(StorageInterface $sourceStorage) : void;

}
