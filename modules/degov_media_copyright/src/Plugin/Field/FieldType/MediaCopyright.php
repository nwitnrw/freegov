<?php

declare(strict_types=1);

namespace Drupal\degov_media_copyright\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * @FieldType(
 *   id = "media_copyright",
 *   label = @Translation("Copyright string"),
 *   description = @Translation("String showing aggregated copyright info."),
 *   default_formatter = "media-copyright-formatter",
 * )
 */
class MediaCopyright extends FieldItemBase {

  /**
   * Whether or not the value has been calculated.
   *
   * @var bool
   */
  protected $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  protected function ensureCalculated() {
    if (!$this->isCalculated) {
      // @var $entity \Drupal\media\Entity\Media
      $entity = $this->getEntity();
      if (!$entity->isNew()) {
        $this->setValue($this->createCopyright($entity));
      }
      $this->isCalculated = TRUE;
    }
  }

  /**
   * @param $media \Drupal\media\Entity\Media
   *
   * @return array
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function createCopyright($media) {
    $caption_field = 'field_' . $media->bundle() . '_caption';
    if ($media->hasField($caption_field) || $media->hasField('field_copyright')) {
      $copyright = NULL;
      if ($media->hasField('field_copyright') && $media->get('field_copyright')->entity) {
        $copyright = $media->get('field_copyright')->entity->get('name')->getString();
      }
      $caption = NULL;
      if ($media->hasField($caption_field) && $media->get($caption_field)->first()) {
        $caption = $media->get($caption_field)->first()->getString();
      }
      return [
        'caption' => $caption,
        'copyright' => $copyright,
        'media_type' => $media->bundle(),
      ];
    }
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // The properties are dynamic and can not be defined statically.
    return [];
  }

  /**
   * @inheritDoc
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    // The properties are dynamic and can not be defined statically.
    return [];
  }

}
