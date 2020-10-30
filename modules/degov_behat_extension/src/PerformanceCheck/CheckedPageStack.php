<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\PerformanceCheck;

/**
 * Class CheckedPageStack.
 */
class CheckedPageStack {

  /**
   * @var CheckedPage[]
   */
  private $successfulPages = [];

  /**
   * @var CheckedPage[]
   */
  private $failedPages = [];

  /**
   * @return CheckedPage[]|null
   */
  public function getSuccessfulPages(): ?array {
    return $this->successfulPages;
  }

  /**
   * @param CheckedPage $successfulPages
   */
  public function addSuccessfulPage(CheckedPage $successfulPages): void {
    $this->successfulPages[] = $successfulPages;
  }

  /**
   * @return CheckedPage[]|null
   */
  public function getFailedPages(): ?array {
    return $this->failedPages;
  }

  /**
   * @param CheckedPage $failedPages
   */
  public function addFailedPage(CheckedPage $failedPages): void {
    $this->failedPages[] = $failedPages;
  }

}
