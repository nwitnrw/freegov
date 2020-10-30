<?php

namespace Drupal\Tests\degov_behat_extension\Unit;

use Drupal\degov_behat_extension\Exception\SlowPagesMoreThan10PercentException;
use Drupal\degov_behat_extension\PerformanceCheck\StaticRequirementTimer;
use Drupal\degov_behat_extension\PerformanceCheck\TestReportGenerator;
use Drupal\Tests\UnitTestCase;

/**
 * Class StaticRequirementTimerTest.
 */
class StaticRequirementTimerTest extends UnitTestCase {

  /**
   * @doesNotPerformAssertions
   */
  public function testPositiveHandleStaticPagesRequirementByOnePage(): void {
    $requirementChecker = new StaticRequirementTimer(0.2);

    $requirementChecker->startMeasuringOnePageVisit();
    usleep(100000);
    $requirementChecker->finishMeasuringOnePageVisit('http://example.com');

    $testReportGenerator = new TestReportGenerator($requirementChecker->getCheckedPageStack());
    $testReportGenerator->evaluateResults(FALSE);
  }

  public function testNegativeHandleStaticPagesRequirementByOnePage(): void {
    $this->expectException(SlowPagesMoreThan10PercentException::class);
    $requirementChecker = new StaticRequirementTimer(0.1);

    $requirementChecker->startMeasuringOnePageVisit();
    usleep(200000);
    $requirementChecker->finishMeasuringOnePageVisit('http://example.com');

    $testReportGenerator = new TestReportGenerator($requirementChecker->getCheckedPageStack());
    $testReportGenerator->evaluateResults(FALSE);
  }

  public function testNegativeEvaluationByMultiplePages(): void {
    $this->expectException(SlowPagesMoreThan10PercentException::class);
    $requirementChecker = new StaticRequirementTimer(0.1);

    for ($i = 1; $i <= 10; ++$i) {
      $requirementChecker->startMeasuringOnePageVisit();
      usleep(200000);
      $requirementChecker->finishMeasuringOnePageVisit('http://example.com');
    }

    try {
      $testReportGenerator = new TestReportGenerator($requirementChecker->getCheckedPageStack());
      $testReportGenerator->evaluateResults(FALSE);
    }
    catch (SlowPagesMoreThan10PercentException $exception) {
      self::assertContains('uri: http://example.com ', $exception->getMessage());
      throw $exception;
    }
  }

  /**
   * @doesNotPerformAssertions
   */
  public function testPositiveEvaluationByMultiplePages(): void {
    $requirementChecker = new StaticRequirementTimer(0.3);

    for ($i = 1; $i <= 10; ++$i) {
      $requirementChecker->startMeasuringOnePageVisit();
      usleep(70000);
      $requirementChecker->finishMeasuringOnePageVisit('http://example.com');
    }

    $testReportGenerator = new TestReportGenerator($requirementChecker->getCheckedPageStack());
    $testReportGenerator->evaluateResults(FALSE);
  }

}
