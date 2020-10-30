<?php

namespace Drupal\degov_theming\Service;

/**
 * Class DrupalPath.
 */
class DrupalPath {

  /**
   * Get path.
   */
  public function getPath(string $type, string $name) {
    return drupal_get_path($type, $name);
  }

}
