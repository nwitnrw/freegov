<?php

namespace Drupal\degov_theme\Preprocess;

use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;

/**
 * Class MediaSearch.
 *
 * @package Drupal\degov_theme\Preprocess
 */
class MediaSearch {

  /**
   * Preprocess media for media search result list.
   *
   * @param array $variables
   *   Variables.
   */
  public static function preprocess(array &$variables) {
    if ($variables['view_mode'] === 'search') {
      /** @var \Drupal\media\Entity\Media $media */
      $media = $variables['media'];
      $img = [
        '#theme' => 'image_style',
        '#style_name' => 'teaser_squared_1_1_320',
      ];

      switch ($media->bundle()) {

        case 'image':
          if ($media->get('image')->entity instanceof File) {
            $img['#uri'] = $media->image->entity->getFileUri();
            $img['#alt'] = $media->image->alt;
            $img['#title'] = $media->image->title;
            $variables['teaser_image'] = $img;
          }
          break;

        case 'audio':
          if (self::itemListContainsFileEntityInstance($media->get('field_audio_preview'))) {
            $img['#uri'] = $media->get('field_audio_preview')->entity->get('image')->entity->getFileUri();
            $img['#alt'] = $media->get('field_audio_preview')->entity->get('image')->alt;
            $img['#title'] = $media->get('field_audio_preview')->entity->get('image')->title;
            $variables['teaser_image'] = $img;
          }
          break;

        case 'video_upload':
          if (self::itemListContainsFileEntityInstance($media->get('field_video_upload_preview'))) {
            $img['#uri'] = $media->get('field_video_upload_preview')->entity->get('image')->entity->getFileUri();
            $img['#alt'] = $media->get('field_video_upload_preview')->entity->get('image')->alt;
            $img['#title'] = $media->get('field_video_upload_preview')->entity->get('image')->title;
            $variables['teaser_image'] = $img;
          }
          break;

        case 'video':
          if (self::itemListContainsFileEntityInstance($media->get('field_video_preview'))) {
            $img['#uri'] = $media->get('field_video_preview')->entity->get('image')->entity->getFileUri();
            $img['#alt'] = $media->get('field_video_preview')->entity->get('image')->alt;
            $img['#title'] = $media->get('field_video_preview')->entity->get('image')->title;
            $variables['teaser_image'] = $img;
          }
          break;

        case 'gallery':
          if (self::itemListContainsFileEntityInstance($media->get('field_gallery_images'))) {
            $img['#uri'] = $media->get('field_gallery_images')->entity->get('image')->entity->getFileUri();
            $img['#alt'] = $media->get('field_gallery_images')->entity->get('image')->alt;
            $img['#title'] = $media->get('field_gallery_images')->entity->get('image')->title;
            $variables['teaser_image'] = $img;
          }
          break;

        case 'person':
          if (self::itemListContainsFileEntityInstance($media->get('field_person_image'))) {
            $img['#uri'] = $media->get('field_person_image')->entity->get('image')->entity->getFileUri();
            $img['#alt'] = $media->get('field_person_image')->entity->get('image')->alt;
            $img['#title'] = $media->get('field_person_image')->entity->get('image')->title;
            $variables['teaser_image'] = $img;
          }
          break;

        default:
          break;
      }
    }
  }

  /**
   * Item list contains file entity instance.
   */
  private static function itemListContainsFileEntityInstance($parentEntity): bool {
    if (!$parentEntity->isEmpty() && $parentEntity->entity->get('image') instanceof FileFieldItemList && $parentEntity->entity->get('image')->entity instanceof File) {
      return TRUE;
    }

    return FALSE;
  }

}
