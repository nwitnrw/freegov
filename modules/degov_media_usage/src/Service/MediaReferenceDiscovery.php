<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Service;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class MediaReferenceDiscovery.
 *
 * @package Drupal\degov_media_usage\Service
 */
final class MediaReferenceDiscovery {

  /**
   * The EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The EntityFieldManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * MediaReferenceDiscovery constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManagerInterface.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The EntityFieldManagerInterface.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * Return all bundles of given type that have references to media entity.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   *
   * @return array|bool
   *   An array of matching bundles.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPossibleBundles($entityTypeId) {
    $fields = $this->getMediaEntityFields($entityTypeId);
    return $fields ? array_keys($fields) : FALSE;
  }

  /**
   * Return all media entity reference fields for given bundle.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param string $bundle
   *   The bundle name.
   *
   * @return array|bool|mixed
   *   The possible fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPossibleFields($entityTypeId, $bundle) {
    return $this->getMediaEntityFields($entityTypeId, $bundle);
  }

  /**
   * Return all media entity reference fields by entity type and bundle name.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param string|null $bundle
   *   An optional media bundle.
   *
   * @return array|bool|mixed
   *   The media entity fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getMediaEntityFields(string $entityTypeId, string $bundle = NULL) {
    $definition = $this->entityTypeManager->getDefinition($entityTypeId);
    $entityType = $definition->getBundleEntityType();
    $types = $this->entityTypeManager->getStorage($entityType)->loadMultiple();

    $results = [];

    foreach (array_keys($types) as $type) {
      $fields = $this->entityFieldManager->getFieldDefinitions(
        $entityTypeId,
        $type
      );

      foreach ($fields as $fieldName => $fieldConfig) {
        /** @var \Drupal\field\Entity\FieldConfig $fieldConfig */
        if ($fieldConfig->getType() === 'entity_reference'
          && $fieldConfig->getSetting('handler') === 'default:media') {
          if (!isset($results[$type])) {
            $results[$type] = [];
          }
          $results[$type][] = $fieldName;
        }
      }
    }

    if ($bundle) {
      return $results[$bundle] ?? FALSE;
    }

    return $results;
  }

}
