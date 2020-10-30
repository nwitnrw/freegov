<?php

namespace Drupal\media_file_links\Plugin\Field\FieldType;

use Drupal\Core\Url;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * Plugin implementation of the 'media_file_links' field type.
 *
 * @FieldType(
 *   id = "media_file_link",
 *   label = @Translation("Link"),
 *   description = @Translation("Stores a URL string, optional varchar link text, and optional blob of attributes to assemble a link."),
 *   default_widget = "link_mediafilelinks",
 *   default_formatter = "link",
 *   constraints = {
 *    "LinkType" = {},
 *    "LinkAccess" = {},
 *    "LinkExternalProtocols" = {},
 *    "LinkNotExistingInternal" = {}
 *   }
 * )
 */
class MediaFileLinkItem extends LinkItem {

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    $mediaId = \Drupal::service('media_file_links.placeholder_handler')->getMediaIdFromPlaceholder($this->uri);

    if ($mediaId !== NULL) {
      $fileUrl = \Drupal::service('media_file_links.file_link_resolver')
        ->getFileUrlString($mediaId);
      if (empty($fileUrl)) {
        return Url::fromRoute('<nolink>');
      }
      return Url::fromUri($fileUrl);
    }

    return Url::fromUri($this->uri, (array) $this->options);
  }

}
