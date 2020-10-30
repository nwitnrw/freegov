<?php

namespace Drupal\degov\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;

/**
 * Class JavaScriptContext.
 */
class JavaScriptContext extends RawMinkContext {

  /**
   * The max duration in seconds.
   *
   * @var int
   */
  private const MAX_DURATION_SECONDS = 1200;

  use DebugOutputTrait;

  /**
   * Select index in dropdown.
   *
   * @Then /^I select index (\d+) in dropdown named "([^"]*)"$/
   */
  public function selectIndexInDropdown($index, $name) {
    $this->getSession()->executeScript('jQuery("[name=' . $name . ']:first option").removeProp("selected"); jQuery("[name=' . $name . ']:first option:eq(' . $index . ')").prop("selected", "selected").trigger("change");');
  }

  /**
   * Scroll to element with id.
   *
   * @param string $id
   *   ID.
   *
   * @Then /^I scroll to element with id "([^"]*)"$/
   */
  public function iScrollToElementWithId($id) {
    $this->getSession()->executeScript(
      "
                var element = document.getElementById('" . $id . "');
                element.scrollIntoView( true );
            "
    );
  }

  /**
   * Click by selector.
   *
   * @param string $selector
   *   Selector.
   *
   * @Then /^I click by selector "([^"]*)" via JavaScript$/
   */
  public function clickBySelector(string $selector) {
    $this->getSession()->executeScript("document.querySelector('" . addslashes($selector) . "').click()");
  }

  /**
   * Css selector has html attribute that matches value.
   *
   * @Then /^I prove css selector "([^"]*)" has HTML attribute "([^"]*)" that matches value "([^"]*)"$/
   */
  public function cssSelectorHasHtmlAttributeThatMatchesValue($selector, $attribute, $value) {
    if (preg_match("/$value/", $this->getSession()->evaluateScript("jQuery('$selector').attr('$attribute')"))) {
      return TRUE;
    }
    else {
      try {
        throw new \Exception("CSS selector $selector does not have attribute '$attribute' matching '$value'");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * CSS selector attribute matches value.
   *
   * @Then /^I proof css selector "([^"]*)" has attribute "([^"]*)" with value "([^"]*)"$/
   */
  public function cssSelectorAttributeMatchesValue($selector, $attribute, $value) {
    if ($this->getSession()
      ->evaluateScript("jQuery('$selector').css('$attribute')") === $value) {
      return TRUE;
    }
    else {
      try {
        throw new \Exception("CSS selector $selector does not match attribute '$attribute' with value '$value'");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Scroll to bottom.
   *
   * @Then /^I scroll to bottom$/
   */
  public function iScrollToBottom(): void {
    $this->getSession()
      ->executeScript('window.scrollTo(0,document.body.scrollHeight);');
  }

  /**
   * Scroll  to top.
   *
   * @Then /^I scroll to top$/
   */
  public function iScrollToTop(): void {
    $this->getSession()
      ->executeScript('window.scrollTo(0,0);');
  }

  /**
   * Focus on the iframe with ID.
   *
   * @Then I focus on the Iframe with ID :arg1
   */
  public function iFocusOnTheIframeWithId($iframe_id) {
    $this->getSession()->switchToIFrame($iframe_id);
  }

  /**
   * Switches out of an frame, into the main window.
   *
   * @When I go back to the main window
   */
  public function exitFrame() {
    $this->getSession()->switchToWindow();
  }

  /**
   * Verify that field has the value.
   *
   * @Then I verify that field :selector has the value :value
   */
  public function iVerifyThatFieldHasTheValue($selector, $value): bool {
    $actual_value = $this->getSession()->evaluateScript("jQuery('" . addslashes($selector) . "').val()");
    if ($actual_value === $value) {
      return TRUE;
    }

    try {
      throw new \Exception("Element matching selector '$selector' does not have the expected value '$value'. Has: $actual_value");
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Verify that field value matches.
   *
   * @Then I verify that field value of :selector matches :value
   */
  public function iVerifyThatFieldValueMatches(string $selector, string $value): bool {
    $actualValue = $this->getSession()->evaluateScript("jQuery('" . $selector . "').val()");
    if (preg_match('/' . $value . '/', $actualValue)) {
      return TRUE;
    }

    try {
      throw new \Exception("Element matching selector '$selector' does not have the expected value '$value'.");
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Should see elements via javascript.
   *
   * @Then I should see :number :selector elements via JavaScript
   */
  public function iShouldSeeElementsViaJavaScript(int $number, string $selector) {
    $numberOfElementsFound = (int) $this->getSession()->evaluateScript("document.querySelectorAll('" . $selector . "').length");
    if ($numberOfElementsFound === $number) {
      return TRUE;
    }

    try {
      throw new \Exception($numberOfElementsFound . ' elements matching css ' . $selector . ' found on the page, but should be ' . $number);
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Set the value of element via javascript.
   *
   * @Then I set the value of element :selector to :value via JavaScript
   */
  public function iSetTheValueOfElementViaJavascript(string $selector, string $value) {
    $this->getSession()->evaluateScript(sprintf("jQuery('%s').val('%s').trigger('change');", $selector, $value));
  }

  /**
   * Should see elements via jquery.
   *
   * @Then I should see :number :selector elements via jQuery
   */
  public function iShouldSeeElementsViaJquery(int $number, string $selector) {
    $numberOfElementsFound = (int) $this->getSession()->evaluateScript("jQuery('" . $selector . "').length");
    if ($numberOfElementsFound === $number) {
      return TRUE;
    }

    try {
      throw new \Exception($numberOfElementsFound . ' elements matching css ' . $selector . ' found on the page, but should be ' . $number);
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Should see elements via jquery after a while.
   *
   * @Then I should see :number :selector elements via jQuery after a while
   */
  public function iShouldSeeElementsViaJqueryAfterWhile(int $number, string $selector) {
    $startTime = time();
    do {
      $numberOfElementsFound = (int) $this->getSession()->evaluateScript("jQuery('" . $selector . "').length");
      if ($numberOfElementsFound === $number) {
        return TRUE;
      }
    } while (time() - $startTime < self::MAX_DURATION_SECONDS);

    try {
      throw new \Exception($numberOfElementsFound . ' elements matching css ' . $selector . ' found on the page after ' . self::MAX_DURATION_SECONDS . ' seconds, but should be ' . $number);

    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Trigger event on element.
   *
   * @Then I trigger the :event event on :selector
   */
  public function iTriggerEventOnElement(string $event, string $selector): void {
    $this->getSession()->evaluateScript('jQuery("' . $selector . '").trigger("' . $event . '")');
  }

  /**
   * Element has the style attribute with values.
   *
   * @Then element :elementSelector has the style attribute :styleAttribute with value :styleValue
   */
  public function elementHasTheStyleAttributeWithValue($elementSelector, $styleAttribute, $styleValue) {
    $actualValue = $this->getSession()->evaluateScript('jQuery(\'' . $elementSelector . '\').css(\'' . $styleAttribute . '\');');

    if ($styleValue === $actualValue) {
      return TRUE;
    }

    try {
      throw new \Exception("Expected element " . $elementSelector . " to have CSS " . $styleAttribute . "=" . $styleValue . ", actual value was " . $actualValue);
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * @Then I check that :selector video is playing after a while
   */
  public function iCheckVideoIsPlayingAfterWhile(string $selector): ?bool {
    $startTime = time();
    $this->getSession()->evaluateScript('video = jQuery("' . $selector . '").get(0)');
    do {
      $playing = $this->getSession()->evaluateScript('!!(video.currentTime > 0 && !video.paused && !video.ended && video.readyState > 2)');
      if ($playing) {
        return TRUE;
      }
    } while (time() - $startTime < self::MAX_DURATION_SECONDS);

    $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
    throw new \Exception('"' . $selector . '" video is not playing after ' . self::MAX_DURATION_SECONDS . ' seconds');
  }

  /**
   * @Then I check that :selector video is paused after a while
   */
  public function iCheckVideoIsPausedAfterWhile(string $selector): ?bool {
    $startTime = time();
    $this->getSession()->evaluateScript('video = jQuery("' . $selector . '").get(0)');
    do {
      $paused = $this->getSession()->evaluateScript('!!(video.currentTime > 0 && video.paused && !video.ended && video.readyState > 2)');
      if ($paused) {
        return TRUE;
      }
    } while (time() - $startTime < self::MAX_DURATION_SECONDS);

    $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
    throw new \Exception('"' . $selector . '" video is not paused after ' . self::MAX_DURATION_SECONDS . ' seconds');
  }

  /**
   * @Then I add a mock play button after :selector
   */
  public function addMockPlayButtonAfterElement($selector) {
    $this->getSession()->executeScript("jQuery('" . $selector . "').after('<div id=\"mock-play\" onclick=\"document.querySelector(\'.slick-current video\').play();\">mock play</div>')");
  }

}
