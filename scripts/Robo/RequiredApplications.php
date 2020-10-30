<?php

namespace degov\Scripts\Robo;

use degov\Scripts\Robo\Exception\ApplicationRequirementFail;

/**
 * Class RequiredApplications.
 */
class RequiredApplications {

  /**
   * Check application requirement fulfilled.
   */
  public function checkApplicationRequirementFulfilled(): void {
    if (!$this->isNpmInstalled() || !$this->isDrushInstalled()) {
      throw new ApplicationRequirementFail();
    }
  }

  /**
   * Is npm installed.
   */
  private function isNpmInstalled(): bool {
    $command = 'which npm';
    exec($command, $output);
    if (!empty($output)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Is drush installed.
   */
  private function isDrushInstalled(): bool {
    $command = 'which drush';
    exec($command, $output);
    if (!empty($output)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
