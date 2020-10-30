<?php

declare(strict_types=1);

namespace Drupal\degov_auto_crop\Service;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\crop\CropTypeInterface;
use Drupal\file\FileInterface;

/**
 * Class AutoCropper.
 *
 * Provides functions to automatically apply image crops to given files.
 *
 * @package Drupal\degov_auto_crop\Service
 */
class AutoCropper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Constructs a new AutoCropper.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   Image factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystemInterface $file_system, LoggerChannelFactoryInterface $logger, ImageFactory $imageFactory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->logger = $logger;
    $this->imageFactory = $imageFactory;
  }

  /**
   * Apply all defined crop types to all image files in our definitions.
   *
   * @param \Drupal\file\FileInterface $file
   *   The File entity the image crops should be applied to.
   *
   * @return array
   *   An array of the crops generated.
   */
  public function applyImageCrops(FileInterface $file): array {
    $crops = [];
    try {
      $crop_types = $this->entityTypeManager
        ->getStorage('crop_type')
        ->loadMultiple();
      if (preg_match("/^image\//", $file->getMimeType())) {
        /** @var \Drupal\crop\Entity\CropType $crop_type */
        foreach ($crop_types as $crop_type) {
          try {
            $file_realpath = $this->fileSystem->realpath($file->getFileUri());
            if ($file_realpath === FALSE || !file_exists($file_realpath)) {
              continue;
            }
            $image = $this->imageFactory->get($file_realpath);
            $image_dimensions = [
              $image->getWidth(),
              $image->getHeight(),
            ];
            /** @var \Drupal\crop\Entity\Crop $crop */
            $crop = $this->entityTypeManager
              ->getStorage('crop')
              ->loadByProperties([
                'type' => $crop_type->id(),
                'uri'  => $file->getFileUri(),
              ]);
            $crop_dimensions = $this->calculateCropDimensions($crop_type, $image_dimensions);

            if (empty($crop)) {
              $crop_values = [
                'type'        => $crop_type->id(),
                'entity_id'   => $file->id(),
                'entity_type' => 'file',
                'uri'         => $file->getFileUri(),
                'x'           => $crop_dimensions['x'],
                'y'           => $crop_dimensions['y'],
              ];
              $crop_values += $crop_dimensions;
              $crop = $this->entityTypeManager
                ->getStorage('crop')
                ->create($crop_values);
            }
            else {
              $crop = reset($crop);
              $crop->set('x', $crop_dimensions['x']);
              $crop->set('y', $crop_dimensions['y']);
              $crop->set('width', $crop_dimensions['width']);
              $crop->set('height', $crop_dimensions['height']);
            }
            $crops[] = $crop;
            $crop->save();
          }
          catch (PluginNotFoundException | InvalidPluginDefinitionException | EntityStorageException $exception) {
            // Crop not found or crop save failed. Log and continue.
            $this->logger->get('degov_auto_crop')
              ->error($exception->getMessage());
          }
        }
        return $crops;
      }
    }
    catch (PluginNotFoundException | InvalidPluginDefinitionException $exception) {
      // No crop types found. Just log, otherwise do nothing.
      $this->logger->get('degov_auto_crop')->error($exception->getMessage());
    }
    return $crops;
  }

  /**
   * Calculate the height and width of the crop frame to be applied.
   *
   * @param \Drupal\crop\CropTypeInterface $crop_type
   *   The Crop crop_type we want to apply.
   * @param array $image_dimensions
   *   The actual width and height of the image file.
   *
   * @return array
   *   The desired height and width of the image.
   */
  public function calculateCropDimensions(CropTypeInterface $crop_type, array $image_dimensions): array {
    $crop_dimensions = [
      'height' => 0,
      'width'  => 0,
      'x'      => 0,
      'y'      => 0,
    ];
    $aspect_ratio_fragments = explode(':', $crop_type->aspect_ratio);
    if (\count($aspect_ratio_fragments) !== 2) {
      $aspect_ratio_fragments = [1, 1];
    }

    $orientation = 'square';
    if ($image_dimensions[0] > $image_dimensions[1]) {
      $orientation = 'landscape';
    }
    else {
      if ($image_dimensions[0] < $image_dimensions[1]) {
        $orientation = 'portrait';
      }
    }

    switch ($orientation) {
      case 'landscape':
        // Landscape orientation.
        $crop_dimensions['height'] = $image_dimensions[1] * $this->calculateScaleFactor($image_dimensions, $aspect_ratio_fragments);
        $crop_dimensions['width'] = (($image_dimensions[1] / $aspect_ratio_fragments[1]) * $aspect_ratio_fragments[0]) * $this->calculateScaleFactor($image_dimensions, $aspect_ratio_fragments);
        break;

      case 'portrait':
      case 'square':
        // Portrait and square orientation.
        $crop_dimensions['height'] = (($image_dimensions[0] / $aspect_ratio_fragments[0]) * $aspect_ratio_fragments[1]) * $this->calculateScaleFactor($image_dimensions, $aspect_ratio_fragments);
        $crop_dimensions['width'] = $image_dimensions[0] * $this->calculateScaleFactor($image_dimensions, $aspect_ratio_fragments);
        break;

    }

    $offsets = [
      'left'   => 1,
      'right'  => 1,
      'top'    => 1,
      'bottom' => 1,
    ];
    $stored_offsets = $crop_type->getThirdPartySetting('degov_auto_crop', 'offsets');
    if (!empty($stored_offsets) && !empty($stored_offsets[$orientation])) {
      $offsets = $stored_offsets[$orientation];
    }

    $this->calculateCropCenterOffsets($offsets, $image_dimensions, $crop_dimensions);

    return $crop_dimensions;
  }

  /**
   * Calculates a scale factor for the crop frame.
   *
   * @param array $image_dimensions
   *   The dimensions of the image.
   * @param array $aspect_ratio_fragments
   *   The aspect ratio of the crop type.
   *
   * @return float|int
   *   The factor to scale the image by.
   */
  public function calculateScaleFactor(array $image_dimensions, array $aspect_ratio_fragments) {
    if ($image_dimensions[0] > $image_dimensions[1]) {
      $image_ratio = $image_dimensions[0] / $image_dimensions[1];
      $crop_ratio = $aspect_ratio_fragments[0] / $aspect_ratio_fragments[1];
    }
    else {
      $image_ratio = $image_dimensions[1] / $image_dimensions[0];
      $crop_ratio = $aspect_ratio_fragments[1] / $aspect_ratio_fragments[0];
    }
    if ($crop_ratio > $image_ratio) {
      return $image_ratio / $crop_ratio;
    }
    return 1;
  }

  /**
   * Calculates the crop center position based on offset values.
   *
   * @param array $offsets
   *   The array of offsets the calculation should be based on.
   * @param array $image_dimensions
   *   The dimensions of the current image.
   * @param array $crop_dimensions
   *   A reference to the dimensions array, will be populated with the offsets.
   */
  public function calculateCropCenterOffsets(array $offsets, array $image_dimensions, array &$crop_dimensions) {
    $crop_dimensions['x'] = $image_dimensions[0] / 2;
    $crop_dimensions['y'] = $image_dimensions[1] / 2;

    $remaining_space['vertical'] = $image_dimensions[1] - $crop_dimensions['height'];
    $remaining_space['horizontal'] = $image_dimensions[0] - $crop_dimensions['width'];

    if (!empty($offsets['left']) && !empty($offsets['right'])) {
      $offsets_sum = (int) $offsets['left'] + (int) $offsets['right'];
      $required_padding['left'] = ($remaining_space['horizontal'] / $offsets_sum) * (int) $offsets['left'];
      $required_padding['right'] = ($remaining_space['horizontal'] / $offsets_sum) * (int) $offsets['right'];
      $crop_dimensions['x'] += ($required_padding['left'] - $required_padding['right']) / 2;
    }

    if (!empty($offsets['top']) && !empty($offsets['bottom'])) {
      $offsets_sum = (int) $offsets['top'] + (int) $offsets['bottom'];
      $required_padding['top'] = ($remaining_space['vertical'] / $offsets_sum) * (int) $offsets['top'];
      $required_padding['bottom'] = ($remaining_space['vertical'] / $offsets_sum) * (int) $offsets['bottom'];
      $crop_dimensions['y'] += ($required_padding['top'] - $required_padding['bottom']) / 2;
    }
  }

}
