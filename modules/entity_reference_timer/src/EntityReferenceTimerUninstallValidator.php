<?php

namespace Drupal\entity_reference_timer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class EntityReferenceTimerUninstallValidator.
 */
class EntityReferenceTimerUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EntityReferenceTimerUninstallValidator constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Validate.
   */
  public function validate($module): array {
    $reasons = [];

    if ($module === 'entity_reference_timer') {
      $fieldConfig = $this->entityTypeManager->getStorage('field_config')
        ->loadByProperties(['field_name' => FieldSwitchService::$fieldName]);
      if (FieldSwitchService::fieldExists() && $fieldConfig[FieldSwitchService::getFieldConfigKey()]->getType() === 'entity_reference_date') {
        $reasons[] = $this->t('To uninstall Entity Reference Timer, first migrate all content to the entity reference field and switch back the form field to the former state. Use the following Drush command for this: "drush entity_reference_timer:uninstall_field"');
      }
    }

    return $reasons;
  }

}
