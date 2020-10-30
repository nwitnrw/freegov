<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

use Drupal\Core\Entity\EntityInterface;

/**
 * Class AccessCheckInterface.
 */
interface AccessCheckInterface {

  /**
   * Can access.
   */
  public function canAccess(EntityInterface $entity);

}
