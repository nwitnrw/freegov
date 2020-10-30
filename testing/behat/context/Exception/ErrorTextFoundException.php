<?php

namespace Drupal\degov\Behat\Context\Exception;

use Throwable;

/**
 * Simple exception without input param to test throwing an exception
 * @package Drupal\degov\Behat\Context\Exception
 */
class ErrorTextFoundException extends \Exception {

  public function __construct(string $errorText, $code = 0, Throwable $previous = NULL) {
    parent::__construct($errorText, $code, $previous);
  }

}
