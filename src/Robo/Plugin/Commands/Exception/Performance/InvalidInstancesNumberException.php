<?php

namespace Drupal\degov\Robo\Plugin\Commands\Exception\Performance;

/**
 * Class InvalidInstancesNumberException.
 */
class InvalidInstancesNumberException extends \Exception {

  public function __construct() {
    $message = 'Only positive numeric values are allowed as instance number.';

    parent::__construct($message);
  }

}
