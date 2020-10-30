<?php

namespace Drupal\degov_theming\Facade;

use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\degov_theming\Factory\FilesystemFactory;
use Drupal\degov_theming\Service\DrupalPath;

/**
 * Class ComponentLocation.
 */
class ComponentLocation {

  /**
   * Library discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  private $libraryDiscovery;

  /**
   * Drupal path.
   *
   * @var \Drupal\degov_theming\Service\DrupalPath
   */
  private $drupalPath;

  /**
   * Filesystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $filesystem;

  /**
   * ComponentLocation constructor.
   */
  public function __construct(LibraryDiscovery $libraryDiscovery, FilesystemFactory $filesystemFactory, DrupalPath $drupalPath) {
    $this->libraryDiscovery = $libraryDiscovery;
    $this->filesystem = $filesystemFactory;
    $this->drupalPath = $drupalPath;
  }

  /**
   * Get library discovery.
   */
  public function getLibraryDiscovery() {
    return $this->libraryDiscovery;
  }

  /**
   * Get filesystem.
   */
  public function getFilesystem() {
    return $this->filesystem->create();
  }

  /**
   * Get drupal path.
   */
  public function getDrupalPath() {
    return $this->drupalPath;
  }

}
