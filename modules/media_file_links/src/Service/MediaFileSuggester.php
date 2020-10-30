<?php

declare(strict_types=1);

namespace Drupal\media_file_links\Service;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Class MediaFileLinkSuggester.
 *
 * Accepts a (partial) search string and finds matching Media by title and
 * filename.
 *
 * @package Drupal\media_file_links\Service
 */
final class MediaFileSuggester {

  /**
   * File field mapper.
   *
   * @var \Drupal\media_file_links\Service\MediaFileFieldMapper
   */
  private $fileFieldMapper;

  /**
   * File link resolver.
   *
   * @var \Drupal\media_file_links\Service\MediaFileLinkResolver
   */
  private $fileLinkResolver;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $mediaStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $fileStorage;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $entityTypeBundleInfo;

  /**
   * MediaFileSuggester constructor.
   *
   * @param \Drupal\media_file_links\Service\MediaFileFieldMapper $file_field_mapper
   *   Maps Media bundles to their primary file fields.
   * @param \Drupal\media_file_links\Service\MediaFileLinkResolver $file_link_resolver
   *   Accepts a Media entity ID and returns the primary file in the entity.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *    Manages entity type plugin definitions.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(MediaFileFieldMapper $file_field_mapper, MediaFileLinkResolver $file_link_resolver, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->fileFieldMapper = $file_field_mapper;
    $this->fileLinkResolver = $file_link_resolver;
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * Runs searches on Media titles and filenames, returns the merged results.
   *
   * @param string $search
   *   Search.
   * @param bool $returnJson
   *   Return JSON.
   *
   * @return array|string
   *   Search results.
   */
  public function findBySearchString(string $search, bool $returnJson = TRUE) {
    $results = array_merge($this->findBySearchInTitle($search), $this->findBySearchInFilename($search));
    $preparedResults = $this->prepareResults($results);

    if ($returnJson) {
      return json_encode($preparedResults);
    }

    return $preparedResults;
  }

  /**
   * Runs a plain search on Media titles.
   *
   * @param string $search
   *   Search string.
   *
   * @return array
   *   Results.
   */
  private function findBySearchInTitle(string $search): array {
    $mediaQuery = $this->mediaStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('bundle', $this->fileFieldMapper->getEnabledBundles(), 'IN')
      ->condition('name', $search, 'CONTAINS');
    $mediaIds = $mediaQuery->execute();
    if (\count($mediaIds) > 0) {
      return $this->mediaStorage->loadMultiple($mediaIds);
    }
    return [];
  }

  /**
   * Performs a search on file names and resolves the corresponding Media.
   *
   * @param string $search
   *   Search string.
   *
   * @return array
   *   Results.
   */
  private function findBySearchInFilename(string $search): array {
    $filesQuery = $this->fileStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('filename', $search, 'CONTAINS');
    $fileIds = $filesQuery->execute();

    if (empty($fileIds)) {
      return [];
    }

    $mediaQuery = $this->mediaStorage->getQuery();
    $fieldValueCombinationsGroup = $mediaQuery->orConditionGroup();
    foreach ($this->fileFieldMapper->getBundleFileFieldMappings() as $bundle => $fileField) {
      $fieldValueCombinationsGroup->condition($fileField, $fileIds, 'IN');
    }

    $mediaQuery->condition('status', NodeInterface::PUBLISHED)
      ->condition('bundle', $this->fileFieldMapper->getEnabledBundles(), 'IN')
      ->condition($fieldValueCombinationsGroup);
    $mediaIds = $mediaQuery->execute();
    if (\count($mediaIds) > 0) {
      return $this->mediaStorage->loadMultiple($mediaIds);
    }
    return [];
  }

  /**
   * Turns an array of search results into a json string.
   *
   * @param array $results
   *   Results.
   *
   * @return array
   *   Prepared results.
   */
  private function prepareResults(array $results): array {
    $preparedResults = [];
    $mediaBundles = $this->entityTypeBundleInfo
      ->getBundleInfo('media');
    if (\count($results) > 0) {
      foreach ($results as $entity) {
        $nameValue = $entity->get('name')->getValue();
        if (!empty($nameValue[0]['value'])) {
          $filename = $this->fileLinkResolver->getFileNameString((int) $entity->id());
          $iconClass = $this->getIconClassForFile($filename);
          $preparedResults[] = [
            'id'          => $entity->id(),
            'title'       => $nameValue[0]['value'],
            'bundle'      => $entity->bundle(),
            'bundleLabel' => $mediaBundles[$entity->bundle()]['label'] ?? $entity->bundle(),
            'mimetype'    => $this->getFileTypeForEntity($entity),
            'iconClass'   => $iconClass,
            'filename'    => $filename,
          ];
        }
      }
      $preparedResults = array_unique($preparedResults, SORT_REGULAR);
    }
    return $preparedResults;
  }

  /**
   * Get icon class for file.
   */
  private function getIconClassForFile(string $filename): string {
    $iconClasses = [
      'fas fa-file-alt'        => ['doc', 'docx', 'odt'],
      'fas fa-file-excel'      => ['xls', 'xlsx', 'csv', 'ods'],
      'fas fa-file-powerpoint' => ['ppt', 'pptx', 'odp'],
      'fas fa-file-pdf'        => ['pdf'],
    ];

    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    foreach ($iconClasses as $iconClass => $extensions) {
      if (\in_array($extension, $extensions, TRUE)) {
        return $iconClass;
      }
    }

    return '';
  }

  /**
   * Accepts a Media entity and returns the mime type of the primary file.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The Media entity to retrieve the mime type from.
   *
   * @return string
   *   The mime type of the entity's primary file.
   */
  private function getFileTypeForEntity(MediaInterface $media): string {
    $fileField = $this->fileFieldMapper->getFileFieldForBundle($media->bundle());
    if (!empty($fileField)) {
      $value = $media->get($fileField)->getValue();
      if (isset($value[0]['target_id'])) {
        /** @var \Drupal\file\FileInterface $file */
        $file = $this->fileStorage->load($value[0]['target_id']);
        return $file->getMimeType();
      }
    }
    return '';
  }

}
