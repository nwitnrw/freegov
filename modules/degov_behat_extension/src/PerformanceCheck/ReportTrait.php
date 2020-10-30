<?php

namespace Drupal\degov_behat_extension\PerformanceCheck;

/**
 * Trait ReportTrait.
 */
trait ReportTrait {

  public function buildMessageStringFromStack(string $message, array $checkPages): string {
    foreach ($checkPages as $page) {
      $uri = $page->getUri();
      $visitingTime = $page->getVisitingTime();
      $message .= <<<HERE
- uri: $uri -- visiting time: $visitingTime
HERE;
      $message .= PHP_EOL;
    }
    return $message;
  }

}
