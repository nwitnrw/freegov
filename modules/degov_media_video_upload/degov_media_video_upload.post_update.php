<?php

/**
 * @file
 * Media video upload post update.
 */

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\Media;

/**
 * Migrate field_media_published_date to field_media_publish_date.
 */
function degov_media_video_upload_post_update_migrate_field_date(&$sandbox) {

  $oldFieldName = 'field_media_published_date';
  $newFieldName = 'field_media_publish_date';
  $bundle = 'video_upload';

  // Initialize some variables during the first pass through.
  if (!isset($sandbox['total'])) {
    $max = \Drupal::entityQuery('media')
      ->condition('bundle', $bundle)
      ->count()
      ->execute();
    $sandbox['total'] = (int) $max;
    $sandbox['current'] = 0;
  }

  if ($sandbox['total'] === 0) {
    $sandbox['#finished'] = 1;

    return t('@current media @bundle processed.', [
      '@current' => $sandbox['current'],
      '@bundle'  => $bundle,
    ]);
  }

  $batchSize = 50;

  // Handle one pass through.
  $ids = \Drupal::entityQuery('media')
    ->condition('bundle', $bundle)
    ->range($sandbox['current'], $batchSize)
    ->execute();
  $medias = Media::loadMultiple($ids);
  foreach ($medias as $media) {
    /**
     * @var $media Media
     */

    if ($media->hasField($oldFieldName)) {
      $mediaDate = (new DrupalDateTime($media->get($oldFieldName)->value))
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $mediaCreatedDate = DrupalDateTime::createFromTimestamp($media->get('created')->value)
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      if ($media->get($newFieldName)->isEmpty()) {
        $mediaPublishedDate = $media->get($oldFieldName)->value === NULL ? $mediaCreatedDate : $mediaDate;
        $media->set($newFieldName, $mediaPublishedDate);
        $media->set($oldFieldName, NULL);
        $media->save();
      }
    }
    else {
      $mediaCreatedDate = DrupalDateTime::createFromTimestamp($media->get('created')->value)
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      if ($media->get($newFieldName)->isEmpty()) {
        $media->set($newFieldName, $mediaCreatedDate);
        $media->save();
      }
    }
    $sandbox['current']++;
  }

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  if ($sandbox['#finished'] === 1) {
    $fieldConfig = FieldConfig::loadByName('media', $bundle, $oldFieldName);
    if ($fieldConfig) {
      $fieldConfig->delete();
    }
  }

  return t('@current media @bundle processed.', [
    '@current' => $sandbox['current'],
    '@bundle'  => $bundle,
  ]);
}
