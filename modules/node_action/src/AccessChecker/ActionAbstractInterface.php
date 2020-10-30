<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Interface ActionAbstractInterface.
 */
interface ActionAbstractInterface {

  /**
   * Has permission.
   */
  public function hasPermission(): bool;

  /**
   * Has permissions by term permission.
   */
  public function hasPermissionsByTermPermission(NodeInterface $node): bool;

  /**
   * Can access.
   */
  public function canAccess(EntityInterface $entity);

}
