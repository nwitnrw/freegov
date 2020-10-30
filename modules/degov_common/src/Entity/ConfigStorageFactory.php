<?php

namespace Drupal\degov_common\Entity;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\degov_common\Entity\Exception\StorageMissingConfigObjectException;

/**
 * Class ConfigStorageFactory.
 */
class ConfigStorageFactory {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactory;

  /**
   * ConfigStorageFactory constructor.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Get editable.
   */
  public function getEditable(string $configObjectName): Config {
    $config = $this->configFactory->getEditable($configObjectName);
    if (!$config->isNew()) {
      return $config;
    }
    else {
      throw new StorageMissingConfigObjectException('Expected config object named "' . $configObjectName . '" is missing in the storage. Accidentally removed config? Config import failed?');
    }
  }

}
