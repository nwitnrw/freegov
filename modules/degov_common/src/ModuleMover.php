<?php

namespace Drupal\degov_common;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * Class ModuleMover
 */
final class ModuleMover {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * ModuleMover constructor.
   */
  public function __construct(Connection $connection, ConfigFactoryInterface $configFactory) {
    $this->connection = $connection;
    $this->configFactory = $configFactory;
  }

  /**
   * @param string $oldModuleName
   * @param string $newModuleName
   */
  public function renameModule(string $oldModuleName, string $newModuleName): void {
    $coreExtensionConfig = $this->configFactory->getEditable('core.extension');
    $moduleList = $coreExtensionConfig->get('module');
    // Only enabled modules are in this config.
    if (array_key_exists($oldModuleName, $moduleList)) {
      unset($moduleList[$oldModuleName]);
      $moduleList[$newModuleName] = 0;
      $coreExtensionConfig->set('module', $moduleList);
      $coreExtensionConfig->save();

      $this->connection->update('key_value')
        ->condition('collection', 'system.schema')
        ->condition('name', $oldModuleName)
        ->fields(['name' => $newModuleName])
        ->execute();

      $allConfigNames = $this->configFactory->listAll();

      foreach ($allConfigNames as $configName) {
        $this->replaceTheModuleDependencyInConfig($configName, $oldModuleName, $newModuleName);
      }
    }
  }

  /**
   * Renames the given list of modules.
   *
   * @param string[] $modules
   */
  public function renameModules(array $modules): void {
    foreach ($modules as $oldName => $newName) {
      $this->renameModule($oldName, $newName);
    }
  }

  /**
   * Replace the dependency in the config.
   *
   * @param string $configName
   * @param string $newModuleName
   * @param string $oldModuleName
   */
  private function replaceTheModuleDependencyInConfig(string $configName, string $newModuleName, string $oldModuleName): void {
    $coreExtensionConfig = $this->configFactory->getEditable($configName);
    $dependenciesConfigData = $coreExtensionConfig->get('dependencies');

    $save = FALSE;

    if (!empty($dependenciesConfigData['enforced']['module'])) {
      $dependenciesConfigData['enforced']['module'] = $this->findAndReplaceModule($dependenciesConfigData['enforced']['module'], $oldModuleName, $newModuleName);
      $save = TRUE;
    }

    if (!empty($dependenciesConfigData['module'])) {
      $dependenciesConfigData['module'] = $this->findAndReplaceModule($dependenciesConfigData['module'], $oldModuleName, $newModuleName);
      $save = TRUE;
    }

    if ($save) {
      $coreExtensionConfig->set('dependencies', $dependenciesConfigData);
      $coreExtensionConfig->save();
    }
  }

  /**
   * Replace the old name with a new name.
   *
   * @param string[] $listModules
   * @param string $oldModuleName
   * @param string $newModuleName
   *
   * @return string[]
   */
  private function findAndReplaceModule(array $listModules, string $oldModuleName, string $newModuleName): array {
    if (\in_array($oldModuleName, $listModules)) {
      $key = array_search($oldModuleName, $listModules);
      unset($listModules[$key]);
      if (!\in_array($newModuleName, $listModules)) {
        $listModules[] = $newModuleName;
      }
    }
    return $listModules;
  }

}
