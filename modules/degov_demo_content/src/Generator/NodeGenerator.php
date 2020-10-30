<?php

declare(strict_types=1);

namespace Drupal\degov_demo_content\Generator;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\pathauto\PathautoState;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class NodeGenerator.
 *
 * @package Drupal\degov_demo_content\Generator
 */
final class NodeGenerator extends ContentGenerator implements GeneratorInterface {

  /**
   * The alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * Configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The paragraphs file handler.
   *
   * @var \Drupal\degov_demo_content\FileHandler\ParagraphsFileHandler
   */
  protected $paragraphsFileHandler;

  /**
   * The entity type we are working with.
   *
   * @var string
   */
  protected $entityType = 'node';

  /**
   * @param \Drupal\pathauto\AliasCleanerInterface $alias_cleaner
   */
  public function setAliasCleaner(AliasCleanerInterface $alias_cleaner): void {
    $this->aliasCleaner = $alias_cleaner;
  }

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function setConfigFactory(ConfigFactoryInterface $configFactory): void {
    $this->configFactory = $configFactory;
  }

  /**
   * Generates content.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateContent(): void {
    $teaserPage = NULL;
    $nodeIds = [];

    // Attach Content reference Slides.
    $attachContentSlides = [
      'node_with_type1_slideshow' => TRUE,
      'node_with_type2_slideshow' => TRUE,
    ];

    foreach ($this->loadDefinitions('node.yml') as $srcId => $rawNode) {
      $paragraphs = [];

      if (isset($rawNode['field_content_paragraphs'])) {
        $paragraphs['field_content_paragraphs'] = $rawNode['field_content_paragraphs'];
      }
      if (isset($rawNode['field_header_paragraphs'])) {
        $paragraphs['field_header_paragraphs'] = $rawNode['field_header_paragraphs'];
      }
      if (isset($rawNode['field_sidebar_right_paragraphs'])) {
        $paragraphs['field_sidebar_right_paragraphs'] = $rawNode['field_sidebar_right_paragraphs'];
      }

      $rawNode['field_tags'] = [
        ['target_id' => $this->getDemoContentTagId()],
      ];

      $paragraphs = array_filter($paragraphs);
      unset($rawNode['field_content_paragraphs'], $rawNode['field_header_paragraphs'], $rawNode['field_sidebar_right_paragraphs']);

      $this->generateParagraphs($paragraphs, $rawNode);
      $this->prepareValues($rawNode);
      $rawNode['path'] = [
        'alias'    => '/degov-demo-content/' . $this->aliasCleaner->cleanString($rawNode['title']),
        'pathauto' => PathautoState::SKIP,
      ];
      // If no "created" date is defined in definitions, we  generate a unique
      // number with 5 digits based on $srcId (% digits are about a day in Unix time
      // 86400s->1 day) and add it to DEGOV_DEMO_CONTENT_CREATED_TIMESTAMP.
      // A manual date defined date should be  > DEGOV_DEMO_CONTENT_CREATED_TIMESTAMP + 100.000 to be stable.
      $rawNode['created'] = isset($rawNode['created']) ? $rawNode['created'] : self::getCreatedTimestamp($srcId);

      /** @var \Drupal\node\NodeInterface $node */
      $node = Node::create($rawNode);
      $node->save();

      // Use first node for teasers.
      if ($teaserPage === NULL) {
        $teaserPage = $node;
        $this->setFrontPage('/node/' . $teaserPage->id());
      }
      else {
        if ($rawNode['type'] !== 'faq') {
          $nodeIds[] = $node->id();
        }
      }

      if (isset($attachContentSlides[$srcId])) {
        $attachContentSlides[$srcId] = $node;
      }

      /*
       * Make sure Nodes are not all created the same second,
       * otherwise views will display them in random order.
       */
      sleep(1);
    }

    foreach ($attachContentSlides as $id => $node) {
      $this->attachContentReferenceSlides($node, $nodeIds);
    }

    $this->generateNodeReferenceParagraphs($teaserPage, $nodeIds);
    $this->generateMediaReferenceParagraphs($teaserPage);
  }

  /**
   * Generate node reference paragraphs.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Teaser page node.
   * @param array $nodeIds
   *   Node IDs.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function attachContentReferenceSlides(Node $node, array $nodeIds) {
    $paragraphs = [];
    foreach ($this->loadDefinitionByNameType('paragraphs', 'node_reference__slideshow') as $rawParagraph) {
      $rawParagraph['field_sub_title'] = empty($rawParagraph['field_sub_title']) ? $this->generateBlindText(3) : $rawParagraph['field_sub_title'];
      $rawParagraph['field_node_reference_nodes'] = $nodeIds;
      $rawParagraph['type'] = 'node_reference';
      $paragraph = Paragraph::create($rawParagraph);
      $paragraph->save();
      $paragraphs[] = $paragraph;
    }
    if (count($paragraphs)) {
      // @var $slider \Drupal\paragraphs\Entity\Paragraph
      $slider = $node->get('field_header_paragraphs')->first()->get('entity')->getTarget()->getValue();

      // @var $slides \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList
      $slides = $slider->get('field_slideshow_slides');
      $slider->set('field_slideshow_slides', array_merge($slides->getValue(), $paragraphs));
      $slider->save();

      $sliderValue[] = [
        'target_id' => $slider->id(),
        'target_revision_id' => $slider->getRevisionId(),
      ];
      $node->set('field_header_paragraphs', $sliderValue);
      $node->save();
    }
  }

  /**
   * Set front page.
   *
   * @param string $path_to_set
   *   Path to set.
   */
  private function setFrontPage(string $path_to_set) {
    $original_front_page = $this->configFactory->get('degov.degov_demo_content')->get('original_front_page');
    if (empty($original_front_page)) {
      // Save original front page.
      $front = $this->configFactory->get('system.site')->get('page.front');
      $this->configFactory->getEditable('degov.degov_demo_content')->set('original_front_page', $front)->save();
    }
    $this->configFactory->getEditable('system.site')->set('page.front', $path_to_set)->save();
  }

  /**
   * Reset front page.
   */
  private function resetFrontPage() {
    $original_front_page = $this->configFactory->get('degov.degov_demo_content')->get('original_front_page');
    if (!empty($original_front_page)) {
      $this->setFrontPage($original_front_page);
      $this->configFactory->getEditable('degov.degov_demo_content')->set('original_front_page', NULL)->save();
    }
  }

  /**
   * Deletes the generated content.
   */
  public function deleteContent(): void {
    parent::deleteContent();
    $this->resetFrontPage();
  }

  /**
   * Reset content.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function resetContent(): void {
    $this->deleteContent();
    $this->generateContent();
  }

}
