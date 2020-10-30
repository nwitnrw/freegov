<?php

namespace degov\Scripts\Robo\Exception;

use Throwable;

/**
 * Class NoInstallationProfileProvided.
 */
class NoInstallationProfileProvided extends \Exception {

  /**
   * NoInstallationProfileProvided constructor.
   */
  public function __construct($message = '', $code = 0, Throwable $previous = NULL) {
    if (!$message) {
      $message = 'You must provide an installation profile object.';
    }

    parent::__construct($message, $code, $previous);
  }

}
