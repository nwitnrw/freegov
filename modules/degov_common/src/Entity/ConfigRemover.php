<?php

namespace Drupal\degov_common\Entity;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ConfigRemover.
 */
class ConfigRemover {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactory;

  /**
   * ConfigRemover constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Remove value from configuration.
   */
  public function removeValueFromConfiguration(string $configName, string $configPath, string $key): void {
    $config = $this->configFactory
      ->getEditable($configName);
    $value = $config->get($configPath);
    if ($value && array_key_exists($key, $value)) {
      unset($value[$key]);
      $config->set($configPath, $value);
      $config->save(TRUE);
    }
  }

  /**
   * Remove list item from configuration.
   */
  public function removeListItemFromConfiguration(string $configName, string $configPath, string $key): void {
    $config = $this->configFactory
      ->getEditable($configName);
    $value = $config->get($configPath);
    if (!$value) {
      return;
    }
    $value = array_flip($value);
    if (array_key_exists($key, $value)) {
      unset($value[$key]);
      if ($value) {
        $value = array_keys($value);
        $config->set($configPath, $value);
        $config->save(TRUE);
      }
      else {
        // Remove element if it's empty.
        $configParts = explode('.', $configPath);
        $newKey = array_pop($configParts);
        $newPath = implode('.', $configParts);
        $this->removeValueFromConfiguration($configName, $newPath, $newKey);
      }
    }
  }

}
