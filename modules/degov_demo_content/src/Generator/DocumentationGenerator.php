<?php

namespace Drupal\degov_demo_content\Generator;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Class DocumentationGenerator.
 *
 * @package Drupal\degov_demo_content\Generator
 */
class DocumentationGenerator {

  use StringTranslationTrait;

  /**
   * The string containing the documentation we want to output.
   *
   * @var string
   */
  private $outputString;

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The FileSystem.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The AliasManager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  private $aliasManager;

  /**
   * Holds statistics of which Entity types we have documented.
   *
   * @var array
   */
  private $statistics;

  /**
   * DocumentationGenerator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The FileSystem.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The AliasManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, AliasManagerInterface $aliasManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->aliasManager = $aliasManager;
  }

  /**
   * Generate Markdown-formatted documentation of the demo content.
   *
   * @param string $outfile
   *   The file to write the output to.
   * @param bool $includeStatistics
   *   Should the document include a block of entity counts?
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function generateDocumentation(string $outfile, bool $includeStatistics = FALSE): void {
    $this->appendToOutputString('# deGov Demo Content');

    $this->prepareStatsArray();
    $this->documentNodes();
    $this->documentMedia();
    if ($includeStatistics === TRUE) {
      $this->outputStats();
    }

    $this->fileSystem->saveData($this->outputString, $outfile, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * Populates an array of all node, media, and paragraphs types with a counter variable.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function prepareStatsArray() {
    $nodeTypeStorage = $this->entityTypeManager->getStorage('node_type');
    $nodeTypes = $nodeTypeStorage->loadMultiple();
    foreach ($nodeTypes as $nodeType) {
      $this->statistics['node'][$nodeType->id()] = [
        'label' => $nodeType->label(),
        'count' => 0,
      ];
    }

    $mediaTypeStorage = $this->entityTypeManager->getStorage('media_type');
    $mediaTypes = $mediaTypeStorage->loadMultiple();
    foreach ($mediaTypes as $mediaType) {
      $this->statistics['media'][$mediaType->id()] = [
        'label' => $mediaType->label(),
        'count' => 0,
      ];
    }

    $paragraphsTypeStorage = $this->entityTypeManager->getStorage('paragraphs_type');
    $paragraphsTypes = $paragraphsTypeStorage->loadMultiple();
    foreach ($paragraphsTypes as $paragraphsType) {
      $this->statistics['paragraphs'][$paragraphsType->id()] = [
        'label' => $paragraphsType->label(),
        'count' => 0,
      ];
    }
  }

  /**
   * Outputs the generated stats.
   */
  private function outputStats(): void {
    $this->appendToOutputString('## ' . $this->t('Content types'));
    foreach ($this->statistics['node'] as $nodeType) {
      $this->appendToOutputString('* ' . $nodeType['label'] . ': ' . $nodeType['count'] . ' ' . $this->t('nodes'), 1);
    }
    $this->appendToOutputString('');

    $this->appendToOutputString('## ' . $this->t('Media'));
    foreach ($this->statistics['media'] as $mediaType) {
      $this->appendToOutputString('* ' . $mediaType['label'] . ': ' . $mediaType['count'] . ' ' . $this->t('media'), 1);
    }
    $this->appendToOutputString('');

    $this->appendToOutputString('## ' . $this->t('Paragraphs'));
    foreach ($this->statistics['paragraphs'] as $paragraphsType) {
      $this->appendToOutputString('* ' . $paragraphsType['label'] . ': ' . $paragraphsType['count'] . ' ' . $this->t('paragraphs'), 1);
    }
    $this->appendToOutputString('');
  }

  /**
   * Output all demo content Nodes as Markdown.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function documentNodes(): void {
    $this->appendToOutputString('## ' . $this->t('Content'));
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $nodeStorage->loadByProperties([
      'field_tags' => $this->getDemoContentTagId(),
    ]);

    foreach ($nodes as $node) {
      $this->appendToOutputString('### ' . $node->getTitle());
      $this->appendToOutputString('*' . $this->t('Content type') . '*' . ': ' . $this->statistics['node'][$node->bundle()]['label']);
      $this->appendToOutputString('*' . $this->t('Alias') . '*' . ': ' . $this->aliasManager
        ->getAliasByPath('/node/' . $node->id()));
      $this->formatParagraphsField($node, 'field_content_paragraphs');
      $this->formatParagraphsField($node, 'field_header_paragraphs');
      $this->formatParagraphsField($node, 'field_sidebar_right_paragraphs');
      $this->statistics['node'][$node->bundle()]['count']++;
    }
  }

  /**
   * Output all demo content Media as Markdown.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function documentMedia(): void {
    $this->appendToOutputString('## ' . $this->t('Media'));
    $mediaStorage = $this->entityTypeManager->getStorage('media');
    $media = $mediaStorage->loadByProperties([
      'field_tags' => $this->getDemoContentTagId(),
    ]);

    foreach ($media as $medium) {
      $this->appendToOutputString('### ' . $medium->getName());
      $this->appendToOutputString('*' . $this->t('Bundle') . '*: ' . $this->statistics['media'][$medium->bundle()]['label']);
      $this->appendToOutputString('*' . $this->t('Alias') . '*: ' . $this->aliasManager
        ->getAliasByPath('/media/' . $medium->id()));
      $this->statistics['media'][$medium->bundle()]['count']++;
    }
  }

  /**
   * Turn a paragraphs field of a node to Markdown.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Node.
   * @param string $fieldName
   *   The field we want to document.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function formatParagraphsField(NodeInterface $node, string $fieldName): void {
    if ($node->hasField($fieldName) && $node->get($fieldName)->count() > 0) {
      $this->appendToOutputString('#### ' . $node->get($fieldName)
        ->getFieldDefinition()
        ->getLabel());
      foreach ($node->get($fieldName) as $fieldValue) {
        $paragraph = Paragraph::load($fieldValue->getValue()['target_id']);
        $this->appendToOutputString('* ' . $this->statistics['paragraphs'][$paragraph->bundle()]['label'], 1);
        $this->statistics['paragraphs'][$paragraph->bundle()]['count']++;
      }
      $this->appendToOutputString('', 1);
    }
  }

  /**
   * Append a string to the output string, with optional trailing newlines.
   *
   * @param string $string
   *   The string to append.
   * @param int $newLinesAfter
   *   How many newlines should be appended afterwards?
   */
  private function appendToOutputString(string $string, int $newLinesAfter = 2): void {
    $this->outputString .= $string;
    for ($i = 0; $i < $newLinesAfter; $i++) {
      $this->outputString .= "\r\n";
    }
  }

  /**
   * Return the TID of the degov_demo_content taxonomy term.
   *
   * @return int|null
   *   The tid.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getDemoContentTagId(): ?int {
    $tag_term = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => DEGOV_DEMO_CONTENT_TAG_NAME,
        'vid'  => DEGOV_DEMO_CONTENT_TAGS_VOCABULARY_NAME,
      ]);
    if (!empty($tag_term)) {
      $tag_term = reset($tag_term);
      return $tag_term->id();
    }
    return NULL;
  }

}
