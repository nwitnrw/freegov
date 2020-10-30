<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\Exception;

use Drupal\degov_behat_extension\PerformanceCheck\CheckedPageStack;
use Drupal\degov_behat_extension\PerformanceCheck\ReportTrait;

/**
 * Class SlowPagesMoreThan10PercentException.
 */
class SlowPagesMoreThan10PercentException extends \Exception {

  use ReportTrait;

  public function __construct(CheckedPageStack $checkedPageStack, float $percentOfFailedPages) {
    $message = "Fail: More then 10 percent of all pages need more than 2 seconds load time. Failed pages total: $percentOfFailedPages%" . PHP_EOL;

    if (!empty($successfulPages = $checkedPageStack->getSuccessfulPages())) {
      $message .= 'Successful pages:' . PHP_EOL;
      $message = $this->buildMessageStringFromStack($message, $successfulPages);
    }

    if (!empty($failedPages = $checkedPageStack->getFailedPages())) {
      $message .= 'Failed pages:' . PHP_EOL;
      $message = $this->buildMessageStringFromStack($message, $failedPages);
    }

    parent::__construct($message, 0, NULL);
  }

}
