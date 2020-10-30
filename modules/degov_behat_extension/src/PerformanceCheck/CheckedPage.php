<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\PerformanceCheck;

/**
 * Class CheckedPage.
 */
class CheckedPage {

  /**
   * @var string
   */
  private $uri;

  /**
   * @var int
   */
  private $visitingTime;

  /**
   * @return float
   */
  public function getVisitingTime(): float {
    return $this->visitingTime;
  }

  /**
   * @param float $visitingTime
   *
   * @return CheckedPage
   */
  public function setVisitingTime(float $visitingTime): CheckedPage {
    $this->visitingTime = $visitingTime;
    return $this;
  }

  /**
   * @return string
   */
  public function getUri(): string {
    return $this->uri;
  }

  /**
   * @param string $uri
   * @return \Drupal\degov_behat_extension\CheckedPage
   */
  public function setUri($uri): CheckedPage {
    $this->uri = $uri;
    return $this;
  }

}
