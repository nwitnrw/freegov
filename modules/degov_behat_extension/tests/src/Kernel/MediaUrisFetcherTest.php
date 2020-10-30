<?php

namespace Drupal\Tests\degov_behat_extension\Kernel;

use Drupal\path_alias\Entity\PathAlias;
use Drupal\Tests\media\Kernel\MediaKernelTestBase;

/**
 * Class MediaUrisFetcherTest.
 */
class MediaUrisFetcherTest extends MediaKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'path_alias',
    'degov_behat_extension',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('path_alias');
  }

  public function testProvide(): void {
    $mediaType = $this->createMediaType('file');
    $media = $this->generateMedia('test-1.txt', $mediaType);
    $media->save();

    $mediaType = $this->createMediaType('file');
    $media = $this->generateMedia('test-2.txt', $mediaType);
    $media->save();

    /**
     * @var \Drupal\Core\Entity\EntityStorageInterface $aliasStorage
     */
    $aliasStorage = $this->container->get('entity_type.manager')->getStorage('path_alias');
    $aliasStorage->save(PathAlias::create([
      'path'     => '/media/1',
      'alias'    => '/test-media-1',
      'langcode' => 'und',
      'status'   => 1,
    ]));
    $aliasStorage->save(PathAlias::create([
      'path'     => '/media/2',
      'alias'    => '/test-media-2',
      'langcode' => 'und',
      'status'   => 1,
    ]));

    /**
     * @var \Drupal\degov_behat_extension\PerformanceCheck\StaticUrisFetcher $staticUrisFetcher
     */
    $staticUrisFetcher = $this->container->get('degov_behat_extension.static_uris_fetcher');

    self::assertSame([
      0 => '/test-media-1',
      1 => '/test-media-2',
    ], $staticUrisFetcher->provideUrisByEntityTypeStorage('media'));
  }

}
