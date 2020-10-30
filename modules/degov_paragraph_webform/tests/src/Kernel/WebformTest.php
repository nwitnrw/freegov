<?php

namespace Drupal\Tests\degov_paragraph_webform\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class WebformTest.
 */
class WebformTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'paragraphs',
    'user',
    'degov_paragraph_webform',
    'system',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('node');
  }

  /**
   * Test create.
   */
  public function testCreate() {
    $paragraph = Paragraph::create([
      'type' => 'webform',
      'field_title' => 'Text paragraph on top level',
      'field_subtitle' => 'Text paragraph on top level',
      'field_title_link' => 'Text paragraph on top level',
    ]);
    $paragraph->save();

    // Create a node with two paragraphs.
    $node = Node::create([
      'title' => $this->randomMachineName(),
      'type' => 'article',
      'node_paragraph_field' => [$paragraph],
    ]);
    $node->save();
  }

}
