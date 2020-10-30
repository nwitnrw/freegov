<?php

declare(strict_types=1);

namespace Drupal\Tests\degov_auto_crop\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class OffsetConfigTest.
 */
class OffsetConfigTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'lightning_core',
    'image',
    'responsive_image',
    'breakpoint',
    'crop',
    'media',
    'degov_image_and_crop_styles',
    'degov_auto_crop',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('crop_type');
    $this->installConfig([
      'image',
      'responsive_image',
      'crop',
      'degov_image_and_crop_styles',
      'degov_auto_crop',
    ]);
  }

  /**
   * Test offset values are installed.
   */
  public function testOffsetValuesAreInstalled() {
    $expectedValues = [
      'degov_auto_crop' => [
        'offsets' => [
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
        ],
      ],
    ];
    /** @var \Drupal\Core\Config\ImmutableConfig $immutableConfig */
    $immutableConfig = $this->config('crop.type.16_to_9');
    $settings = $immutableConfig->get('third_party_settings');
    $this->assertSame($expectedValues, $settings);
  }

}
