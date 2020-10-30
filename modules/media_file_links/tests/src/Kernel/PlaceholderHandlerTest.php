<?php

namespace Drupal\Tests\media_file_links\Kernel;

/**
 * Class PlaceholderHandlerTest.
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class PlaceholderHandlerTest extends MediaFileLinksTestBase {

  /**
   * Placeholder handler.
   *
   * @var \Drupal\media_file_links\Service\MediaFileLinkPlaceholderHandler
   */
  private $placeholderHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->placeholderHandler = $this->container->get('media_file_links.placeholder_handler');
  }

  /**
   * Test random string is no placeholder.
   */
  public function testRandomStringIsNoPlaceholder(): void {
    $result = $this->placeholderHandler->isValidMediaFileLinkPlaceholder('Not a placeholder!');
    self::assertFalse($result);
  }

  /**
   * Test menu placeholder is placeholder.
   */
  public function testMenuPlaceholderIsPlaceholder(): void {
    $result = $this->placeholderHandler->isValidMediaFileLinkPlaceholder('Some text here <media/file/1>');
    self::assertTrue($result);
  }

  /**
   * Test text placeholder is placeholder.
   */
  public function testTextPlaceholderIsPlaceholder(): void {
    $result = $this->placeholderHandler->isValidMediaFileLinkPlaceholder('Some text here [media/file/1]');
    self::assertTrue($result);
  }

  /**
   * Test link item Media ID resolution single digit.
   */
  public function testLinkItemMediaIdResolutionSingleDigit(): void {
    $mediaId = $this->placeholderHandler->getMediaIdFromPlaceholder('Some text here <media/file/1>');
    self::assertSame(1, $mediaId);
  }

  /**
   * Test link item media ID resolution double digit.
   */
  public function testLinkItemMediaIdResolutionDoubleDigit(): void {
    $mediaId = $this->placeholderHandler->getMediaIdFromPlaceholder('Some text here <media/file/12>');
    self::assertSame(12, $mediaId);
  }

}
