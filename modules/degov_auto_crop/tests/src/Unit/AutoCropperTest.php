<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_auto_crop\Unit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\Image;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Image\ImageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\crop\CropInterface;
use Drupal\crop\CropStorageInterface;
use Drupal\crop\Entity\CropType;
use Drupal\degov_auto_crop\Service\AutoCropper;
use Drupal\file\Entity\File;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AutoCropperTest.
 */
class AutoCropperTest extends UnitTestCase {

  /**
   * The AutoCropper instance.
   *
   * @var \Drupal\degov_auto_crop\Service\AutoCropper
   */
  private $autoCropper;

  /**
   * The FileSystem instance.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The ImageFactory instance.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  private $imageFactory;

  /**
   * Holds the CropTypes to test against.
   *
   * @var array
   */
  private $cropTypes = [];

  /**
   * Holds the Files to test against.
   *
   * @var array
   */
  private $files = [];

  /**
   * Holds the Images to test against.
   *
   * @var array
   */
  private $images = [];

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {

    parent::setUp();

    $this->generateCropTypes();

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->method('getStorage')
      ->with($this->logicalOr(
        $this->equalTo('crop_type'),
        $this->equalTo('crop')
      ))
      ->will($this->returnCallback([$this, 'returnStorage']));

    /** @var \Drupal\Core\File\FileSystemInterface fileSystem */
    $this->fileSystem = $this->getMockBuilder(FileSystemInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var \Drupal\Core\Image\ImageFactory imageFactory */
    $this->imageFactory = $this->getMockBuilder(ImageFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger */
    $logger = $this->getMockBuilder(LoggerChannelFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->generateFiles();

    $this->autoCropper = new AutoCropper($entityTypeManager, $this->fileSystem, $logger, $this->imageFactory);

  }

  /**
   * Tests that calculateCropCenterOffsets returns the expected values.
   */
  public function testCalculateCropCenterOffsets(): void {
    // Landscape crop on landscape image.
    $image_orientation = 'landscape';
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'landscape');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 800,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => (800 / 2) + ((900 - 800) / 3),
    ], $crop_dimensions);

    // Portrait crop on landscape image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'portrait');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 900,
      'width'  => 450,
      'x'      => (450 / 2) + ((1600 - 450) / 3),
      'y'      => 900 / 2,
    ], $crop_dimensions);

    // Square crop on landscape image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'square');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 900,
      'width'  => 900,
      'x'      => (900 / 2) + ((1600 - 900) / 3),
      'y'      => 900 / 2,
    ], $crop_dimensions);

    // Landscape crop on portrait image.
    $image_orientation = 'portrait';
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'landscape');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 450,
      'width'  => 900,
      'x'      => 900 / 2,
      'y'      => (450 / 2) + ((1600 - 450) / 3),
    ], $crop_dimensions);

    // Portrait crop on portrait image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'portrait');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 1600,
      'width'  => 800,
      'x'      => (800 / 2) + ((900 - 800) / 3),
      'y'      => 1600 / 2,
    ], $crop_dimensions);

    // Square crop on portrait image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'square');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 900,
      'width'  => 900,
      'x'      => 900 / 2,
      'y'      => (900 / 2) + ((1600 - 900) / 3),
    ], $crop_dimensions);

    // Landscape crop on square image.
    $image_orientation = 'square';
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'landscape');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 800,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => (800 / 2) + ((1600 - 800) / 3),
    ], $crop_dimensions);

    // Portrait crop on square image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'portrait');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 1600,
      'width'  => 800,
      'x'      => (800 / 2) + ((1600 - 800) / 3),
      'y'      => 1600 / 2,
    ], $crop_dimensions);

    // Square crop on square image.
    $crop_dimensions = $this->getMockCropDimensions($image_orientation, 'square');
    $this->autoCropper->calculateCropCenterOffsets($this->getMockOffsets(), $this->getMockImageDimensions($image_orientation), $crop_dimensions);
    $this->assertEquals([
      'height' => 1600,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => 1600 / 2,
    ], $crop_dimensions);
  }

  /**
   * Tests that a crop with a given aspect ratio is scaled to fit the image.
   */
  public function testCalculateScaleFactor(): void {
    // 1:1 fits into 16:9.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('landscape'), [
      1,
      1,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 2:1 needs to be scaled to fit into 16:9.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('landscape'), [
      2,
      1,
    ]);
    $this->assertLessThan(1, $scaleFactor);
    // 16:9 fits into 16:9.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('landscape'), [
      16,
      9,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 1:2 fits into 16:9.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('landscape'), [
      1,
      2,
    ]);
    $this->assertEquals(1, $scaleFactor);

    // 1:1 fits into 9:16.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('portrait'), [
      1,
      1,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 2:1 fits into 9:16.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('portrait'), [
      2,
      1,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 16:9 fits into 9:16.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('portrait'), [
      16,
      9,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 1:2 needs to be scaled to fit into 9:16.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('portrait'), [
      1,
      2,
    ]);
    $this->assertLessThan(1, $scaleFactor);

    // 1:1 fits into 1:1.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('square'), [
      1,
      1,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 2:1 fits into 1:1.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('square'), [
      2,
      1,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 16:9 fits into 1:1.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('square'), [
      16,
      9,
    ]);
    $this->assertEquals(1, $scaleFactor);
    // 1:2 needs to be scaled to fit into 1:1.
    $scaleFactor = $this->autoCropper->calculateScaleFactor($this->getMockImageDimensions('square'), [
      1,
      2,
    ]);
    $this->assertLessThan(1, $scaleFactor);
  }

  /**
   * Tests that a CropType applied to a given image has the expected dimensions.
   */
  public function testCalculateCropDimensions(): void {
    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['1_by_1'], $this->getMockImageDimensions('landscape'));
    $this->assertEquals([
      'height' => 900,
      'width'  => 900,
      'x'      => (900 / 2) + ((1600 - 900) / 2),
      'y'      => 900 / 2,
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['2_by_1'], $this->getMockImageDimensions('landscape'));
    $this->assertEquals([
      'height' => 800,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => (800 / 2) + ((900 - 800) / 3),
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['16_by_9'], $this->getMockImageDimensions('landscape'));
    $this->assertEquals([
      'height' => 900,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => 900 / 2,
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['1_by_1'], $this->getMockImageDimensions('portrait'));
    $this->assertEquals([
      'height' => 900,
      'width'  => 900,
      'x'      => 900 / 2,
      'y'      => (900 / 2) + ((1600 - 900) / 3),
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['2_by_1'], $this->getMockImageDimensions('portrait'));
    $this->assertEquals([
      'height' => 450,
      'width'  => 900,
      'x'      => 900 / 2,
      'y'      => (450 / 2) + ((1600 - 450) / 3),
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['16_by_9'], $this->getMockImageDimensions('portrait'));
    $this->assertEquals([
      'height' => (900 / 16) * 9,
      'width'  => 900,
      'x'      => 900 / 2,
      'y'      => (((900 / 16) * 9) / 2) + ((1600 - ((900 / 16) * 9)) / 3),
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['1_by_1'], $this->getMockImageDimensions('square'));
    $this->assertEquals([
      'height' => 1600,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => 1600 / 2,
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['2_by_1'], $this->getMockImageDimensions('square'));
    $this->assertEquals([
      'height' => 800,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => (800 / 2) + ((1600 - 800) / 3),
    ], $dimensions);

    $dimensions = $this->autoCropper->calculateCropDimensions($this->cropTypes['16_by_9'], $this->getMockImageDimensions('square'));
    $this->assertEquals([
      'height' => 900,
      'width'  => 1600,
      'x'      => 1600 / 2,
      'y'      => (900 / 2) + ((1600 - 900) / 3),
    ], $dimensions);
  }

  /**
   * Tests that for a given File the expected crops will be set.
   */
  public function testApplyImageCrops(): void {

    $crops = $this->autoCropper->applyImageCrops($this->files['16_by_9']);
    $this->assertCount(count($this->cropTypes), $crops);
    /** @var \Drupal\crop\CropInterface $crop */
    foreach ($crops as $crop) {
      $type = $crop->get('type');
      $position = $crop->position();
      $size = $crop->size();
      switch ($type) {
        case '1_by_1':
          $this->assertEquals([
            'x' => (int) ((900 / 2) + ((1600 - 900) / 2)),
            'y' => (int) (900 / 2),
          ], $position);
          $this->assertEquals(['height' => 900, 'width' => 900], $size);
          break;

        case '1_by_2':
          $this->assertEquals([
            'x' => (int) ((450 / 2) + ((1600 - 450) / 3)),
            'y' => (int) (900 / 2),
          ], $position);
          $this->assertEquals(['height' => 900, 'width' => 450], $size);
          break;

        case '2_by_1':
          $this->assertEquals([
            'x' => (int) (1600 / 2),
            'y' => (int) ((800 / 2) + ((900 - 800) / 3)),
          ], $position);
          $this->assertEquals(['height' => 800, 'width' => 1600], $size);
          break;

        case '16_by_9':
          $this->assertEquals([
            'x' => (int) (1600 / 2),
            'y' => (int) (900 / 2),
          ], $position);
          $this->assertEquals(['height' => 900, 'width' => 1600], $size);
          break;
      }
    }

    $crops = $this->autoCropper->applyImageCrops($this->files['9_by_16']);
    $this->assertCount(count($this->cropTypes), $crops);
    /** @var \Drupal\crop\CropInterface $crop */
    foreach ($crops as $crop) {
      $type = $crop->get('type');
      $position = $crop->position();
      $size = $crop->size();
      switch ($type) {
        case '1_by_1':
          $this->assertEquals([
            'x' => (int) (900 / 2),
            'y' => (int) ((900 / 2) + ((1600 - 900) / 3)),
          ], $position);
          $this->assertEquals(['height' => 900, 'width' => 900], $size);
          break;

        case '1_by_2':
          $this->assertEquals([
            'x' => (int) ((800 / 2) + ((900 - 800) / 2)),
            'y' => (int) (1600 / 2),
          ], $position);
          $this->assertEquals(['height' => 1600, 'width' => 800], $size);
          break;

        case '2_by_1':
          $this->assertEquals([
            'x' => (int) (900 / 2),
            'y' => (int) ((450 / 2) + ((1600 - 450) / 3)),
          ], $position);
          $this->assertEquals(['height' => 450, 'width' => 900], $size);
          break;

        case '16_by_9':
          $target_height = (int) ((900 / 16) * 9);
          $this->assertEquals([
            'x' => (int) (900 / 2),
            'y' => (int) (($target_height / 2) + ((1600 - $target_height) / 3)),
          ], $position);
          $this->assertEquals([
            'height' => $target_height,
            'width'  => 900,
          ], $size);
          break;
      }
    }

    $crops = $this->autoCropper->applyImageCrops($this->files['1_by_1']);
    $this->assertCount(count($this->cropTypes), $crops);
    /** @var \Drupal\crop\CropInterface $crop */
    foreach ($crops as $crop) {
      $type = $crop->get('type');
      $position = $crop->position();
      $size = $crop->size();
      switch ($type) {
        case '1_by_1':
          $this->assertEquals([
            'x' => (int) (1600 / 2),
            'y' => (int) (1600 / 2),
          ], $position);
          $this->assertEquals(['height' => 1600, 'width' => 1600], $size);
          break;

        case '1_by_2':
          $this->assertEquals([
            'x' => (int) ((800 / 2) + ((1600 - 800) / 3)),
            'y' => (int) (1600 / 2),
          ], $position);
          $this->assertEquals(['height' => 1600, 'width' => 800], $size);
          break;

        case '2_by_1':
          $this->assertEquals([
            'x' => (int) (1600 / 2),
            'y' => (int) ((800 / 2) + ((1600 - 800) / 3)),
          ], $position);
          $this->assertEquals(['height' => 800, 'width' => 1600], $size);
          break;

        case '16_by_9':
          $this->assertEquals([
            'x' => (int) (1600 / 2),
            'y' => (int) ((900 / 2) + ((1600 - 900) / 3)),
          ], $position);
          $this->assertEquals(['height' => 900, 'width' => 1600], $size);
          break;
      }
    }
  }

  /**
   * Returns an array of crop frame offsets to test against.
   *
   * @return array
   *   The offsets.
   */
  private function getMockOffsets(): array {
    return [
      'top'    => 1,
      'bottom' => 2,
      'left'   => 1,
      'right'  => 2,
    ];
  }

  /**
   * Provides mock image dimensions for a given image orientation.
   *
   * @param string $orientation
   *   The orientation of the image we want.
   *
   * @return array
   *   The image dimensions for the requested orientation.
   */
  private function getMockImageDimensions(string $orientation = 'landscape'): array {
    $dimensions = [
      'landscape' => [
        1600,
        900,
      ],
      'portrait'  => [
        900,
        1600,
      ],
      'square'    => [
        1600,
        1600,
      ],
    ];

    return $dimensions[$orientation];
  }

  /**
   * Provides mock crop sizes for given image orientations.
   *
   * @param string $image_orientation
   *   The orientation of the image.
   * @param string $crop_orientation
   *   The orientation of the crop.
   *
   * @return array
   *   The appropriate crop dimensions, if any.
   */
  private function getMockCropDimensions(string $image_orientation = 'landscape', string $crop_orientation = 'landscape'): array {
    $dimensions = [
      'landscape' => [
        'landscape' => [
          'height' => 800,
          'width'  => 1600,
        ],
        'portrait'  => [
          'height' => 900,
          'width'  => 450,
        ],
        'square'    => [
          'height' => 900,
          'width'  => 900,
        ],
      ],
      'portrait'  => [
        'landscape' => [
          'height' => 450,
          'width'  => 900,
        ],
        'portrait'  => [
          'height' => 1600,
          'width'  => 800,
        ],
        'square'    => [
          'height' => 900,
          'width'  => 900,
        ],
      ],
      'square'    => [
        'landscape' => [
          'height' => 800,
          'width'  => 1600,
        ],
        'portrait'  => [
          'height' => 1600,
          'width'  => 800,
        ],
        'square'    => [
          'height' => 1600,
          'width'  => 1600,
        ],
      ],
    ];

    return $dimensions[$image_orientation][$crop_orientation];
  }

  /**
   * Generates an array of mock CropTypes to test against.
   */
  private function generateCropTypes(): void {
    $this->cropTypes['1_by_1'] = $this->getMockBuilder(CropType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cropTypes['1_by_1']->aspect_ratio = '1:1';
    $this->cropTypes['1_by_1']->method('id')->willReturn('1_by_1');
    $this->cropTypes['1_by_1']->method('getThirdPartySetting')
      ->with('degov_auto_crop', 'offsets')
      ->willReturn([
        'landscape' => [
          'top'    => 1,
          'bottom' => 1,
          'left'   => 1,
          'right'  => 1,
        ],
        'portrait'  => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'square'    => [
          'top'    => 1,
          'bottom' => 1,
          'left'   => 1,
          'right'  => 1,
        ],
      ]);

    $this->cropTypes['1_by_2'] = $this->getMockBuilder(CropType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cropTypes['1_by_2']->method('id')->willReturn('1_by_2');
    $this->cropTypes['1_by_2']->aspect_ratio = '1:2';
    $this->cropTypes['1_by_2']->method('getThirdPartySetting')
      ->with('degov_auto_crop', 'offsets')
      ->willReturn([
        'landscape' => [
          'top'    => 1,
          'bottom' => 1,
          'left'   => 1,
          'right'  => 2,
        ],
        'portrait'  => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'square'    => [
          'top'    => 1,
          'bottom' => 1,
          'left'   => 1,
          'right'  => 2,
        ],
      ]);

    $this->cropTypes['2_by_1'] = $this->getMockBuilder(CropType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cropTypes['2_by_1']->method('id')->willReturn('2_by_1');
    $this->cropTypes['2_by_1']->aspect_ratio = '2:1';
    $this->cropTypes['2_by_1']->method('getThirdPartySetting')
      ->with('degov_auto_crop', 'offsets')
      ->willReturn([
        'landscape' => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'portrait'  => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'square'    => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
      ]);

    $this->cropTypes['16_by_9'] = $this->getMockBuilder(CropType::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->cropTypes['16_by_9']->method('id')->willReturn('16_by_9');
    $this->cropTypes['16_by_9']->aspect_ratio = '16:9';
    $this->cropTypes['16_by_9']->method('getThirdPartySetting')
      ->with('degov_auto_crop', 'offsets')
      ->willReturn([
        'landscape' => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'portrait'  => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
        'square'    => [
          'top'    => 1,
          'bottom' => 2,
          'left'   => 1,
          'right'  => 1,
        ],
      ]);
  }

  /**
   * Generates an array of Files to test against.
   */
  private function generateFiles(): void {
    $root = vfsStream::setup('');
    $this->images['vfs:///16_by_9.jpg'] = $this->getMockBuilder(Image::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->images['vfs:///16_by_9.jpg']->method('getWidth')->willReturn(1600);
    $this->images['vfs:///16_by_9.jpg']->method('getHeight')->willReturn(900);

    $this->images['vfs:///9_by_16.jpg'] = $this->getMockBuilder(Image::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->images['vfs:///9_by_16.jpg']->method('getWidth')->willReturn(900);
    $this->images['vfs:///9_by_16.jpg']->method('getHeight')->willReturn(1600);

    $this->images['vfs:///1_by_1.jpg'] = $this->getMockBuilder(Image::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->images['vfs:///1_by_1.jpg']->method('getWidth')->willReturn(1600);
    $this->images['vfs:///1_by_1.jpg']->method('getHeight')->willReturn(1600);

    $this->files['16_by_9'] = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->files['16_by_9']->method('id')->willReturn(1);
    $this->files['16_by_9']->method('getMimeType')->willReturn('image/jpeg');
    $this->files['16_by_9']->method('getFileUri')->willReturn(vfsStream::newFile('16_by_9.jpg')->at($root)->setContent('')->url());

    $this->files['9_by_16'] = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->files['9_by_16']->method('id')->willReturn(1);
    $this->files['9_by_16']->method('getMimeType')->willReturn('image/jpeg');
    $this->files['9_by_16']->method('getFileUri')->willReturn(vfsStream::newFile('9_by_16.jpg')->at($root)->setContent('')->url());

    $this->files['1_by_1'] = $this->getMockBuilder(File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->files['1_by_1']->method('id')->willReturn(1);
    $this->files['1_by_1']->method('getMimeType')->willReturn('image/jpeg');
    $this->files['1_by_1']->method('getFileUri')->willReturn(vfsStream::newFile('1_by_1.jpg')->at($root)->setContent('')->url());

    $this->fileSystem
      ->method('realpath')
      ->with($this->logicalOr(
        $this->equalTo(vfsStream::newFile('16_by_9.jpg')->at($root)->setContent('')->url()),
        $this->equalTo(vfsStream::newFile('9_by_16.jpg')->at($root)->setContent('')->url()),
        $this->equalTo(vfsStream::newFile('1_by_1.jpg')->at($root)->setContent('')->url())
      ))
      ->will($this->returnCallback([$this, 'returnRealpath']));

    $this->imageFactory
      ->method('get')
      ->with($this->logicalOr(
        $this->equalTo(vfsStream::newFile('16_by_9.jpg')->at($root)->setContent('')->url()),
        $this->equalTo(vfsStream::newFile('9_by_16.jpg')->at($root)->setContent('')->url()),
        $this->equalTo(vfsStream::newFile('1_by_1.jpg')->at($root)->setContent('')->url())
      ))
      ->will($this->returnCallback([$this, 'returnImage']));
  }

  /**
   * Mocks the return of a realpath for a file.
   *
   * @param string $filename
   *   The incoming filename.
   *
   * @return string
   *   The mock realpath (really just the filename).
   */
  public function returnRealpath(string $filename): string {
    return $filename;
  }

  /**
   * Returns the image associated with the given realpath.
   *
   * @param string $realpath
   *   The realpath we are looking for an image for.
   *
   * @return \Drupal\Core\Image\ImageInterface
   *   The matching image.
   */
  public function returnImage(string $realpath): ImageInterface {
    return $this->images[$realpath];
  }

  /**
   * Returns storages for CropTypes and Crops.
   *
   * @param string $entityType
   *   The entity type we need a storage for.
   *
   * @return null|\PHPUnit\Framework\MockObject\MockObject|\Drupal\crop\CropStorageInterface
   *   The mocked storage, or null.
   */
  public function returnStorage(string $entityType): ?MockObject {
    $cropStorage = NULL;

    switch ($entityType) {
      case 'crop_type':
        $cropStorage = $this->getMockBuilder(CropStorageInterface::class)
          ->disableOriginalConstructor()
          ->getMock();
        $cropStorage->method('loadMultiple')->willReturn($this->cropTypes);
        break;

      case 'crop':
        $cropStorage = $this->getMockBuilder(CropStorageInterface::class)
          ->disableOriginalConstructor()
          ->getMock();
        $cropStorage->method('loadByProperties')->willReturn(NULL);
        $cropStorage->method('create')->will(
          $this->returnCallback([$this, 'returnCrop'])
        );
        break;
    }

    return $cropStorage;
  }

  /**
   * Return a mocked Crop instance with the associated values.
   *
   * @param array $cropValues
   *   The calculated values for the Crop.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The mocked Crop instance.
   */
  public function returnCrop(array $cropValues): MockObject {
    $crop = $this->getMockBuilder(CropInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $crop->method('get')->with('type')->willReturn($cropValues['type']);
    $crop->method('position')->willReturn([
      'x' => (int) $cropValues['x'],
      'y' => (int) $cropValues['y'],
    ]);
    $crop->method('size')->willReturn([
      'height' => (int) $cropValues['height'],
      'width'  => (int) $cropValues['width'],
    ]);
    return $crop;
  }

}
