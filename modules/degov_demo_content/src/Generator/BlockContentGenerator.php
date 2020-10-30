<?php

declare(strict_types=1);

namespace Drupal\degov_demo_content\Generator;

use Drupal\block\Entity\Block;
use Drupal\block_content\BlockContentInterface;
use Drupal\block_content\Entity\BlockContent;

/**
 * Class BlockContentGenerator.
 *
 * @package Drupal\degov_demo_content\Generator
 */
final class BlockContentGenerator extends ContentGenerator implements GeneratorInterface {

  /**
   * Node title which contains slideshow paragraph.
   */
  const NODE_WITH_SLIDER_LABEL = "Page with slideshow";

  /**
   * The name of field which relates to the Paragraph in the node.
   */
  const NODE_PARAGRAPH_FIELD = 'field_header_paragraphs';

  /**
   * The name of field which relates to the Paragraph in the block.
   */
  const BLOCK_PARAGRAPH_FIELD = 'field_content_paragraphs';

  /**
   * The entity type we are working with.
   *
   * @var string
   */
  protected $entityType = 'block_content';

  /**
   * The type of block.
   *
   * @var string
   */
  protected $blockType = 'basic';

  /**
   * {@inheritdoc}
   */
  public function generateContent(): void {
    foreach ($this->loadDefinitions('block_content.yml') as $definition) {
      $block = $this->createContentBlock();
      $this->generateBlockReferenceParagraphs($block);
    }
    $this->enableParagraphField();
  }

  /**
   * Creates the ContentBlock entity.
   *
   * @param array $definition
   *   Definition array.
   *
   * @return \Drupal\block_content\BlockContentInterface
   *   Content block.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createContentBlock() {
    foreach ($this->loadDefinitions('block_content.yml') as $srcId => $rawBlock) {
      $paragraphs = [];
      if (isset($rawBlock['field_content_paragraphs'])) {
        $paragraphs['field_content_paragraphs'] = $rawBlock['field_content_paragraphs'];
      }
      $rawBlock['field_tags'] = [
        ['target_id' => $this->getDemoContentTagId()],
      ];

      $rawBlock['created'] = isset($rawBlock['created']) ? $rawBlock['created'] : $this->getCreatedTimestamp($srcId);

      $paragraphs = array_filter($paragraphs);
      unset($rawBlock['field_content_paragraphs']);
      $this->generateParagraphs($paragraphs, $rawBlock);
      $this->prepareValues($rawBlock);
    }
    $block = BlockContent::create($rawBlock);
    $block->save();
    $this->setBlockToRegion($block);
    return $block;
  }

  /**
   * Place block to the region.
   *
   * @param \Drupal\block_content\BlockContentInterface $block
   *   The Block entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function setBlockToRegion(BlockContentInterface $block): void {
    $placed_block = Block::create([
      'id' => 'slideshow',
      'theme' => 'degov_theme',
      'weight' => -50,
      'status' => TRUE,
      'region' => 'content',
      'plugin' => 'block_content:' . $block->uuid(),
      'settings' => [
        'id' => 'block_content:' . $block->uuid(),
        'label' => 'Slideshow',
        'provider' => 'block_content',
        'label_display' => '0',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [
        'request_path' => [
          'id' => 'request_path',
          'negate' => FALSE,
          'pages' => '<front>',
        ],
      ],
    ]);

    $placed_block->save();
  }

  /**
   * Generates reference to paragraph in the block field.
   *
   * @param \Drupal\block_content\BlockContentInterface $block_content
   *   BlockContent entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function generateBlockReferenceParagraphs(
    BlockContentInterface $block_content): void {
    $nodes_array = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => 'normal_page',
        'title' => self::NODE_WITH_SLIDER_LABEL,
      ]);

    /** @var \Drupal\node\NodeInterface $node */
    if ($node = reset($nodes_array)) {
      /** @var \Drupal\paragraphs\ParagraphInterface $paragraph_slideshow */
      $paragraph_slideshow = $node->get(self::NODE_PARAGRAPH_FIELD)->entity;

      $block_content->set(self::BLOCK_PARAGRAPH_FIELD, $paragraph_slideshow);
      $block_content->save();
    }
  }

  /**
   * Enable paragraph fields on view display & form display.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function enableParagraphField(): void {
    $this->enableParagraphFieldOnViewDisplay();
    $this->enableParagraphFieldOnFormDisplay();
  }

  /**
   * Enable paragraph field on view display.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function enableParagraphFieldOnViewDisplay(): void {
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $view_display */
    $view_display = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load("{$this->entityType}.{$this->blockType}.default");
    $view_display->setComponent(self::BLOCK_PARAGRAPH_FIELD, ['label' => 'hidden']);
    $view_display->save();
  }

  /**
   * Returns array of paragraph field for the form display.
   *
   * @return string<string,<string|int|array>>
   */
  private function getParagraphFieldFormDisplaySettings(): array {
    return [
      'type' => 'paragraphs',
      'weight' => 4,
      'region' => "content",
      'settings' => [
        'edit_mode' => 'closed',
        'closed_mode' => 'summary',
        'autocollapse' => 'none',
        'closed_mode_threshold' => 0,
        'add_mode' => 'modal',
        'form_display_mode' => 'default',
        'default_paragraph_type' => '_none',
        'features' => [
          'duplicate' => 'duplicate',
          'collapse_edit_all' => 'collapse_edit_all',
          'add_above' => 'add_above',
        ],
      ],
      'third_party_settings' => [],
    ];
  }

  /**
   * Enable paragraph field on the form display.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function enableParagraphFieldOnFormDisplay(): void {
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->entityTypeManager
      ->getStorage('entity_form_display')
      ->load("{$this->entityType}.{$this->blockType}.default");

    $form_display->setComponent(self::BLOCK_PARAGRAPH_FIELD,
      $this->getParagraphFieldFormDisplaySettings()
    );
    $form_display->save();
  }

  /**
   * Deletes the generated block.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteContent(): void {
    $entities = $this->entityTypeManager
      ->getStorage($this->entityType)
      ->loadMultiple();

    foreach ($entities as $entity) {
      $entity->delete();
    }
  }

  /**
   * Regenerates the block.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function resetContent(): void {
    $this->deleteContent();
    $this->generateContent();
  }

}
