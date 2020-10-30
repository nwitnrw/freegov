<?php

namespace Drupal\degov_common\Entity;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class WorkflowHandler.
 */
class WorkflowHandler {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * WorkflowHandler constructor.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $this->config = $this->configFactory
      ->getEditable('workflows.workflow.editorial');
  }

  /**
   * Enable workflow.
   */
  public function enableWorkflow(string $nodeType): void {
    if (empty($this->config)) {
      throw new \Exception('workflows core module is not installed.');
    }

    $nodeTypes = $this->config->get('type_settings.entity_types.node');
    if (!$nodeTypes || !array_key_exists($nodeType, array_flip($nodeTypes))) {
      $nodeTypes[] = $nodeType;
      $this->config
        ->set('type_settings.entity_types.node', $nodeTypes)
        ->save(TRUE);
    }
  }

  /**
   * Disable workflow.
   */
  public function disableWorkflow(string $nodeType): void {
    if (empty($this->config)) {
      throw new \Exception('workflows core module is not installed.');
    }

    /** @var string[] $nodeTypesConfig */
    $nodeTypesConfig = $this->config->get('type_settings.entity_types.node');
    $nodeTypes = array_keys(array_flip($nodeTypesConfig));
    if (\in_array($nodeType, $nodeTypes, TRUE)) {
      unset($nodeTypes[$nodeType]);
      $filteredTypes = array_values($nodeTypes);
      $this->config
        ->set('type_settings.entity_types.node', $filteredTypes)
        ->save(TRUE);
    }
  }

}
