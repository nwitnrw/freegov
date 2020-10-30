<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage;

/**
 * Class EntityTypeInfo.
 *
 * @package Drupal\degov_media_usage
 */
class EntityTypeInfo {

  /**
   * Adds degov_media_usage links to appropriate entity types. This is an alter hook bridge.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface[] $entityTypes
   *   The master entity type list to alter.
   *
   * @see hook_entity_type_alter()
   */
  public function entityTypeAlter(array &$entityTypes): void {
    $entityTypes['media']->setLinkTemplate(
      'degov-media-usage-refs',
      '/admin/content/media/refs/{media}'
    );
  }

}
