<?php

namespace Drupal\entity_reference_timer\Command;

use Drupal\entity_reference_timer\FieldSwitchService;
use Drush\Commands\DrushCommands;

/**
 * Class EntityReferenceTimerDrushCommands.
 */
class EntityReferenceTimerDrushCommands extends DrushCommands {

  /**
   * Uninstall field.
   *
   * @command entity_reference_timer:uninstall_field
   */
  public function uninstallField(): void {
    FieldSwitchService::uninstallField();

    $this->say('Success. You have switched the field type "entity_reference_date" back to "entity_reference". Now you are able to uninstall the entity_reference_timer module (drush pm:uninstall entity_reference_timer -y).');
  }

}
