<?php

namespace Drupal\degov_theming\Factory;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FilesystemFactory.
 */
class FilesystemFactory {

  /**
   * Create.
   *
   * @return \Symfony\Component\Filesystem\Filesystem
   *   File system.
   */
  public function create() {
    return new Filesystem();
  }

}
