<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage_paragraphs\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\degov_media_usage\Service\MediaUsagePersistance;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class ParagraphPersistance.
 *
 * @package Drupal\degov_media_usage_paragraphs\Service
 */
final class ParagraphPersistance extends MediaUsagePersistance {

  /**
   * Store references from a paragraph to Media.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The referencing paragraph.
   * @param array $media
   *   The referenced Media.
   *
   * @return bool
   *   Was the storage process successful?
   *
   * @throws \Exception
   */
  public function store(EntityInterface $entity, array $media = []): bool {
    parent::store($entity, $media);

    if ($entity instanceof ParagraphInterface) {
      $queryFields = [
        'mid',
        'entity_type',
        'bundle_name',
        'eid',
        'langcode',
        'submodule'
      ];

      $parent = $entity->getParentEntity();
      if ($parent === NULL) {
        return TRUE;
      }

      foreach ($media as $mid) {
        $queryValues = [
          $mid,
          $parent->getEntityType()->id(),
          $parent->bundle(),
          $parent->id(),
          $parent->language()->getId(),
          $this->submodule,
        ];

        if (!$this->isNewEntry($entity, (int) $mid)) {
          continue;
        }

        $this->database->insert(parent::TABLE)
          ->fields($queryFields)
          ->values($queryValues)
          ->execute();
      }
    }

    return TRUE;
  }

  /**
   * Purges reference records to a given paragraph.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to purge references to.
   *
   * @return bool|int
   *   Was the purge successful?
   */
  public function purge(EntityInterface $entity) {
    parent::purge($entity);

    if ($entity instanceof ParagraphInterface) {
      $parent = $entity->getParentEntity();
      if ($parent === NULL) {
        return TRUE;
      }

      $query = $this->database->delete(parent::TABLE)
        ->condition('submodule', $this->submodule)
        ->condition('entity_type', $parent->getEntityType()->id())
        ->condition('bundle_name', $parent->bundle())
        ->condition('eid', $parent->id())
        ->condition('langcode', $parent->language()->getId());
      $query->execute();
    }

    return TRUE;
  }

}
