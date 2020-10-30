<?php

namespace Drupal\degov_media_document\Helper;

use Drupal\file\FileInterface;

/**
 * Class PlaceholderHelper.
 *
 * @package Drupal\degov_media_document\Helper
 */
class DocumentLinkHelper {

  /**
   * Map file to font  awesome icon.
   *
   * @param \Drupal\file\FileInterface $file
   *   File.
   *
   * @return string
   *   Icon class.
   */
  public static function mapFileToFaIcon(FileInterface $file): string {
    $extension = self::getExtensionForFile($file);
    switch ($extension) {
      case 'doc':
      case 'docx':
      case 'odt':
        return 'fa fa-file-word-o';

      case 'xls':
      case 'xlsx':
      case 'csv':
      case 'ods':
        return 'fa fa-file-excel-o';

      case 'ppt':
      case 'pptx':
      case 'odp':
        return 'fa fa-file-powerpoint-o';

      case 'pdf':
        return 'fa fa-file-pdf-o';

      default:
        return 'fa fa-file';
    }
  }

  /**
   * Determine what target attribute value to use for a file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   *
   * @return string
   *   The target value.
   */
  public static function getTargetAttributeForFile(FileInterface $file): string {
    $extension = self::getExtensionForFile($file);
    switch ($extension) {
      case 'pdf':
        return '_blank';

      default:
        return '';
    }
  }

  /**
   * Get the extension for a file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   *
   * @return string
   *   The file extension.
   */
  private static function getExtensionForFile(FileInterface $file): string {
    return pathinfo($file->getFilename(), PATHINFO_EXTENSION);
  }

}
