<?php

namespace Drupal\degov\Behat\Context;

use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Testwork\Hook\HookDispatcher;
use Drupal\degov\Behat\Context\Exception\TextNotFoundException;
use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;
use Drupal\degov\Behat\Context\Traits\TranslationTrait;
use WebDriver\Exception\StaleElementReference;

/**
 * Class DrupalIndependentContext.
 */
class DrupalIndependentContext extends RawMinkContext {

  use TranslationTrait;

  use DebugOutputTrait;

  private const MAX_DURATION_SECONDS = 600;
  private const MAX_SHORT_DURATION_SECONDS = 10;

  /**
   * Dispatcher.
   *
   * @var \Behat\Testwork\Hook\HookDispatcher
   */
  private $dispatcher;

  /**
   * {@inheritdoc}
   */
  public function setDispatcher(HookDispatcher $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Before scenario.
   *
   * @BeforeScenario
   */
  public function beforeScenario() {
    $this->iSetWindowSizeWidthHeight();
  }

  /**
   * Set window size width and height.
   *
   * @Then I set the window size to :width width and :height height
   * @Then I reset the window size
   */
  public function iSetWindowSizeWidthHeight(string $width = "1440px", string $height = "900px"): void {
    $this->getSession()->resizeWindow((int) $width, (int) $height);
  }

  /**
   * Should see text so many times.
   *
   * @Then /^I should see "([^"]*)" exactly "([^"]*)" times$/
   */
  public function iShouldSeeTextSoManyTimes($sText, $iExpected) {
    $sContent = $this->getSession()->getPage()->getText();
    $iFound = substr_count($sContent, $sText);
    if ($iExpected != $iFound) {

      try {
        throw new \Exception('Found ' . $iFound . ' occurences of "' . $sText . '" when expecting ' . $iExpected);
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
  }

  /**
   * Should see translated uppercase text after a while.
   *
   * @Then /^I should see text matching "([^"]*)" via translated text in uppercase after a while$/
   */
  public function iShouldSeeTranslatedUppercaseTextAfterWhile(string $text): bool {
    $translatedText = mb_strtoupper($this->translateString($text));

    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getText();
        if (substr_count($content, $translatedText) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new TextNotFoundException(
          sprintf('Could not find text %s after %s seconds', $translatedText, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (TextNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should see translated text after a while.
   *
   * @Then /^I should see text matching "([^"]*)" via translated text after a while$/
   */
  public function iShouldSeeTranslatedTextAfterWhile(string $text): bool {
    $translatedText = $this->translateString($text);

    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getText();
        if (substr_count($content, $translatedText) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new TextNotFoundException(
          sprintf('Could not find text %s after %s seconds', $translatedText, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (TextNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should see text after a while.
   *
   * @Then /^I should see text matching "([^"]*)" after a while$/
   */
  public function iShouldSeeTextAfterWhile(string $text): bool {
    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getText();
        if (substr_count($content, $text) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new TextNotFoundException(
          sprintf('Could not find text %s after %s seconds', $text, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (TextNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should see html content matching.
   *
   * @Then /^I should see HTML content matching "([^"]*)"$/
   * @Then /^I should see HTML content matching '([^']*)'$/
   */
  public function iShouldSeeHtmlContentMatching(string $content) {
    $html = $this->getSession()->getPage()->getHtml();
    if (substr_count($html, $content) > 0) {
      return TRUE;
    }

    try {
      throw new TextNotFoundException(
        sprintf('HTML does not contain content "%s"', $content),
        $this->getSession());
    }
    catch (TextNotFoundException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Should see html content translated matching.
   *
   * @Then /^I should see HTML content matching "([^"]*)" via translated text$/
   * @Then /^I should see HTML content matching '([^']*)' via translated text$/
   */
  public function iShouldSeeHtmlContentTranslatedMatching(string $content) {
    if (ctype_upper($content)) {
      $translatedText = mb_strtoupper($this->translateString($content));
    }
    else {
      $translatedText = $this->translateString($content);
    }

    $html = $this->getSession()->getPage()->getHtml();
    if (substr_count($html, $translatedText) > 0) {
      return TRUE;
    }

    try {
      throw new TextNotFoundException(
        sprintf('HTML does not contain content "%s"', $translatedText),
        $this->getSession());
    }
    catch (TextNotFoundException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Should see html content translated matching after a while.
   *
   * @Then /^I should see HTML content matching "([^"]*)" via translated text after a while$/
   * @Then /^I should see HTML content matching '([^']*)' via translated text after a while$/
   */
  public function iShouldSeeHtmlContentTranslatedMatchingAfterWhile(string $content): ?bool {
    if (ctype_upper($content)) {
      $translatedText = mb_strtoupper($this->translateString($content));
    }
    else {
      $translatedText = $this->translateString($content);
    }

    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getHtml();
        if (substr_count($content, $translatedText) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);
      throw new TextNotFoundException(
        sprintf('Could not find text %s after %s seconds', $translatedText, self::MAX_DURATION_SECONDS),
        $this->getSession()
      );
    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should  not see html content matching.
   *
   * @Then /^I should not see HTML content matching "([^"]*)"$/
   * @Then /^I should not see HTML content matching '([^']*)'$/
   */
  public function iShouldNotSeeHtmlContentMatching(string $content): ?bool {
    $html = $this->getSession()->getPage()->getHtml();
    if (substr_count($html, $content) === 0) {
      return TRUE;
    }

    try {
      throw new TextNotFoundException(
        sprintf('HTML does contain content "%s"', $content),
        $this->getSession());
    }
    catch (TextNotFoundException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Should see html content matching after while.
   *
   * @Then /^I should see HTML content matching "([^"]*)" after a while$/
   * @Then /^I should see HTML content matching '([^']*)' after a while$/
   */
  public function iShouldSeeHtmlContentMatchingAfterWhile($text) {
    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getHtml();
        if (substr_count($content, $text) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new TextNotFoundException(
          sprintf('Could not find text %s after %s seconds', $text, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (TextNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should not see text after a while.
   *
   * @Then /^I should not see text matching "([^"]*)" after a while$/
   * @Then /^I should not see text matching '([^']*)' after a while$/
   */
  public function iShouldNotSeeTextAfterWhile($text) {
    $startTime = time();
    do {
      $content = $this->getSession()->getPage()->getText();
      if (substr_count($content, $text) === 0) {
        return TRUE;
      }
    } while (time() - $startTime < self::MAX_SHORT_DURATION_SECONDS);

    try {
      throw new TextNotFoundException(
        sprintf('Could find text %s after %s seconds', $text, self::MAX_SHORT_DURATION_SECONDS),
        $this->getSession()
      );
    }
    catch (TextNotFoundException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Wait x seconds.
   *
   * @Then /^wait (\d+) seconds$/
   */
  public function waitSeconds($secondsNumber) {
    $this->getSession()->wait($secondsNumber * 1000);
  }

  /**
   * The html title should show the page title and the distribution title.
   *
   * @Then the HTML title should show the page title and the distribution title
   */
  public function theHtmlTitleShouldShowThePageTitleAndTheDistributionTitle() {
    return $this->elementWithSelectorShouldMatchPattern('css', 'html>head>title', "/^[^|]+ | [^|]+$/");
  }

  /**
   * Elements with selector should match pattern.
   */
  private function elementWithSelectorShouldMatchPattern($selector_type, $locator, $pattern) {
    $element = $this->getSession()->getPage()->find($selector_type, $locator);
    if (preg_match($pattern, $element->getHtml())) {
      return TRUE;
    }

    try {
      throw new TextNotFoundException(sprintf('The text of the element "%s" ("%s") did not match the pattern "%s"', $locator, $element->getHtml(), $pattern), $this->getSession());
    }
    catch (TextNotFoundException $exception) {

    }

  }

  /**
   * Xpath contains text.
   *
   * @Then /^I proof xpath "([^"]*)" contains text$/
   */
  public function xpathContainsText(string $xpath) {
    $page = $this->getSession()->getPage();
    /** @var $xpathNode \Behat\Mink\Element\NodeElement */
    $xpathNode = $page->find('xpath', $xpath);

    if (empty(\trim(\strip_tags($xpathNode->getHtml())))) {
      throw new \Exception("Xpath $xpath does not contain any text.");

    }
  }

  /**
   * Xpath contains specific text.
   *
   * @Then /^I proof xpath "([^"]*)" contains "([^"]*)" text via translation$/
   * @Then /^I proof xpath '([^']*)' contains "([^"]*)" text via translation$/
   */
  public function xpathContainsSpecificText(string $xpath, string $text) {
    $page = $this->getSession()->getPage();
    /** @var $xpathNode \Behat\Mink\Element\NodeElement */
    $xpathNode = $page->find('css', $xpath);

    if (!is_numeric(strpos($this->translateString($xpathNode->getText()), $text))) {
      throw new \Exception("Xpath $xpath does not contain text $text.");
    }
  }

  /**
   * Css contains text.
   *
   * @Then /^I proof css "([^"]*)" contains text$/
   */
  public function cssContainsText(string $css) {
    $page = $this->getSession()->getPage();
    /** @var $node \Behat\Mink\Element\NodeElement */
    $node = $page->find('css', $css);

    if (empty(\trim(\strip_tags($node->getHtml())))) {
      try {
        throw new \Exception("CSS $css does not contain any text.");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Css selector matches DOM node.
   *
   * @Then /^I proof css selector "([^"]*)" matches a DOM node$/
   */
  public function cssSelectorMatchesDomNode(string $css) {
    $page = $this->getSession()->getPage();
    /** @var $cssSelector NodeElement */
    $cssSelector = $page->find('css', $css);

    if (!$cssSelector instanceof NodeElement) {
      throw new \Exception("CSS selector $css is not of expected object type NodeElement.");
    }
  }

  /**
   * Checks, that (?P<num>\d+) CSS elements exist on the page for some time.
   *
   * Example: Then I should see 5 "div" elements after a while
   * Example: And I should see 5 "div" elements after a while.
   *
   * @Then /^(?:|I )should see (?P<num>\d+) "(?P<element>[^"]*)" elements? after a while$/
   */
  public function iShouldSeeNumberOfElementsAfterWhile(int $expectedNumberOfElements, string $selector, bool $negate = FALSE): void {
    $startTime = time();
    $wait = self::MAX_SHORT_DURATION_SECONDS * 2;
    do {
      $actualElements = $this->getSession()
        ->getPage()
        ->findAll('css', $selector);
      $actualNumberOfElements = \count($actualElements);

      if (!$negate) {
        if ($actualNumberOfElements === $expectedNumberOfElements) {
          return;
        }
      }
      else {
        if ($actualNumberOfElements !== $expectedNumberOfElements) {
          return;
        }
      }

    } while (time() - $startTime < $wait);
    throw new \Exception(
      sprintf('Could find %s %s elements after %s seconds, found %s', $expectedNumberOfElements, $selector, $wait, $actualNumberOfElements)
    );
  }

  /**
   * Checks, that (?P<num>\d+) CSS elements exist on the page for some time.
   *
   * Example: Then I should not see 5 "div" elements after a while
   * Example: And I should not see 5 "div" elements after a while.
   *
   * @Then /^(?:|I )should not see (?P<num>\d+) "(?P<element>[^"]*)" elements? after a while$/
   */
  public function iShouldNotSeeNumberOfElementsAfterWhile(int $expectedNumberOfElements, string $selector): void {
    self::iShouldSeeNumberOfElementsAfterWhile($expectedNumberOfElements, $selector, TRUE);
  }

  /**
   * Should see elements with selector and text.
   *
   * @Then I should see :number element(s) with the selector :selector and the translated text :text
   */
  public function iShouldSeeElementsWithSelectorAndText(int $expectedNumberOfElements, string $selector, string $text): void {
    $page = $this->getSession()->getPage();
    $matchedElements = $page->findAll('css', $selector);

    $matchedElementsCount = \count($matchedElements);
    if ($expectedNumberOfElements !== $matchedElementsCount) {
      throw new \Exception("Expected $expectedNumberOfElements elements matching $selector, found $matchedElementsCount");
    }
  }

  /**
   * Assert page address not equals.
   *
   * @Then /^(?:|I )should be not on "(?P<page>[^"]+)"$/
   */
  public function assertPageAddressNotEquals(string $page): void {
    $this->assertSession()->addressNotEquals($this->locatePath($page));
  }

  /**
   * Dump html.
   *
   * @Then I dump the HTML of the current page
   */
  public function dumpHtml() {
    print_r($this->getSession()->getPage()->getContent());
  }

  /**
   * Trigger php function.
   *
   * @Then I trigger the PHP function :functionName
   */
  public function triggerPhpFunction($functionName): void {
    if (\function_exists($functionName)) {
      $functionName();
    }
    else {
      throw new \Exception('Function ' . $functionName . ' does not exist.');
    }
  }

  /**
   * @Then /^I (?:am|should be) redirected to "([^"]*)"$/
   */
  public function iAmRedirectedTo($actualPath) {
    // Ignoring trailing slashes.
    $actualPath = rtrim($actualPath, '/');
    $pageUrl = rtrim($this->getSession()->getCurrentUrl(), '/');
    if ($pageUrl !== $actualPath) {
      throw new \Exception("Expected to be on $actualPath after being redirected, but I am on $pageUrl");
    }
  }

}
