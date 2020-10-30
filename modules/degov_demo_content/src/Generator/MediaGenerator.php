<?php

declare(strict_types=1);

namespace Drupal\degov_demo_content\Generator;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\degov_demo_content\FileHandler\MediaFileHandler;
use Drupal\file\Entity\File;
use Drupal\geofield\WktGeneratorInterface;
use Drupal\media\Entity\Media;

/**
 * Class MediaGenerator.
 *
 * Generates Media entities from a YAML definition file.
 *
 * @package Drupal\degov_demo_content\Factory
 */
final class MediaGenerator extends ContentGenerator implements GeneratorInterface {

  /**
   * The entity type we are working with.
   *
   * @var string
   */
  protected $entityType = 'media';

  /**
   * The ids of the Media entities we have saved.
   *
   * @var array
   */
  private $savedEntities = [];

  /**
   * The Geofield WktGenerator.
   *
   * @var \Drupal\geofield\WktGenerator
   */
  protected $wktGenerator;

  /**
   * Media file handler.
   *
   * @var \Drupal\degov_demo_content\FileHandler\MediaFileHandler
   */
  private $mediaFileHandler;

  /**
   * @param \Drupal\geofield\WktGeneratorInterface $wktGenerator
   *   The Geofield WktGenerator service.
   */
  public function setWktGenerator(WktGeneratorInterface $wkt_generator): void {
    $this->wktGenerator = $wkt_generator;
  }

  /**
   * @param \Drupal\degov_demo_content\FileHandler\MediaFileHandler $media_file_handler
   *   Media file handler service.
   */
  public function setMediaFileHandler(MediaFileHandler $media_file_handler): void {
    $this->mediaFileHandler = $media_file_handler;
  }

  /**
   * Generates a set of media entities.
   */
  public function generateContent(): void {
    $media_to_generate = $this->loadDefinitions('media.yml');

    $this->mediaFileHandler->saveFiles($media_to_generate, $this->fixturesPath);
    $this->saveEntities($media_to_generate);
    $this->saveEntityReferences($media_to_generate);
  }

  /**
   * Deletes and regenerates all demo Media.
   */
  public function resetContent(): void {
    $this->deleteContent();
    $this->generateContent();
  }

  /**
   * Saves the defined Media entities.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function saveEntities($media_to_generate): void {
    // Create the Media entities.
    foreach ($media_to_generate as $media_item_key => $media_item) {
      $paragraphs = [];

      if (isset($media_item['field_video_upload_subtitle'])) {
        $paragraphs['field_video_upload_subtitle'] = $media_item['field_video_upload_subtitle'];
      }

      $paragraphs = array_filter($paragraphs);
      unset($media_item['field_video_upload_subtitle']);

      $this->generateParagraphs($paragraphs, $media_item);

      $this->prepareValues($media_item);
      $fields = $this->mediaFileHandler->mapFileFields($media_item, $media_item_key);
      $fields['field_title'] = $media_item['name'];
      $fields['status'] = $media_item['status'] ?? TRUE;
      $fields['field_tags'] = [
        ['target_id' => $this->getDemoContentTagId()],
      ];
      if (empty($media_item['field_royalty_free'])) {
        $fields['field_copyright'] = [
          'target_id' => $this->getDemoContentCopyrightId(),
        ];
      }

      if (!empty($media_item['field_address_location'])) {
        $fields['field_address_location'] = $this->wktGenerator->wktBuildPoint($media_item['field_address_location']);
      }
      // If no "created" date is defined in definitions, we  generate a unique
      // number with 5 digits based on $media_item_key (% digits are abozt a day in Unix time
      // 86400s->1 day) and add it to DEGOV_DEMO_CONTENT_CREATED_TIMESTAMP.
      // A manual date defined date should be  > DEGOV_DEMO_CONTENT_CREATED_TIMESTAMP + 100.000 to be stable.
      $fields['created'] = isset($media_item['created']) ? $media_item['created'] : self::getCreatedTimestamp($media_item_key);

      // Auto generate media publish date fields if not set.
      $fields['field_media_publish_date'] = isset($media_item['field_media_publish_date']) ? $media_item['field_media_publish_date'] : date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, self::getCreatedTimestamp($media_item_key));

      $new_media = Media::create($fields);
      $new_media->save();
      $this->savedEntities[$media_item_key] = $new_media;
    }
  }

  /**
   * Store references between Media entities, e.g. preview images.
   */
  private function saveEntityReferences($media_to_generate): void {
    // Create references between Media entities.
    foreach ($media_to_generate as $media_item_key => $media_item) {
      if (!empty($this->savedEntities[$media_item_key])) {
        $saved_entity = $this->savedEntities[$media_item_key];
        if (($media_item['bundle'] === 'gallery') && !empty($media_item['field_gallery_images'])) {
          $image_target_ids = [];
          foreach ($media_item['field_gallery_images'] as $image_key) {
            if (isset($this->savedEntities[$image_key])) {
              $image_target_ids[] = [
                'target_id' => $this->savedEntities[$image_key]->id(),
              ];
            }
          }
          $saved_entity->set('field_gallery_images', $image_target_ids);
          $saved_entity->save();

        }
      }
    }
  }

  /**
   * Deletes the generated entities.
   */
  public function deleteContent(): void {
    $query = $this->entityTypeManager->getStorage('file')->getQuery();
    $query->condition('uri', 'public://degov_demo_content/%', 'LIKE');
    $query_results = $query->execute();

    if (!empty($query_results)) {
      $file_entities = File::loadMultiple($query_results);

      foreach ($file_entities as $file_entity) {
        $file_entity->delete();
      }
    }
    parent::deleteContent();
  }

}
