<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\PerformanceCheck;

use Drupal\degov_behat_extension\Exception\SlowPagesMoreThan10PercentException;

/**
 * Class TestReportGenerator.
 */
class TestReportGenerator {

  use ReportTrait;

  /**
   * @var CheckedPageStack
   */
  private $checkedPageStack;

  public function __construct(CheckedPageStack $checkedPageStack) {
    $this->checkedPageStack = $checkedPageStack;
  }

  public function evaluateResults(bool $printOutput = TRUE): void {
    $countFailedPages = count($this->checkedPageStack->getFailedPages());
    $countSuccessfulPages = count($this->checkedPageStack->getSuccessfulPages());

    $numTotalPages = $countFailedPages + $countSuccessfulPages;

    $percentOfFailedPages = ($countFailedPages / ($numTotalPages * 0.01));

    if ($percentOfFailedPages > 10) {
      throw new SlowPagesMoreThan10PercentException($this->checkedPageStack, $percentOfFailedPages);
    }

    $message = "Success: less than 10 percent of all pages need more than 2 seconds load time. Failed pages total: $percentOfFailedPages%" . PHP_EOL;
    if (!empty($successfulPages = $this->checkedPageStack->getSuccessfulPages())) {
      $message .= 'Successful pages:' . PHP_EOL;
      $message = $this->buildMessageStringFromStack($message, $successfulPages);
    }

    if (!empty($failedPages = $this->checkedPageStack->getFailedPages())) {
      $message .= 'Failed pages:' . PHP_EOL;
      $message = $this->buildMessageStringFromStack($message, $failedPages);
    }

    if ($printOutput) {
      print $message;
    }

  }

}
