<?php

namespace Drupal\Tests\media_file_links\Kernel;

use Drupal\media\Entity\Media;

/**
 * Class LinkResolutionTest.
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class LinkResolutionTest extends MediaFileLinksTestBase {

  /**
   * File link resolver.
   *
   * @var \Drupal\media_file_links\Service\MediaFileLinkResolver
   *   $fileLinkResolver
   */
  private $fileLinkResolver;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->fileLinkResolver = $this->container->get('media_file_links.file_link_resolver');
  }

  /**
   * Test link resolution with existing supported media.
   */
  public function testLinkResolutionWithExistingSupportedMedia(): void {
    $urlString = $this->fileLinkResolver->getFileUrlString($this->supportedMediaId);
    self::assertContains('dummy.pdf', $urlString);
  }

  /**
   * Test link resolution with existing unsupported media.
   */
  public function testLinkResolutionWithExistingUnsupportedMedia(): void {
    $urlString = $this->fileLinkResolver->getFileUrlString($this->unsupportedMediaId);
    self::assertContains('', $urlString);
  }

  /**
   * Test link resolution with nonexistent media.
   */
  public function testLinkResolutionWithNonexistentMedia(): void {
    $urlString = $this->fileLinkResolver->getFileUrlString(99);
    self::assertSame('', $urlString);
  }

  /**
   * Test changing the file results in uploaded link.
   */
  public function testChangingTheFileResultsInUpdatedLink(): void {
    $media = Media::load($this->supportedMediaId);
    $media->set('field_document', [
      'target_id' => $this->fileIds['word'],
    ]);
    $media->save();

    $urlString = $this->fileLinkResolver->getFileUrlString($this->supportedMediaId);
    self::assertContains('word-document.docx', $urlString);
  }

}
