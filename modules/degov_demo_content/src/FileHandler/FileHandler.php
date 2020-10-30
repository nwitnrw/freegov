<?php

namespace Drupal\degov_demo_content\FileHandler;

use Drupal\degov_demo_content\FileAdapter;
use Drupal\degov_demo_content\Generator\ContentGenerator;
use Drupal\file\Entity\File;

/**
 * Class FileHandler.
 */
class FileHandler {

  /**
   * File adapter.
   *
   * @var \Drupal\degov_demo_content\FileAdapter
   */
  protected $fileAdapter;

  /**
   * Files.
   *
   * @var array
   */
  protected $files = [];

  /**
   * FileHandler constructor.
   *
   * @param \Drupal\degov_demo_content\FileAdapter $fileAdapter
   */
  public function __construct(FileAdapter $fileAdapter) {
    $this->fileAdapter = $fileAdapter;
  }

  /**
   * Get file.
   *
   * @param string $itemKey
   *   Media item key.
   *
   * @return \Drupal\file\Entity\File
   *   File.
   */
  public function getFile(string $itemKey): ?File {
    return $this->files[$itemKey];
  }

  /**
   * Get files.
   *
   * @param string $itemKey
   *   Media item key.
   *
   * @return array
   *   Files.
   */
  public function getFiles(string $itemKey): ?array {
    return $this->files[$itemKey]['files'];
  }

  /**
   * Add file.
   *
   * @param \Drupal\file\Entity\File $file
   *   File.
   * @param string $itemKey
   *   Media item key.
   */
  public function addFile(File $file, string $itemKey): void {
    $this->files[$itemKey] = $file;
  }

  /**
   * Add to files.
   *
   * @param \Drupal\file\Entity\File $file
   *   File.
   * @param string $itemKey
   *   Media item key.
   * @param string $fieldName
   *   Field name.
   */
  public function addToFiles(File $file, string $itemKey, string $fieldName): void {
    $this->files[$itemKey]['files'][$fieldName] = $file;
  }

  /**
   * Saves the files listed in the definitions as File entities.
   *
   * @param array $itemsToGenerate
   * @param string $fixturesPath
   */
  public function saveFiles(array $itemsToGenerate, string $fixturesPath): void {
    foreach ($itemsToGenerate as $itemKey => $item) {
      if (isset($item['file']) && $savedFile = $this->fileAdapter->fileSaveData(
          $this->fileAdapter->fileGetContents($fixturesPath . '/' . $item['file']),
          ContentGenerator::DEGOV_DEMO_CONTENT_FILES_SAVE_PATH . '/' . $item['file'])) {
        $this->addFile($savedFile, $itemKey);
      }

      if (isset($item['files'])) {
        foreach ($item['files'] as $fieldName => $fileName) {
          if ($savedFile = $this->fileAdapter->fileSaveData(
            $this->fileAdapter->fileGetContents($fixturesPath . '/' . $fileName),
            ContentGenerator::DEGOV_DEMO_CONTENT_FILES_SAVE_PATH . '/' . $fileName)
          ) {
            $this->addToFiles($savedFile, $itemKey, $fieldName);
          }
        }
      }
    }
  }

}
