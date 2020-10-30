<?php

namespace Drupal\degov_demo_content\FileHandler;

use Drupal\degov_demo_content\Generator\ContentGenerator;

/**
 * Class MediaFileHandler.
 */
class MediaFileHandler extends FileHandler {

  /**
   * MediaFileHandler constructor.
   *
   * @param array $mediaToGenerate
   * @param string $fixturesPath
   */
  public function saveFiles(array $mediaToGenerate, string $fixturesPath): void {
    foreach ($mediaToGenerate as $mediaItemKey => $mediaItem) {
      if (isset($mediaItem['file'])) {
        if ($savedFile = $this->fileAdapter->fileSaveData(
          $this->fileAdapter->fileGetContents($fixturesPath . '/' . $mediaItem['file']),
          ContentGenerator::DEGOV_DEMO_CONTENT_FILES_SAVE_PATH . '/' . $mediaItem['file'])
        ) {
          $this->addFile($savedFile, $mediaItemKey);
        }
      }

      if (isset($mediaItem['files'])) {
        foreach ($mediaItem['files'] as $fieldName => $fileName) {
          if ($savedFile = $this->fileAdapter->fileSaveData(
            $this->fileAdapter->fileGetContents($fixturesPath . '/' . $fileName),
            ContentGenerator::DEGOV_DEMO_CONTENT_FILES_SAVE_PATH . '/' . $fileName)
          ) {
            $this->addToFiles($savedFile, $mediaItemKey, $fieldName);
          }
        }

      }
    }
  }

  /**
   * Map file fields.
   *
   * @param array &$media_item
   *   Media item reference.
   * @param string $customMediaKey
   *   Custom media key.
   *
   * @return array
   *   Fields.
   */
  public function mapFileFields(array &$media_item, string $customMediaKey): array {
    $fields = [];
    foreach ($media_item as $media_item_field_key => $media_item_field_value) {
      if ($media_item_field_key === 'file' && $this->getFile($customMediaKey) !== NULL) {
        switch ($media_item['bundle']) {
          case 'image':
            $fields['image'] = [
              'target_id' => $this->getFile($customMediaKey)->id(),
              'alt'       => $media_item['name'],
              'title'     => $media_item['name'],
            ];
            break;

          case 'document':
            $fields['field_document'] = [
              'target_id' => $this->getFile($customMediaKey)->id(),
            ];
            break;

          case 'audio':
            $fields['field_audio_mp3'] = [
              'target_id' => $this->getFile($customMediaKey)->id(),
            ];
            break;

          case 'video_upload':
            $fields['field_video_upload_mp4'] = [
              'target_id' => $this->getFile($customMediaKey)->id(),
            ];
            break;
        }
        continue;
      }
      elseif ($media_item_field_key === 'files' && $this->getFiles($customMediaKey) !== NULL) {
        foreach ($media_item_field_value as $fileFieldName => $fileName) {
          switch ($fileFieldName) {
            case 'field_fullhd_video_mobile_mp4':
              $fields['field_fullhd_video_mobile_mp4'] = [
                'target_id' => $this->getFiles($customMediaKey)['field_fullhd_video_mobile_mp4']->id(),
              ];
              break;

            case 'field_hdready_video_mobile_mp4':
              $fields['field_hdready_video_mobile_mp4'] = [
                'target_id' => $this->getFiles($customMediaKey)['field_hdready_video_mobile_mp4']->id(),
              ];
              break;

            case 'field_mobile_video_mobile_mp4':
              $fields['field_mobile_video_mobile_mp4'] = [
                'target_id' => $this->getFiles($customMediaKey)['field_mobile_video_mobile_mp4']->id(),
              ];
              break;

            case 'field_video_mobile_mp4':
              $fields['field_video_mobile_mp4'] = [
                'target_id' => $this->getFiles($customMediaKey)['field_video_mobile_mp4']->id(),
              ];
              break;

            case 'field_ultrahd4k_video_mobile_mp4':
              $fields['field_ultrahd4k_video_mobile_mp4'] = [
                'target_id' => $this->getFiles($customMediaKey)['field_ultrahd4k_video_mobile_mp4']->id(),
              ];
              break;
          }
        }

      }
      else {
        $fields[$media_item_field_key] = $media_item_field_value;
      }

    }

    return $fields;
  }

}
