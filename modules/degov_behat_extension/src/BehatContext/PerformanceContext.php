<?php

declare(strict_types=1);

namespace Drupal\degov_behat_extension\BehatContext;

use Drupal\degov_behat_extension\PerformanceCheck\StaticRequirementTimer;
use Drupal\degov_behat_extension\PerformanceCheck\TestReportGenerator;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class PerformanceContext.
 */
class PerformanceContext extends RawDrupalContext {

  /**
   * @Then /^I visit static pages and expect fulfillment of performance requirement$/
   */
  public function visitStaticPagesAndExpectFulfillmentOfPerformanceRequirement(): void {
    $staticRequirementTimer = new StaticRequirementTimer();

    /**
     * @var \Drupal\degov_behat_extension\PerformanceCheck\StaticUrisFetcher $staticUrisFetcher
     */
    $staticUrisFetcher = \Drupal::service('degov_behat_extension.static_uris_fetcher');
    $uris = $staticUrisFetcher->provideUris();

    foreach ($uris as $uri) {
      $staticRequirementTimer->startMeasuringOnePageVisit();
      $this->visitPath($uri);
      $staticRequirementTimer->finishMeasuringOnePageVisit($uri);
    }

    $testReportGenerator = new TestReportGenerator($staticRequirementTimer->getCheckedPageStack());
    $testReportGenerator->evaluateResults();
  }

  /**
   * @Then /^I visit static pages$/
   */
  public function visitStaticPages(): void {
    /**
     * @var \Drupal\degov_behat_extension\PerformanceCheck\StaticUrisFetcher $staticUrisFetcher
     */
    $staticUrisFetcher = \Drupal::service('degov_behat_extension.static_uris_fetcher');
    $uris = $staticUrisFetcher->provideUris();

    foreach ($uris as $uri) {
      $this->visitPath($uri);
    }
  }

  /**
   * @Then /^I am warming the cache of the static pages$/
   */
  public function warmStaticPagesCache(): void {
    $this->visitStaticPages();
  }

}
