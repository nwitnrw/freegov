<?php

namespace Drupal\degov_file_management\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\LinkBase;
use Drupal\views\ResultRow;

/**
 * Field handler to add a file delete link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("degov_file_management_file_delete_link")
 */
class FileDeleteLink extends LinkBase {

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    /** @var \Drupal\node\NodeInterface $node */
    $file = $this->getEntity($row);
    return Url::fromRoute(
      'degov_file_management.file_delete_confirm',
      [
        'fid' => $file->id(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel() {
    return $this->t('Delete');
  }

}
