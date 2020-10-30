<?php

namespace Drupal\media_file_links\Service;

/**
 * Class MediaFileLinkPlaceholderHandler.
 *
 * @package Drupal\media_file_links\Service
 */
class MediaFileLinkPlaceholderHandler {

  /**
   * Is valid media file link placeholder.
   */
  public function isValidMediaFileLinkPlaceholder(string $placeholder): bool {
    return $this->getMediaIdFromPlaceholder($placeholder) !== NULL;
  }

  /**
   * Get media id from placeholder.
   */
  public function getMediaIdFromPlaceholder(string $placeholder): ?int {
    if (preg_match('/[\[<]media\/file\/([\d]+)[\]>]/', $placeholder, $matches) && !empty($matches[1]) && preg_match('/^[\d]+$/', $matches[1])) {
      return $matches[1];
    }

    return NULL;
  }

  /**
   * Get placeholder for media ID.
   */
  public function getPlaceholderForMediaId(int $mediaId, string $usage = 'node'): ?string {
    switch ($usage) {
      case 'node':
      case 'paragraph':
        return sprintf('[media/file/%s]', $mediaId);

      case 'menu_link_content':
        return sprintf('<media/file/%s>', $mediaId);
    }
    return NULL;
  }

}
