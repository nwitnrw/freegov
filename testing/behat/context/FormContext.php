<?php

namespace Drupal\degov\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;
use Drupal\degov\Behat\Context\Traits\TranslationTrait;

/**
 * Class FormContext.
 */
class FormContext extends RawMinkContext {

  use TranslationTrait;

  use DebugOutputTrait;

  /**
   * Check checkbox with js.
   *
   * @param string $id
   *   ID.
   *
   * @Then /^I check checkbox with id "([^"]*)" by JavaScript$/
   */
  public function checkCheckboxWithJs($id) {
    $this->getSession()->executeScript(
      "
                document.getElementById('" . $id . "').checked = true;
            "
    );
  }

  /**
   * Check checkbox by selector.
   *
   * @param string $selector
   *   Selector.
   *
   * @Then /^I check checkbox by selector "([^"]*)" via JavaScript$/
   */
  public function checkCheckboxBySelector(string $selector) {
    $this->getSession()->executeScript(
      "
                document.querySelector('" . $selector . "').checked = true;
            "
    );
  }

  /**
   * Click checkbox by name attribute value.
   *
   * @param string $selector
   *   Selector.
   *
   * @Then /^I click checkbox by name attribute value "([^"]*)"$/
   */
  public function clickCheckboxByNameAttributeValue(string $selector): void {
    $this->getSession()->executeScript(
      "
                jQuery('input[name=\"$selector\"').click();
            "
    );
  }

  /**
   * Check checkbox by value.
   *
   * @param string $value
   *   Value.
   *
   * @Then /^I check checkbox by value "([^"]*)" via JavaScript$/
   */
  public function checkCheckboxByValue(string $value) {
    $this->getSession()->executeScript(
      "
                document.querySelector('input[value=" . $value . "]').checked = true;
            "
    );
  }

  /**
   * Fill in textarea with.
   *
   * @Given /^I fill in Textarea with "([^"]*)"$/
   */
  public function iFillInTextareaWith($arg1) {

    $this->getSession()
      ->executeScript('jQuery("div.form-textarea-wrapper iframe").contents().find("p").text("' . $arg1 . '")');

  }

  /**
   * Submit a form by ID.
   *
   * @Given /^I submit a form by id "([^"]*)"$/
   */
  public function iSubmitFormById($id) {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', "form#${id}");
    $element->submit();
  }

  /**
   * Should not see the option in.
   *
   * @param string $value
   *   Value.
   * @param string $id
   *   ID.
   *
   * @Given /^I should not see the option "([^"]*)" in "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iShouldNotSeeTheOptionIn($value, $id) {
    $page = $this->getSession()->getPage();
    /** @var $selectElement \Behat\Mink\Element\NodeElement */
    $selectElement = $page->find('xpath', '//select[@id = "' . $id . '"]');
    $element = $selectElement->find('css', 'option[value=' . $value . ']');
    if ($element) {
      try {
        throw new \Exception("There is an option with the value '$value' in the select '$id'");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Check checkbox.
   *
   * @param string $id
   *   ID.
   *
   * @Then /^I check checkbox with id "([^"]*)"$/
   */
  public function checkCheckbox($id) {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//input[@id = "' . $id . '"]');

    $selectElement->check();
  }

  /**
   * Uncheck checkbox.
   *
   * @param string $id
   *   ID.
   *
   * @Then /^I uncheck checkbox with id "([^"]*)"$/
   */
  public function uncheckCheckbox($id) {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//input[@id = "' . $id . '"]');

    $selectElement->uncheck();
  }

  /**
   * Submit the form.
   *
   * @Then /^I submit the form$/
   */
  public function iSubmitTheForm() {
    // Get the mink session.
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()
        ->selectorToXpath('xpath', '//*[@type="submit"]')
    // Runs the actual query and returns the element.
    );

    // Errors must not pass silently.
    if (NULL === $element) {
      try {
        throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', '//*[@type="submit"]'));
      }
      catch (\InvalidArgumentException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }

    // ok, let's click on it.
    $element->click();
  }

  /**
   * Select option.
   *
   * @Then /^I select "([^"]*)" in "([^"]*)"$/
   */
  public function selectOption($label, $id) {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//select[@id = "' . $id . '"]');
    $selectElement->selectOption($label);
  }

  /**
   * Select via label by name attribute.
   *
   * @Then /^I select by label "([^"]*)" via name attribute value "([^"]*)"$/
   */
  public function selectViaLabelByNameAttribute(string $label, string $nameAttribute): void {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//select[@name = "' . $nameAttribute . '"]');
    $selectElement->selectOption($label);
  }

  /**
   * Select option via translation.
   *
   * @Then /^I select "([^"]*)" in "([^"]*)" via translated text$/
   * @Then /^I select "([^"]*)" via translation in "([^"]*)"$/
   */
  public function selectOptionViaTranslation($label, $id) {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//select[@id = "' . $id . '"]');
    $selectElement->selectOption($this->translateString($label));
  }

  /**
   * Select option by name.
   *
   * @Then /^I select "([^"]*)" by name "([^"]*)"$/
   */
  public function selectOptionByName(string $label, string $name): void {
    $page = $this->getSession()->getPage();
    $selectElement = $page->find('xpath', '//select[@name = "' . $name . '"]');
    $selectElement->selectOption($label);
  }

  /**
   * Assert date field reflects current date.
   *
   * @Then /^I assert date field with name attribute value "([^"]*)" contains current date$/
   */
  public function assertDateFieldReflectsCurrentDate(string $nameAttributeValue): ?bool {
    $date = $this->getSession()->evaluateScript(
      "
              return jQuery('input[name=\"$nameAttributeValue\"').val();
            "
    );

    $expectedDate = date('Y-m-d');

    if ($date === $expectedDate) {
      return TRUE;
    }

    throw new \Exception("Date field named '$nameAttributeValue' does not reflect the current date. Expected date was '$expectedDate'. Got the following date: '$date'.");
  }

  /**
   * Assert input field with name attribute value is empty.
   *
   * @Then /^I assert input field with name attribute value "([^"]*)" is empty$/
   */
  public function assertInputFieldWithNameAttributeValueIsEmpty(string $nameAttributeValue): ?bool {
    $value = $this->getSession()->evaluateScript(
      "
              return jQuery('input[name=\"$nameAttributeValue\"').val();
            "
    );

    if (empty($value)) {
      return TRUE;
    }

    throw new \Exception("Form field named '$nameAttributeValue' is expected to be empty. Got the following value: '$value'.");
  }

  /**
   * Assert date field reflects expected date.
   *
   * @Then /^I assert date field with name attribute value "([^"]*)" contains the date value "([^"]*)"$/
   */
  public function assertDateFieldReflectsExpectedDate(string $nameAttributeValue, string $expectedDate): ?bool {
    $date = $this->getSession()->evaluateScript(
      "
              return jQuery('input[name=\"$nameAttributeValue\"').val();
            "
    );

    if ($date === $expectedDate) {
      return TRUE;
    }

    throw new \Exception("Date field named '$nameAttributeValue' does not reflect the expected date. Expected date was '$expectedDate'. Got the following date: '$date'.");
  }

  /**
   * Assert date field contains date value.
   *
   * @Then /^I assert date field with name attribute value "([^"]*)" contains date value "([^"]*)"$/
   */
  public function assertDateFieldContainsDateValue(string $nameAttributeValue): ?bool {
    $date = $this->getSession()->evaluateScript(
      "
              return jQuery('input[name=\"$nameAttributeValue\"').val();
            "
    );

    $expectedDate = date('Y-m-d');

    if ($date === $expectedDate) {
      return TRUE;
    }

    throw new \Exception("Date field named '$nameAttributeValue' does not reflect the current date. Expected date was '$expectedDate'. Got the following date: '$date'.");
  }

  /**
   * Assert dropdown.
   *
   * @Then /^I assert dropdown named "([^"]*)" contains the following text-value pairs:$/
   *
   * Provide data in the following format:
   *
   * | text                | value       |
   * | Teaser kleines Bild | small_image |
   * | Teaser langer Text  | long_text   |
   * | Teaser schmal       | slim        |
   * | Vorschau            | preview     |
   */
  public function assertDropdown(string $nameAttributeValue, TableNode $table): void {
    $rowsHash = $table->getRowsHash();
    unset($rowsHash['text']);

    $selector = "select[name='$nameAttributeValue']";
    $node = $this->getSession()->getPage()->find('css', $selector);

    if (NULL === $node) {
      if (is_array($selector)) {
        $selector = implode(' ', $selector);
      }

      try {
        throw new ElementNotFoundException($this->getSession()
          ->getDriver(), 'element', 'css', $selector);
      }
      catch (ElementNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }

    $html = $node->getHtml();

    $htmlParts = explode('</option>', $html);
    if (strpos($html, 'value="_none"')) {
      array_shift($htmlParts);
    }
    array_pop($htmlParts);

    if (count($htmlParts) !== count($rowsHash)) {
      print_r($rowsHash);

      try {
        throw new \Exception(sprintf('Table items number does not match found option values number. Expected %s, found %s', count($rowsHash), count($htmlParts)));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }

    foreach ($rowsHash as $text => $value) {
      $found = FALSE;
      $htmlPartItems = count($htmlParts) - 1;
      for ($i = 0; $i <= $htmlPartItems; ++$i) {
        if (strpos($htmlParts[$i], $text) && (empty($value) || strpos($htmlParts[$i], $value))) {
          $found = TRUE;
        }
      }
      if ($found === FALSE) {
        try {
          throw new \Exception("Text '$text' and value '$value' not found in given options.");
        }
        catch (\Exception $exception) {
          $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
          throw $exception;
        }

      }
    }
  }

  /**
   * Assert dropbutton.
   *
   * @Then /^I assert dropbutton actions with css selector "([^"]*)" contains the following name-value pairs:$/
   *
   * Provide data in the following format:
   *
   * value | name
   * FAQ hinzufügen |
   *   field_content_paragraphs_faq_add_more
   * FAQ / Akkordion Liste hinzufügen |
   *   field_content_paragraphs_faq_list_add_more
   * Medienreferenz hinzufügen |
   *   field_content_paragraphs_media_reference_add_more
   */
  public function assertDropbutton(string $cssSelector, TableNode $table): void {
    $rowsHash = $table->getRowsHash();
    unset($rowsHash['text']);

    $node = $this->getSession()->getPage()->find('css', $cssSelector);

    if (NULL === $node) {
      throw new ElementNotFoundException($this->getSession()->getDriver(), 'element', 'css', $cssSelector);
    }

    $html = $node->getHtml();

    $htmlParts = explode('</li>', $html);

    // Remove last element which is empty.
    array_pop($htmlParts);

    if (count($htmlParts) !== count($rowsHash) - 1) {
      print_r($htmlParts);

      try {
        throw new \Exception(sprintf('Table items number does not match found option values number. (expected: %s, found: %s)', (count($rowsHash) - 1), count($htmlParts)));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }

    foreach ($rowsHash as $text => $value) {
      $found = FALSE;
      foreach ($htmlParts as $htmlPart) {
        if (strpos($htmlPart, $text) && strpos($htmlPart, $value)) {
          $found = TRUE;
        }
      }

      if ($found === FALSE) {
        try {
          throw new \Exception("Text '$text' and value '$value' not found in given options.");
        }
        catch (\Exception $exception) {
          $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
          throw $exception;
        }

      }
    }
  }

  /**
   * Press button translate.
   *
   * @When /^I press button with label "([^"]*)" via translated text$/
   * @When /^I click button with label "([^"]*)" via translated text$/
   */
  public function pressButtonTranslate(string $button) {
    $this->getSession()->getPage()->pressButton($this->translateString($button));
  }

  /**
   * Should see the input with the name and the value checked.
   *
   * @Then I should see the input with the name :input_name and the value :input_value checked
   */
  public function iShouldSeeTheInputWithTheNameAndTheValueChecked(string $input_name, string $input_value) {
    $radio_button = $this
      ->getSession()
      ->getPage()
      ->findAll('xpath', '//input[@name and contains(@name, "' . $input_name . '") and @value and @value="' . $input_value . '" and @checked and @checked="checked"]');

    if (count($radio_button) > 0) {
      return TRUE;
    }

    try {
      throw new \Exception(sprintf('Element "%s" with value "%s" not found!', $input_name, $input_value));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Select has following options.
   *
   * @Given /^Select "([^"]*)" has following options "([^"]*)"$/
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function selectHasFollowingOptions($select, $optionsRaw) {

    $select = $this->getSession()
      ->getPage()
      ->find('css', 'select[name="' . $select . '"]');

    $options = explode(' ', $optionsRaw);
    foreach ($options as $option) {
      $element = $select->find('css', 'option[value="' . $option . '"]');
      if (!$element) {
        try {
          throw new ElementNotFoundException($this->getSession(), 'custom', 'option[value="' . $option . '"]', 'css');
        }
        catch (ElementNotFoundException $exception) {
          $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
          throw $exception;
        }

      }
    }
  }

  /**
   * Fills in form field with specified id|name|label|value.
   *
   * Example: When I fill in "username" with: "bwayne"
   * Example: And I fill in "bwayne" for "username".
   *
   * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" via translated text with "(?P<value>(?:[^"]|\\")*)"$/
   * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" via translated text with:$/
   * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" via translated text for "(?P<field>(?:[^"]|\\")*)"$/
   */
  public function fillField($field, $value) {
    $field = $this->fixStepArgument($this->translateString($field));
    $value = $this->fixStepArgument($value);
    $this->getSession()->getPage()->fillField($field, $value);
  }

  /**
   * Fill field with formatted date.
   *
   * Fills in form field with specified id|name|label|value with a formatted
   * date.
   *
   * Example: And I fill in "start_date[date]" with relative date
   * "now - 1 minute" formatted "%d%m%Y".
   *
   * @When I fill in :field with date :dateValue formatted :dateFormat
   * @When I fill in :field with date :dateValue formatted :dateFormat in timezone :timezone
   */
  public function fillFieldWithFormattedDate(string $field, string $dateValue, string $dateFormat, string $timezone = NULL) {
    $timezone = $timezone ?? date_default_timezone_get();
    $dateValue = $this->fixStepArgument($dateValue);
    $dateFormat = $this->fixStepArgument($dateFormat);
    $dateTime = new \DateTime($dateValue);
    $dateTime->setTimezone(new \DateTimeZone($timezone));
    $formattedDate = $dateTime->format($dateFormat);
    $field = $this->fixStepArgument($this->translateString($field));
    $this->getSession()->getPage()->fillField($field, $formattedDate);
  }

  /**
   * Returns fixed step argument (with \\" replaced back to ")
   *
   * @param string $argument
   *   Argument.
   *
   * @return string
   *   Modified argument.
   */
  protected function fixStepArgument($argument) {
    return str_replace('\\"', '"', $argument);
  }

}
