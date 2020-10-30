<?php

namespace Drupal\Tests\media_file_links\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class MediaFileLinksTestBase
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class MediaFileLinksTestBase extends KernelTestBase {

  use MediaTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'media_file_links',
    'field',
    'file',
    'image',
    'media',
    'media_test_source',
    'menu_link_content',
    'system',
    'user',
    'node',
    'filter',
    'filter_test',
  ];

  /**
   * Supported media ID.
   *
   * @var int|string|null
   */
  protected $supportedMediaId;

  /**
   * Unsupported media ID.
   *
   * @var int|string|null
   */
  protected $unsupportedMediaId;

  /**
   * File IDs.
   *
   * @var array
   */
  protected $fileIds = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('file', ['file_usage']);
    $this->installSchema('media_file_links', ['media_file_links_usage']);
    $this->installSchema('node', ['node_access']);

    $this->installConfig(['filter']);

    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('node');

    // Save a document file.
    $pdfFile = file_save_data(file_get_contents(drupal_get_path('module', 'degov_demo_content') . '/fixtures/dummy.pdf'), 'public://dummy.pdf', FileSystemInterface::EXISTS_REPLACE);
    $this->fileIds['pdf'] = $pdfFile->id();
    $wordFile = file_save_data(file_get_contents(drupal_get_path('module', 'degov_demo_content') . '/fixtures/word-document.docx'), 'public://word-document.docx', FileSystemInterface::EXISTS_REPLACE);
    $this->fileIds['word'] = $wordFile->id();

    // Create a supported document entity.
    $documentType = $this->createMediaType('test', ['id' => 'document', 'label' => 'Document']);

    $fieldDocumentStorage = FieldStorageConfig::create([
      'entity_type' => 'media',
      'field_name'  => 'field_document',
      'type'        => 'file',
    ]);
    $fieldDocumentStorage->save();

    FieldConfig::create([
      'field_storage' => $fieldDocumentStorage,
      'bundle'        => $documentType->id(),
      'label'         => 'Document field',
    ])->save();

    $newDocument = Media::create([
      'bundle'         => $documentType->id(),
      'name'           => 'Test document',
      'field_document' => [
        'target_id' => $this->fileIds['pdf'],
      ],
    ]);
    $newDocument->save();
    $this->supportedMediaId = $newDocument->id();

    // Create an unsupported foo entity.
    $fooType = $this->createMediaType('test', ['id' => 'foo', 'label' => 'Foo']);

    $fieldFooStorage = FieldStorageConfig::create([
      'entity_type' => 'media',
      'field_name'  => 'field_foo',
      'type'        => 'file',
    ]);
    $fieldFooStorage->save();

    FieldConfig::create([
      'field_storage' => $fieldFooStorage,
      'bundle'        => $fooType->id(),
      'label'         => 'Foo field',
    ])->save();

    $newFoo = Media::create([
      'bundle'    => $fooType->id(),
      'name'      => 'Test foo',
      'field_foo' => [
        'target_id' => $this->fileIds['pdf'],
      ],
    ]);
    $newFoo->save();
    $this->unsupportedMediaId = $newFoo->id();
  }

}
