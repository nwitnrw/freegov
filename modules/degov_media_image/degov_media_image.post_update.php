<?php

/**
 * @file
 * Media image module.
 */

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\Media;

/**
 * Migrate field_image_date to field_media_publish_date.
 */
function degov_media_image_post_update_migrate_field_date(&$sandbox) {

  $oldFieldName = 'field_image_date';
  $newFieldName = 'field_media_publish_date';

  // Initialize some variables during the first pass through.
  if (!isset($sandbox['total'])) {
    $max = \Drupal::entityQuery('media')
      ->condition('bundle', 'image')
      ->count()
      ->execute();
    $sandbox['total'] = (int) $max;
    $sandbox['current'] = 0;
  }

  if ($sandbox['total'] === 0) {
    $sandbox['#finished'] = 1;

    return t('@current media @bundle processed.', [
      '@current' => $sandbox['current'],
      '@bundle'  => 'image',
    ]);
  }

  $batchSize = 50;

  // Handle one pass through.
  $ids = \Drupal::entityQuery('media')
    ->condition('bundle', 'image')
    ->range($sandbox['current'], $batchSize)
    ->execute();
  $medias = Media::loadMultiple($ids);
  foreach ($medias as $media) {
    /**
     * @var $media Media
     */

    if ($media->hasField($oldFieldName)) {
      $imageDate = (new DrupalDateTime($media->get($oldFieldName)->value))
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $imageCreatedDate = DrupalDateTime::createFromTimestamp($media->get('created')->value)
        ->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      if ($media->get($newFieldName)->isEmpty()) {
        $imagePublishedDate = $media->get($oldFieldName)->value === NULL ? $imageCreatedDate : $imageDate;
        $media->set($newFieldName, $imagePublishedDate);
        $media->set($oldFieldName, NULL);
        $media->save();
      }
    }
    $sandbox['current']++;
  }

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  if ($sandbox['#finished'] === 1) {
    $fieldConfig = FieldConfig::loadByName('media', 'image', $oldFieldName);
    if ($fieldConfig) {
      $fieldConfig->delete();
    }
  }

  return t('@current media processed.', ['@current' => $sandbox['current']]);
}
