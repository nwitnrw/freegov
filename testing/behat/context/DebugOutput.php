<?php

namespace Drupal\degov\Behat\Context;

use Drupal\degov\Behat\Context\Exception\ErrorTextFoundException;
use Drupal\degov\Behat\Context\Traits\ErrorTrait;

/**
 * Bugtesting Class to detect false positives
 * @package Drupal\degov\Behat\Context
 */
class DebugOutput {

  use ErrorTrait;

  public function isErrorOnCurrentPage(string $pageText): bool {
    if (empty(self::$errorTexts)) {
      return FALSE;
    }

    foreach (self::$errorTexts as $errorText) {
      if (substr_count($pageText, $errorText) > 0) {
        throw new ErrorTextFoundException($errorText);
      }
    }
    return FALSE;
  }

}
