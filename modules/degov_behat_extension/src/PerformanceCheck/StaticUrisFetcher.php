<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\PerformanceCheck;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasRepositoryInterface;

/**
 * Class StaticUrisFetcher.
 */
class StaticUrisFetcher {

  /**
   * @var \Drupal\path_alias\AliasRepositoryInterface
   */
  private $aliasRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  public function __construct(AliasRepositoryInterface $aliasRepository, EntityTypeManagerInterface $entityTypeManager) {
    $this->aliasRepository = $aliasRepository;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function provideUris(): ?array {
    $contentEntityTypes = ['media', 'node'];
    $uris = [];

    foreach ($contentEntityTypes as $contentEntityType) {
      $uris = \array_merge($uris, $this->provideUrisByEntityTypeStorage($contentEntityType));
    }

    return $uris;
  }

  public function provideUrisByEntityTypeStorage(string $contentEntityTypeStorage): ?array {
    $contentEntityIds = $this->getContentEntityTypeIds($contentEntityTypeStorage);
    $uris = $this->provideUrisByContentEntityType($contentEntityTypeStorage, $contentEntityIds);

    return $uris;
  }

  private function getContentEntityTypeIds(string $storage) {
    $storage = $this->entityTypeManager->getStorage($storage);
    $entities = $storage->loadByProperties([]);

    $ids = [];

    foreach ($entities as $entity) {
      $ids[] = $entity->id();
    }

    return $ids;
  }

  private function provideUrisByContentEntityType(string $entityType, array $entityIds): array {
    foreach ($entityIds as $entityId) {
      $uri = $this->aliasRepository->lookupBySystemPath('/' . $entityType . '/' . $entityId, 'und');

      if (!empty($uri)) {
        $uris[] = $uri['alias'];
        continue;
      }

      $uris[] = '/' . $entityType . '/' . $entityId;
    }

    return $uris;
  }

}
