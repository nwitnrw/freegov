<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Service;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class MediaUsagePersistance.
 *
 * @package Drupal\degov_media_usage\Service
 */
abstract class MediaUsagePersistance {

  protected const TABLE = 'degov_media_usage';

  /**
   * The MediaReferenceDiscovery.
   *
   * @var \Drupal\degov_media_usage\Service\MediaReferenceDiscovery
   */
  protected $referenceDiscovery;

  /**
   * The Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The id of the submodule.
   *
   * @var string
   */
  protected $submodule;

  /**
   * MediaUsagePersistance constructor.
   *
   * @param \Drupal\degov_media_usage\Service\MediaReferenceDiscovery $referenceDiscovery
   *   The MediaReferenceDiscovery.
   * @param \Drupal\Core\Database\Connection $database
   *   The Connection.
   * @param string $submodule
   *   The id of the submodule.
   */
  public function __construct(MediaReferenceDiscovery $referenceDiscovery, Connection $database, string $submodule) {
    $this->referenceDiscovery = $referenceDiscovery;
    $this->database = $database;
    $this->submodule = $submodule;
  }

  /**
   * Checks if entity has media reference fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   Does the entity have reference fields?
   */
  public function canHandle(EntityInterface $entity): bool {
    $bundles = $this->referenceDiscovery->getPossibleBundles($entity->getEntityType()->id());
    return in_array($entity->bundle(), $bundles, TRUE);
  }

  /**
   * Removes all media usages of given type for entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we want to purge references to.
   *
   * @return int
   *   The number of references deleted.
   */
  public function purge(EntityInterface $entity) {
    Cache::invalidateTags(['config:views.view.media', 'config:views.view.files']);

    $query = $this->database->delete(static::TABLE)
      ->condition('submodule', $this->submodule)
      ->condition('entity_type', $entity->getEntityType()->id())
      ->condition('bundle_name', $entity->bundle())
      ->condition('eid', $entity->id())
      ->condition('langcode', $entity->language()->getId());

    return $query->execute();
  }

  /**
   * Stores all media usages for entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $media
   *   An array of Media IDs to store for the entity.
   *
   * @return bool
   *   Was the storage process successful?
   *
   * @throws \Exception
   */
  public function store(EntityInterface $entity, array $media = []): bool {
    $queryFields = [
      'mid',
      'entity_type',
      'bundle_name',
      'eid',
      'langcode',
      'submodule'
    ];

    foreach ($media as $mid) {
      $queryValues = [
        $mid,
        $entity->getEntityType()->id(),
        $entity->bundle(),
        $entity->id(),
        $entity->language()->getId(),
        $this->submodule,
      ];

      if (!$this->isNewEntry($entity, (int) $mid)) {
        continue;
      }

      $this->database->insert(static::TABLE)
        ->fields($queryFields)
        ->values($queryValues)
        ->execute();
    }

    Cache::invalidateTags(['config:views.view.media', 'config:views.view.files']);

    return TRUE;
  }

  /**
   * Returns array of media ids for given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we want Media IDs for.
   *
   * @return array|bool
   *   The array of Media IDs, or FALSE.
   */
  public function getMedia(EntityInterface $entity) {
    $results = [];

    $fields = $this->referenceDiscovery->getPossibleFields(
      $entity->getEntityType()->id(),
      $entity->bundle()
    );
    $data = $entity->toArray();

    foreach ($fields as $field) {
      if (isset($data[$field])) {
        foreach ($data[$field] as $value) {
          $targetId = $value['target_id'];
          if (!empty($targetId) && !in_array($targetId, $results, TRUE)) {
            $results[] = $targetId;
          }
        }
      }
    }

    return $results ?: FALSE;
  }

  /**
   * Check if an entity is a new entry.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   * @param int $mid
   *   The Media ID.
   *
   * @return bool
   *   Is the entity new?
   */
  protected function isNewEntry(EntityInterface $entity, int $mid): bool {
    $query = $this->database->select(static::TABLE, 'mu')
      ->condition('mu.submodule', $this->submodule)
      ->condition('mu.entity_type', $entity->getEntityType()->id())
      ->condition('mu.bundle_name', $entity->bundle())
      ->condition('mu.eid', $entity->id())
      ->condition('mu.mid', $mid)
      ->condition('mu.langcode', $entity->language()->getId());
    $query->addField('mu', 'mid');

    $result = $query->countQuery()->execute()->fetchField();
    return (int) $result <= 0;
  }

}
