<?php

namespace Drupal\degov_common\Entity;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ConfigAdder.
 */
class ConfigAdder {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * ConfigAdder constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Add list item from configuration.
   */
  public function addListItemFromConfiguration(string $configName, string $configPath, string $value) {
    $config = $this->configFactory
      ->getEditable($configName);
    $newValue = array_flip($config->get($configPath));
    $newValue[$value] = -1;
    $newValue = array_keys($newValue);
    $config->set($configPath, $newValue);
    $config->save(TRUE);
  }

}
