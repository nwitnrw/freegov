<?php

declare(strict_types=1);

namespace Drupal\Tests\media_file_links\Kernel;

/**
 * Class SuggestionsTest.
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class SuggestionsTest extends MediaFileLinksTestBase {

  /**
   * File suggester.
   *
   * @var \Drupal\media_file_links\Service\MediaFileSuggester
   */
  private $fileSuggester;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->fileSuggester = $this->container->get('media_file_links.file_suggester');
  }

  /**
   * Test find supported media by title.
   */
  public function testFindSupportedMediaByTitle(): void {
    $searchResult = $this->fileSuggester->findBySearchString('document');
    self::assertSame('[{"id":"1","title":"Test document","bundle":"document","bundleLabel":"Document","mimetype":"application\/pdf","iconClass":"fas fa-file-pdf","filename":"dummy.pdf"}]', $searchResult);
  }

  /**
   * Test unsupported media cannot be found by title.
   */
  public function testUnsupportedMediaCannotBeFoundByTitle(): void {
    $searchResult = $this->fileSuggester->findBySearchString('foo');
    self::assertSame('[]', $searchResult);
  }

  /**
   * Test find only supported media by filename.
   */
  public function testFindOnlySupportedMediaByFilename(): void {
    $searchResult = $this->fileSuggester->findBySearchString('dummy');
    self::assertSame('[{"id":"1","title":"Test document","bundle":"document","bundleLabel":"Document","mimetype":"application\/pdf","iconClass":"fas fa-file-pdf","filename":"dummy.pdf"}]', $searchResult);
  }

  /**
   * Test query should only return one suggestion per entity.
   */
  public function testQueryShouldOnlyReturnOneSuggestionPerEntity(): void {
    $searchResult = $this->fileSuggester->findBySearchString('um');
    self::assertSame('[{"id":"1","title":"Test document","bundle":"document","bundleLabel":"Document","mimetype":"application\/pdf","iconClass":"fas fa-file-pdf","filename":"dummy.pdf"}]', $searchResult);
  }

}
