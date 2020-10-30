<?php

namespace Drupal\lightning_media;

use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\media\MediaTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements InputMatchInterface for media types that use an embed code or URL.
 */
trait ValidationConstraintMatchTrait {


  /** @var \Drupal\Core\TypedData\TypedDataManagerInterface*/
  protected $typedDataManager;

  /**
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   */
  public function setTypedDataManager(TypedDataManagerInterface $typedDataManager): void {
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setTypedDataManager($container->get('typed_data_manager'));
    return $instance;
  }

  /**
   * Returns the typed data manager.
   *
   * @return \Drupal\Core\TypedData\TypedDataManagerInterface
   *   The typed data manager.
   */
  private function typedDataManager(): TypedDataManagerInterface {
    return $this->typedDataManager;
  }

  /**
   * Implements InputMatchInterface::appliesTo().
   */
  public function appliesTo($value, MediaTypeInterface $media_type) {
    $plugin_definition = $this->getPluginDefinition();

    $definition = $this->typedDataManager()
      ->createDataDefinition('string')
      ->addConstraint($plugin_definition['input_match']['constraint']);

    $data = StringData::createInstance($definition);
    $data->setValue($value);

    return $data->validate()->count() === 0;
  }

}
