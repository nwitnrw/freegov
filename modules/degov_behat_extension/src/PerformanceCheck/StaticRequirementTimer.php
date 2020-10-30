<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\PerformanceCheck;

use Drupal\degov_behat_extension\Exception\PageloadTimeoutException;

/**
 * Class StaticRequirementTimer.
 */
class StaticRequirementTimer {

  /**
   * @var float
   */
  private $requestTimeLimitInSeconds;

  /**
   * @var float
   */
  private $startTimestampInMilliseconds = 0.0;

  /**
   * @var int
   */
  private $numTotalPages = 0;

  /**
   * @var CheckedPageStack[]
   */
  private $checkedPageStack;

  public function __construct(float $requestTimeLimitInSeconds = 2.0) {
    $this->checkedPageStack = new CheckedPageStack();
    $this->requestTimeLimitInSeconds = $requestTimeLimitInSeconds;
  }

  /**
   * @return CheckedPageStack
   */
  public function getCheckedPageStack(): CheckedPageStack {
    return $this->checkedPageStack;
  }

  public function startMeasuringOnePageVisit(): void {
    $this->startTimestampInMilliseconds = microtime(TRUE);
  }

  public function finishMeasuringOnePageVisit(string $uri): void {
    $this->numTotalPages++;
    $visitingTime = microtime(TRUE) - $this->startTimestampInMilliseconds;

    $checkedPage = new CheckedPage();
    $checkedPage
      ->setUri($uri)
      ->setVisitingTime($visitingTime);

    if ($visitingTime > $this->requestTimeLimitInSeconds) {
      try {
        throw new PageloadTimeoutException();
      }
      catch (PageloadTimeoutException $exception) {
        $this->checkedPageStack->addFailedPage($checkedPage);
      }
    }
    else {
      $this->checkedPageStack->addSuccessfulPage($checkedPage);
    }

  }

}
