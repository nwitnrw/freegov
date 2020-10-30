<?php

namespace Drupal\degov\Robo\Plugin\Commands\Exception\Performance;

/**
 * Class AlreadyExistingScreenLogfileException.
 */
class AlreadyExistingScreenLogfileException extends \Exception {

  public function __construct() {
    $message = 'There is an already existing screen logfile in the current writing directory (screenlog.0). Please remove it as first.';

    parent::__construct($message);
  }

}
