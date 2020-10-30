<?php

declare(strict_types=1);

namespace Drupal\degov_common\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class EntityService.
 */
final class EntityService {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function load(string $entityType, array $conditions):?int {
    $query = $this->entityTypeManager->getStorage($entityType)->getQuery();
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }

    if ($nid = current($query->execute())) {
      return (int) $nid;
    }
    return NULL;
  }

}
