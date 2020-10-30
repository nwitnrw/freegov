<?php

declare(strict_types=1);

namespace Drupal\degov;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Used for the installation of the degov profile.
 */
final class Installation {
  /** @var \Drupal\Core\Extension\ModuleHandlerInterface*/
  private $moduleHandler;

  /** @var \Drupal\Core\Extension\ModuleExtensionList*/
  private $moduleExtensionList;

  public function __construct(ModuleHandlerInterface $moduleHandler, ModuleExtensionList $moduleExtensionList) {
    $this->moduleHandler = $moduleHandler;
    $this->moduleExtensionList = $moduleExtensionList;
  }

  /**
   * @param array<string,string> $modules
   */
  public function getBatchOperations(array $modules):array {
    // Rebuild, save, and return data about all currently available modules.
    $this->moduleExtensionList->reset();
    $operations = [];
    foreach ($modules as $module) {
      if (!$this->moduleHandler->moduleExists($module)) {
        $operations[] = ['_install_degov_module_batch', [[$module], $this->moduleHandler->getName($module)]];
      }
    }
    return $operations;
  }

}
