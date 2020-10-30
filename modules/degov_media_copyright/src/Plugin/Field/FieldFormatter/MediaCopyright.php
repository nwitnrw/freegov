<?php

declare(strict_types=1);

namespace Drupal\degov_media_copyright\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_copyright' formatter.
 *
 * @FieldFormatter(
 *   id = "media-copyright-formatter",
 *   label = @Translation("Caption and copyright"),
 *   field_types = {
 *     "media_copyright",
 *     "field_media_copyright"
 *   }
 * )
 */
class MediaCopyright extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    foreach ($items as $i => $item) {
      if ($item->getValue()) {
        $values = $item->getValue();
        if (isset($values['caption']) || isset($values['copyright'])) {
          $elements[$i] = [
            '#theme' => 'field_media_copyright',
            '#caption' => isset($values['caption']) ? $values['caption'] : NULL,
            '#copyright' => isset($values['copyright']) ? $values['copyright'] : NULL,
            '#media_type' => isset($values['media_type']) ? $values['media_type'] : NULL,
            '#attributes' => [],
          ];
        }
        else {
          $elements[$i] = NULL;
        }
      }
    }
    return $elements;
  }

}
