<?php

namespace Drupal\Tests\media_file_links\Kernel;

use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\media_file_links\Plugin\Field\FieldType\MediaFileLinkItem;

/**
 * Class MediaFileLinkItemTest.
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class MediaFileLinkItemTest extends MediaFileLinksTestBase {

  /**
   * Media file link item.
   *
   * @var \Drupal\media_file_links\Plugin\Field\FieldType\MediaFileLinkItem
   */
  private $mediaFileLinkItem;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $property_definition = $this->createMock(DataReferenceDefinitionInterface::class);
    $data_definition = $this->createMock(ComplexDataDefinitionInterface::class);
    $data_definition->expects($this->any())
      ->method('getPropertyDefinitions')
      ->willReturn([$property_definition]);

    $this->mediaFileLinkItem = new MediaFileLinkItem($data_definition, NULL, NULL);
  }

  /**
   * Test link resolution with existing supported media.
   */
  public function testLinkResolutionWithExistingSupportedMedia(): void {
    $this->mediaFileLinkItem->uri = '<media/file/' . $this->supportedMediaId . '>';
    $urlString = $this->mediaFileLinkItem->getUrl()->toString();
    self::assertContains('dummy.pdf', $urlString);
  }

  /**
   * Test link resolution with existing unsupported media.
   */
  public function testLinkResolutionWithExistingUnupportedMedia(): void {
    $this->mediaFileLinkItem->uri = '<media/file/' . $this->unsupportedMediaId . '>';
    $urlString = $this->mediaFileLinkItem->getUrl()->toString();
    self::assertContains('', $urlString);
  }

  /**
   * Test link resolution with nonexistent media.
   */
  public function testLinkResolutionWithNonexistentMedia(): void {
    $this->mediaFileLinkItem->uri = '<media/file/999>';
    $urlString = $this->mediaFileLinkItem->getUrl()->toString();
    self::assertContains('', $urlString);
  }

  /**
   * Test link resolution with regular url.
   */
  public function testLinkResolutionWithRegularUrl(): void {
    $this->mediaFileLinkItem->uri = 'http://www.drupal.org/';
    $urlString = $this->mediaFileLinkItem->getUrl()->toString();
    self::assertContains('http://www.drupal.org/', $urlString);
  }

}
