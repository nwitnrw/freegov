<?php

declare(strict_types=1);

namespace Drupal\degov_common\Entity;

/**
 * Class NodeService.
 */
final class NodeService {

  /** @var \Drupal\degov_common\Entity\EntityService*/
  private $entityService;

  public function __construct(EntityService $entity_service) {
    $this->entityService = $entity_service;
  }

  public function load(array $conditions): ?int {
    return $this->entityService->load('node', $conditions);
  }

}
