<?php

namespace Drupal\degov\Robo\Plugin\Commands\Exception\Performance;

/**
 * Class NoBehatFileException.
 */
class NoBehatFileException extends \Exception {

  public function __construct() {
    $message = 'Could not find any behat.yml file in the current writing directory. Please provide a behat.yml file.';

    parent::__construct($message);
  }

}
