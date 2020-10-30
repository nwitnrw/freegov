<?php

namespace Drupal\degov\Behat\Context\Traits;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Drupal\degov\Behat\Context\Exception\TextNotFoundException;
use WebDriver\Exception\StaleElementReference;

/**
 * Trait DebugOutputTrait.
 */
trait DebugOutputTrait {

  /**
   * Screenshot count.
   *
   * @var int
   */
  private $screenshotCount = 0;

  /**
   * Html dump count.
   *
   * @var int
   */
  private $htmlDumpCount = 0;

  /**
   * Generate step debugging output with scope.
   *
   * @AfterStep
   */
  public function generateStepDebuggingOutputWithScope(AfterStepScope $scope): void {
    if (!$scope->getTestResult()->isPassed()) {
      $this->saveHtmlOfPage($scope->getFeature()->getTitle());
      $this->saveScreenshotAsFile($scope->getFeature()->getTitle());
    }
  }

  /**
   * Close symphony toolbar.
   *
   * @AfterStep
   */
  public function closeSymfonyToolbar(AfterStepScope $scope): void {
    $this->getSession()->executeScript('if (document.querySelector(".sf-toolbar a.hide-button") !== null) { document.querySelector(".sf-toolbar a.hide-button").click(); }');
  }

  /**
   * Generate current browser view debugging output.
   */
  public function generateCurrentBrowserViewDebuggingOutput(string $name): void {
    $this->saveHtmlOfPage($name);
    $this->saveScreenshotAsFile($name);
  }

  /**
   * Save html of page.
   */
  public function saveHtmlOfPage(string $name): void {
    $dateTime = new \DateTime();
    $filename = $name . '-' . date_format($dateTime, 'H:i:s') . '.html';

    file_put_contents($this->computePath() . '/' . $filename, $this->getSession()->getPage()->getContent());
  }

  /**
   * Save screenshot as file.
   */
  public function saveScreenshotAsFile(string $name): void {
    $dateTime = new \DateTime();
    $filename = sprintf('%s-%s.png', $name, date_format($dateTime, 'H:i:s'));

    file_put_contents($this->computePath() . '/' . $filename, $this->getSession()->getScreenshot());
  }

  /**
   * Check errors with scope.
   *
   * @AfterStep
   */
  public function checkErrorsWithScope(AfterStepScope $scope): void {
    try {
      if (empty(self::$errorTexts)) {
        return;
      }

      foreach (self::$errorTexts as $errorText) {
        $pageText = $this->getSession()->getPage()->getText();
        if (substr_count($pageText, $errorText) > 0) {

          try {
            throw new TextNotFoundException(
              sprintf('Task failed due "%s" text on page \'', $pageText . '\''),
              $this->getSession()
            );
          }
          catch (TextNotFoundException $exception) {
            $this->generateCurrentBrowserViewDebuggingOutput($scope->getFeature()->getTitle());

            throw $exception;
          }
        }
      }

    }
    catch (StaleElementReference $exception) {
    }

  }

  /**
   * Compute path.
   */
  private function computePath(): string {
    if (!empty($path = getenv('CI_ROOT_DIR'))) {
      return $path;
    }

    if (\Drupal::hasContainer()) {
      return \Drupal::root() . '/..';
    }

    return __DIR__ . '/../../../../../../../../';
  }

}
