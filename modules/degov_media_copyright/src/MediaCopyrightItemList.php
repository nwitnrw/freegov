<?php

declare(strict_types=1);

namespace Drupal\degov_media_copyright;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * @see \Drupal\degov_media_copyright\Plugin\Field\FieldType\MediaCopyright
 */
class MediaCopyrightItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * Computes the calculated values for this item list.
   *
   * There is only a single item/delta for this field.
   *
   * The ComputedItemListTrait only calls this once on the same instance; from
   * then on, the value is automatically cached in $this->items, for use by
   * methods like getValue().
   */
  protected function ensurePopulated() {
    if (!isset($this->list[0])) {
      $this->list[0] = $this->createItem(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $this->list[0] = $this->createItem();
  }

}
