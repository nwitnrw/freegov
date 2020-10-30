<?php

namespace Drupal\Tests\degov_media_document\Kernel;

use Drupal\degov_media_document\Helper\DocumentLinkHelper;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the text formatters functionality.
 *
 * @group text
 */
class DocumentLinkHelperTest extends KernelTestBase {

  /**
   * Required modules.
   *
   * @var array
   */
  public static $modules = [
    'file',
    'user',
  ];

  /**
   * Dummy file data.
   *
   * @var array
   */
  private $files;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('file');

    $this->files['pdf'] = File::create([
      'uid'      => 0,
      'filename' => 'test.pdf',
      'uri'      => 'test.pdf',
    ]);
    $this->files['docx'] = File::create([
      'uid'      => 0,
      'filename' => 'test.docx',
      'uri'      => 'test.docx',
    ]);
  }

  /**
   * Do certain file types result in the expected FontAwesome icon class?
   */
  public function testFilenamesResultInExpectedIconClasses(): void {
    self::assertEquals('fa fa-file-pdf-o', DocumentLinkHelper::mapFileToFaIcon($this->files['pdf']));
    self::assertEquals('fa fa-file-word-o', DocumentLinkHelper::mapFileToFaIcon($this->files['docx']));
  }

  /**
   * Are PDF files opened in a new window?
   */
  public function testPdfsLinkToNewWindow(): void {
    self::assertEquals('_blank', DocumentLinkHelper::getTargetAttributeForFile($this->files['pdf']));
  }

  /**
   * Are non-PDF files opened in the same window?
   */
  public function testNonPdfFilesLinkToSameWindow(): void {
    self::assertEquals('', DocumentLinkHelper::getTargetAttributeForFile($this->files['docx']));
  }

}
