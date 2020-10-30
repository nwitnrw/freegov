<?php

namespace Drupal\Tests\degov_demo_content\Unit;

use Drupal\degov_demo_content\FileAdapter;
use Drupal\degov_demo_content\FileHandler\MediaFileHandler;
use Drupal\file\Entity\File;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Class MediaFileHandlerTest.
 */
class MediaFileHandlerTest extends UnitTestCase {

  /**
   * File adapter.
   *
   * @var \Drupal\degov_demo_content\FileAdapter
   */
  private $fileAdapter;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    /**
     * @var \Drupal\degov_demo_content\FileAdapter $fileAdapter
     */
    $fileAdapter = $this->prophesize(FileAdapter::class);
    $fileAdapter->fileGetContents(Argument::type('string'))->willReturn('file-contents');

    $file = $this->prophesize(File::class);
    $file->id()->willReturn(5);

    $fileAdapter->fileSaveData(Argument::type('string'), Argument::type('string'))->willReturn($file->reveal());
    $this->fileAdapter = $fileAdapter->reveal();
  }

  /**
   * Save files.
   *
   * @dataProvider getMediaFilesData
   */
  public function testSaveFiles(array $mediaToGenerate): void {
    $mediaFileHandler = new MediaFileHandler($this->fileAdapter);

    $mediaFileHandler->saveFiles($mediaToGenerate, '/some/fixtures/path');

    $files = $mediaFileHandler->getFiles('video_mobile_1');

    self::assertCount(5, $files);

    foreach ($files as $file) {
      self::assertIsInt($file->id());
    }

    $file = $mediaFileHandler->getFile('image_2');

    self::assertIsInt($file->id());
  }

  /**
   * Map file fields.
   *
   * @dataProvider getMediaForMapping
   */
  public function testMapFileFields(array $mediaDemoData, array $expectedMapping): void {
    $mediaFileHandler = new MediaFileHandler($this->fileAdapter);

    $mediaFileHandler->saveFiles($mediaDemoData, '/some/fixtures/path');

    $mappedFields = NULL;

    foreach ($mediaDemoData as $customMediaKey => $mediaData) {
      $mappedFields = $mediaFileHandler->mapFileFields($mediaData, $customMediaKey);
    }

    self::assertSame($mappedFields, $expectedMapping);
  }

  /**
   * Get media for mapping.
   *
   * @return array
   *   Media array.
   */
  public function getMediaForMapping(): array {
    return [
      [
        'mediaDemoData' => [
          'image_2' => [
            'bundle'             => 'image',
            'name'               => '{{SUBTITLE}}',
            'file'               => 'humberto-chavez-1058365-unsplash.jpg',
            'status'             => 1,
            'field_royalty_free' => 1,
          ],
        ],
        'expectedMapping' => [
          'bundle' => 'image',
          'name' => '{{SUBTITLE}}',
          'image' =>
              [
                'target_id' => 5,
                'alt' => '{{SUBTITLE}}',
                'title' => '{{SUBTITLE}}',
              ],
          'status' => 1,
          'field_royalty_free' => 1,
        ],
      ],
      [
        'mediaDemoData' => [
          'video_mobile_1' => [
            'bundle'  => 'video_mobile',
            'name'    => '{{SUBTITLE}}',
            'files'   => [
              'field_fullhd_video_mobile_mp4'    => 'pexels-videos-1409899-full-hd.mp4',
              'field_hdready_video_mobile_mp4'   => 'pexels-videos-1409899-hd-ready.mp4',
              'field_mobile_video_mobile_mp4'    => 'pexels-videos-1409899-standard.mp4',
              'field_video_mobile_mp4'           => 'pexels-videos-1409899-mobile.mp4',
              'field_ultrahd4k_video_mobile_mp4' => 'pexels-videos-1409899-4k.mp4',
            ],
            'status' => 1,
          ],
        ],
        'expectedMapping' => [
          'bundle' => 'video_mobile',
          'name' => '{{SUBTITLE}}',
          'field_fullhd_video_mobile_mp4' =>
            [
              'target_id' => 5,
            ],
          'field_hdready_video_mobile_mp4' =>
            [
              'target_id' => 5,
            ],
          'field_mobile_video_mobile_mp4' =>
            [
              'target_id' => 5,
            ],
          'field_video_mobile_mp4' =>
            [
              'target_id' => 5,
            ],
          'field_ultrahd4k_video_mobile_mp4' =>
            [
              'target_id' => 5,
            ],
          'status' => 1,
        ],
      ],
    ];
  }

  /**
   * Get media files data.
   *
   * @return array
   *   Media file data.
   */
  public function getMediaFilesData(): array {
    return [
      [
        'mediaToGenerate' => [
          'image_2' => [
            'bundle'             => 'image',
            'name'               => '{{SUBTITLE}}',
            'file'               => 'humberto-chavez-1058365-unsplash.jpg',
            'status'             => 1,
            'field_royalty_free' => 1,
          ],
          'video_mobile_1' => [
            'bundle' => 'video_mobile',
            'name'   => '{{SUBTITLE}}',
            'files'  => [
              'field_fullhd_video_mobile_mp4'    => 'pexels-videos-1409899-full-hd.mp4',
              'field_hdready_video_mobile_mp4'   => 'pexels-videos-1409899-hd-ready.mp4',
              'field_mobile_video_mobile_mp4'    => 'pexels-videos-1409899-standard.mp4',
              'field_video_mobile_mp4'           => 'pexels-videos-1409899-mobile.mp4',
              'field_ultrahd4k_video_mobile_mp4' => 'pexels-videos-1409899-4k.mp4',
            ],
            'status' => 1,
          ],
        ],
      ],
    ];
  }

}
