<?php

namespace Drupal\degov\Robo\Plugin\Commands\Exception\Performance;

/**
 * Class ExistingScreenProcessesException.
 */
class ExistingScreenProcessesException extends \Exception {

  public function __construct() {
    $message = 'There are already Behat screen processes running. Please exit them as first. You can check them with the following command: "screen -list"';

    parent::__construct($message);
  }

}
