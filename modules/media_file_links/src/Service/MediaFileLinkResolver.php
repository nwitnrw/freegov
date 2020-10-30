<?php

namespace Drupal\media_file_links\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MediaFileLinkResolver.
 *
 * Accepts a Media entity ID and returns the primary file in the entity.
 *
 * @package Drupal\media_file_links\Service
 */
class MediaFileLinkResolver {
  use StringTranslationTrait;
  /**
   * File field mapper.
   *
   * @var \Drupal\media_file_links\Service\MediaFileFieldMapper
   */
  private $fileFieldMapper;

  /** @var \Drupal\media\MediaStorage*/
  private $mediaStorage;

  /** @var \Drupal\file\FileStorageInterface*/
  private $fileStorage;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * MediaFileLinkResolver constructor.
   *
   * @param \Drupal\media_file_links\Service\MediaFileFieldMapper $fileFieldMapper
   *   File field mapper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Manages entity type plugin definitions.
   */
  public function __construct(MediaFileFieldMapper $fileFieldMapper, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->fileFieldMapper = $fileFieldMapper;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->logger = $logger;
  }

  /**
   * Accepts the id of a Media entity, returns the primary file URL.
   *
   * @param int $mediaId
   *   Media ID.
   *
   * @return string
   *   File url string.
   */
  public function getFileUrlString(int $mediaId): string {
    $file = $this->getFileForMedia($mediaId);
    if ($file instanceof FileInterface) {
      $uri = $file->getFileUri();
      return Url::fromUri(file_create_url($uri))->toString();
    }

    $this->logger->warning($this->t('Requested file for Media ID %id could not be found.', ['%id' => $mediaId]));
    return '';
  }

  /**
   * Get file name string.
   *
   * @param int $mediaId
   *   Media ID.
   *
   * @return string
   *   File name string.
   */
  public function getFileNameString(int $mediaId): string {
    $file = $this->getFileForMedia($mediaId);

    if ($file instanceof FileInterface) {
      return $file->getFilename();
    }

    $this->logger->warning($this->t('Requested file for Media ID %id could not be found.', ['%id' => $mediaId]));
    return '';
  }

  /**
   * Accepts a Media id and returns the primary file of the entity.
   *
   * @param int $mediaId
   *   Media ID.
   *
   * @return \Drupal\file\FileInterface|null
   *   File.
   */
  private function getFileForMedia(int $mediaId): ?FileInterface {
    $media = $this->mediaStorage->load($mediaId);
    if ($media instanceof MediaInterface) {
      $mediaBundle = $media->bundle();
      $fileFieldName = $this->fileFieldMapper->getFileFieldForBundle($mediaBundle);
      if (!empty($fileFieldName)) {
        $value = $media->get($fileFieldName)->getValue();
        if (isset($value[0]['target_id'])) {
          /** @var \Drupal\file\FileInterface $file */
          $file = $this->fileStorage->load($value[0]['target_id']);
          return $file;
        }
      }
    }
    return NULL;
  }

}
