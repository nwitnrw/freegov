<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_govbot_faq\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Class FAQAccessTest.
 */
class FAQAccessTest extends KernelTestBase {

  private const SHORT_BLIND_TEXT = 'Lorem ipsum dolor';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'system',
    'node',
    'paragraphs',
    'degov_paragraph_faq',
    'paragraphs',
    'text',
    'taxonomy',
    'degov_govbot_faq',
    'entity_reference_revisions',
    'field',
    'file',
  ];

  /**
   * Faq access.
   *
   * @var \Drupal\degov_govbot_faq\FAQAccess
   */
  private $faqAccess;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', 'node_access');
    $this->faqAccess = $this->container->get('degov_govbot_faq.faq_access');
    $this->createParagraphTypeFaq();
    $this->createParagraphTypeFaqList();
    $this->createFaqNodeType();
  }

  /**
   * Create paragraph type faq list.
   */
  private function createParagraphTypeFaqList(): void {
    $paragraph_type = ParagraphsType::create([
      'label' => 'FAQ list',
      'id'    => 'faq_list',
    ]);
    $paragraph_type->save();

    $field_storage = FieldStorageConfig::create([
      'field_name'  => 'field_faq_list_inner_paragraphs',
      'entity_type' => 'paragraph',
      'type'        => 'entity_reference_revisions',
      'cardinality' => '-1',
      'settings'    => [
        'target_type' => 'paragraph',
      ],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'faq_list',
    ]);
    $field->save();
  }

  /**
   * Create paragraph type faq.
   */
  private function createParagraphTypeFaq(): void {
    $paragraph_type = ParagraphsType::create([
      'label' => 'FAQ',
      'id'    => 'faq',
    ]);
    $paragraph_type->save();

    $textFields = [
      'field_faq_text',
      'field_faq_title',
      'field_govbot_answer',
      'field_govbot_question',
    ];

    foreach ($textFields as $textField) {
      $field_storage = FieldStorageConfig::create([
        'field_name'  => $textField,
        'entity_type' => 'paragraph',
        'type'        => 'string',
        'cardinality' => '1',
      ]);
      $field_storage->save();

      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle'        => 'faq',
      ]);
      $field->save();
    }

  }

  /**
   * Setup accessible node.
   */
  private function setupAccessibleNode(): NodeInterface {
    $faqElement = Paragraph::create([
      'type'                  => 'faq',
      'field_faq_text'        => self::SHORT_BLIND_TEXT,
      'field_faq_title'       => self::SHORT_BLIND_TEXT,
      'field_govbot_answer'   => self::SHORT_BLIND_TEXT,
      'field_govbot_question' => self::SHORT_BLIND_TEXT,
    ]);
    $faqElement->save();

    $faqList = Paragraph::create([
      'type'                            => 'faq_list',
      'field_title'                     => self::SHORT_BLIND_TEXT,
      'field_faq_list_inner_paragraphs' => $faqElement,
    ]);
    $faqList->save();

    $node = Node::create([
      'title'             => self::SHORT_BLIND_TEXT,
      'type'              => 'faq',
      'field_faq_related' => [
        $faqList,
      ],
    ]);
    $node->save();

    /** @var \Drupal\node\NodeInterface $nodeLoaded */
    $nodeLoaded = Node::load($node->id());

    self::assertSame(self::SHORT_BLIND_TEXT, $nodeLoaded->getTitle());

    return $nodeLoaded;
  }

  /**
   * Setup not accessible node.
   */
  private function setupNotAccessibleNode(): NodeInterface {
    $faqElement = Paragraph::create([
      'type'                  => 'faq',
      'field_faq_text'        => '',
      'field_faq_title'       => '',
      'field_govbot_answer'   => self::SHORT_BLIND_TEXT,
      'field_govbot_question' => self::SHORT_BLIND_TEXT,
    ]);
    $faqElement->save();

    $faqList = Paragraph::create([
      'type'                            => 'faq_list',
      'field_title'                     => self::SHORT_BLIND_TEXT,
      'field_faq_list_inner_paragraphs' => $faqElement,
    ]);
    $faqList->save();

    $node = Node::create([
      'title'             => self::SHORT_BLIND_TEXT,
      'type'              => 'faq',
      'field_faq_related' => [
        $faqList,
      ],
    ]);
    $node->save();

    /** @var \Drupal\node\NodeInterface $nodeLoaded */
    $nodeLoaded = Node::load($node->id());

    self::assertSame(self::SHORT_BLIND_TEXT, $nodeLoaded->getTitle());

    return $nodeLoaded;
  }

  /**
   * Create faq node type.
   */
  public function createFaqNodeType(): void {
    $nodeType = NodeType::create([
      'name' => 'FAQ',
      'type' => 'faq',
    ]);
    $nodeType->save();

    $fieldStorage = FieldStorageConfig::create([
      'field_name'  => 'field_faq_related',
      'entity_type' => 'node',
      'type'        => 'entity_reference_revisions',
      'cardinality' => '-1',
      'settings'    => [
        'target_type' => 'paragraph',
      ],
    ]);
    $fieldStorage->save();

    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle'        => 'faq',
    ]);
    $field->save();
  }

  /**
   * Test if accessible on site.
   */
  public function testIsAccessibleOnSite(): void {
    self::assertTrue($this->faqAccess->isAccessibleOnSite($this->setupAccessibleNode()));
  }

  /**
   * Test if not accessible on site.
   */
  public function testIsNotAccessibleOnSite(): void {
    self::assertFalse($this->faqAccess->isAccessibleOnSite($this->setupNotAccessibleNode()));
  }

}
