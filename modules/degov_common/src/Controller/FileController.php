<?php

namespace Drupal\degov_common\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileController.
 */
final class FileController implements ContainerInjectionInterface {

  /** @var \Drupal\Core\File\FileSystemInterface*/
  protected $fileSystem;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  public function __construct(FileSystemInterface $file_system, ModuleHandlerInterface $module_handler) {
    $this->fileSystem = $file_system;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $file_system = $container->get('file_system');
    $module_handler = $container->get('module_handler');
    return new static($file_system, $module_handler);
  }

  /**
   * Returns an HTTP response for a file being downloaded.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to download, as an entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The file to download, as a response.
   */
  public function download(FileInterface $file): Response {

    // Get correct headers.
    $headers = [
      'Content-Type' => Unicode::mimeHeaderEncode($file->getMimeType()),
      'Content-Disposition' => 'attachment; filename="' . Unicode::mimeHeaderEncode($this->fileSystem->basename($file->getFileUri())) . '"',
      'Content-Length' => $file->getSize(),
      'Content-Transfer-Encoding' => 'binary',
      'Pragma' => 'no-cache',
      'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
      'Expires' => '0',
    ];

    // Let other modules alter the download headers.
    $this->moduleHandler->alter('file_download_headers', $headers, $file);

    // Let other modules know the file is being downloaded.
    $this->moduleHandler->invokeAll('file_transfer', [$file->getFileUri(), $headers]);

    try {
      return new BinaryFileResponse($file->getFileUri(), 200, $headers);
    }
    catch (FileNotFoundException $e) {
      return new Response(t('File @uri not found', ['@uri' => $file->getFileUri()]), 404);
    }
  }

}
