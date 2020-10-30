<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\media\MediaInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class MediaUsageInfo.
 *
 * @package Drupal\degov_media_usage\Service
 */
final class MediaUsageInfo {

  use StringTranslationTrait;

  /**
   * The Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The EntityTypeManagerInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The EntityTypeBundleInfoInterface.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private $bundleInfo;

  /**
   * MediaUsageInfo constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The Connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManagerInterface.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   The EntityTypeBundleInfoInterface.
   */
  public function __construct(
    Connection $database,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeBundleInfoInterface $bundleInfo
  ) {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
  }

  /**
   * Count the references to a given Media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The Media entity to count references to.
   *
   * @return int
   *   The count.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRefsCount(MediaInterface $media): int {
    $refs = $this->getRefsList($media);
    return \count($refs);
  }

  /**
   * Get an array of references for a given Media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The Media entity to get references for.
   * @param bool $excludeOrphaned
   *   Should we exclude orphaned paragraphs?
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|bool
   *   An array of Entities or FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRefsList(MediaInterface $media, $excludeOrphaned = TRUE): array {
    $refs = [];
    $query = $this->database->query(
      'SELECT DISTINCT mu.eid, mu.entity_type, mu.bundle_name FROM {degov_media_usage} mu WHERE mu.mid = :mid',
      [':mid' => $media->id()]
    );

    $results = $query->fetchAll();
    foreach ($results as $result) {
      $ref = $this->entityTypeManager
        ->getStorage($result->entity_type)
        ->load($result->eid);
      if ($excludeOrphaned && $ref instanceof ParagraphInterface && $this->isOrphaned($ref)) {
        continue;
      }
      $refs[] = $ref;
    }

    return $refs;
  }

  /**
   * Build a table from an array of references.
   *
   * @param array $refs
   *   The references to include in the table.
   *
   * @return array
   *   A build array for the table.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildRefsTable(array $refs): array {
    $rows = [];

    foreach ($refs as $ref) {
      $rows[] = [
        $ref->hasLinkTemplate('canonical')
        ? $ref->toLink($ref->label(), 'canonical')
        : $this->getLabel($ref),
        $ref->getEntityType()->getLabel(),
        $this->bundleInfo->getBundleInfo(
          $ref->getEntityType()->id()
        )[$ref->bundle()]['label'],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Reference'),
        $this->t('Entity type'),
        $this->t('Bundle name'),
      ],
      '#rows' => $rows,
    ];
  }

  /**
   * Check if a paragraph entity is orphaned.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph to check.
   *
   * @return bool
   *   Is the paragraph orphaned?
   */
  private function isOrphaned(ParagraphInterface $paragraph): bool {
    return !(($parent = $paragraph->getParentEntity()) && $parent->hasField($paragraph->get('parent_field_name')->value));
  }

  /**
   * Get the label for a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph.
   *
   * @return \Drupal\Component\Render\FormattableMarkup|\Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   The finished label.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function getLabel(ParagraphInterface $paragraph) {
    if (!$this->isOrphaned($paragraph)) {
      /** @var \Drupal\paragraphs\ParagraphInterface $parent */
      $parent = $paragraph->getParentEntity();
      $parentField = $paragraph->get('parent_field_name')->value;
      $field = $parent->get($parentField);
      $found = FALSE;

      foreach ($field as $key => $value) {
        if ($value->entity->id() === $paragraph->id()) {
          $found = TRUE;
          break;
        }
      }

      if ($found) {
        /** @var \Drupal\paragraphs\ParagraphInterface $parent */
        if ($parent->hasLinkTemplate('canonical')) {
          $label = $this->labelWithLink($parent, $field);
        }
        else {
          $label = $this->getLabel($parent) . ' > ' . $field->getFieldDefinition()->getLabel();
        }
      }
      else {
        // A previous or draft revision or a deleted stale Paragraph.
        if ($parent->hasLinkTemplate('canonical')) {
          $label = $this->labelWithLink($parent, $field, '(previous revision)');
        }
        else {
          $label = $this->getLabel($parent) . ' > ' . $field->getFieldDefinition()->getLabel() . ' (previous revision)';
        }
      }
    }
    else {
      $paragraphSummary = $paragraph->getSummary();
      if ($paragraphSummary instanceof MarkupInterface) {
        $paragraphSummary = (string) $paragraphSummary;
      }
      $label = $this->t(
        'Orphaned @type: @summary',
        [
          '@summary' => Unicode::truncate(\strip_tags($paragraphSummary), 50, FALSE, TRUE),
          '@type' => $paragraph->get('type')->entity->label(),
        ]
      );
    }

    return $label;
  }

  /**
   * Output the markup for a linked label.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to link to.
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The specific field to link to.
   * @param string $additionalText
   *   Additional text to display.
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   *   The markup of the linked label.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function labelWithLink(EntityInterface $entity, FieldItemListInterface $field, string $additionalText = ''): FormattableMarkup {
    $string = '@label (@bundleLabel) > @fieldDefinition @additionalText';
    $arguments = [
      '@label' => $entity->label(),
      '@fieldDefinition' => $field->getFieldDefinition()->getLabel(),
      '@bundleLabel' => $entity->getEntityType()->getBundleLabel(),
      '@additionalText' => $additionalText,
    ];
    if ($entity->getEntityTypeId() === 'node') {
      $string = '<a href=":link">@label</a> > @fieldDefinition @additionalText';
      $arguments += [
        ':link' => $entity->toUrl()->toString(),
      ];
    }
    return new FormattableMarkup($string, $arguments);
  }

}
