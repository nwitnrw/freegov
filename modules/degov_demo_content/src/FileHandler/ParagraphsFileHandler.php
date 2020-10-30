<?php

namespace Drupal\degov_demo_content\FileHandler;

/**
 * Class ParagraphsFileHandler
 */
class ParagraphsFileHandler extends FileHandler {

  /**
   * Map file fields.
   *
   * @param array &$item
   *   Media item reference.
   * @param string $customEntityKey
   *   Custom entity key.
   *
   * @return array
   *   Fields.
   */
  public function mapFileFields(array &$item, string $customEntityKey): array {
    $fields = [];
    foreach ($item as $item_field_key => $item_field_value) {
      if ($item_field_key === 'file' && $this->getFile($customEntityKey) !== NULL) {
        switch ($item['type']) {
          case 'video_subtitle':
            $fields['field_subtitle_file'] = [
              'target_id' => $this->getFile($customEntityKey)->id(),
            ];
            break;
        }
        continue;
      }

      $fields[$item_field_key] = $item_field_value;
    }

    return $fields;
  }

}
