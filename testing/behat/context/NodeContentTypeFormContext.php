<?php

namespace Drupal\degov\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;
use Drupal\degov\Behat\Context\Traits\TranslationTrait;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\node\Entity\Node;

/**
 * Class NodeContentTypeFormContext.
 */
class NodeContentTypeFormContext extends RawDrupalContext {

  use TranslationTrait;

  use DebugOutputTrait;

  /**
   * See element with div class.
   *
   * @Given /^I see element "([^"]*)" with divclass "([^"]*)"$/
   */
  public function iSeeElementWithDivclass(string $elmnt, $className) {
    // Get the mink session.
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $elmnt . '.' . $className);

    if (!$element) {
      try {
        throw new \Exception("Element " . $elmnt . "with classname " . $className . " not found");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Choose from tab menu.
   *
   * @Given /^I choose "([^"]*)" from tab menu$/
   */
  public function iChooseFromTabMenu(string $text): void {
    $page = $this->getSession()->getPage();
    $cssClass = 'div.vertical-tabs.clearfix ul.vertical-tabs__menu li a';
    $elements = $page->findAll('css', $cssClass);

    $counter = 0;
    $executedScript = FALSE;

    foreach ($elements as $element) {
      $tmp = $element->find('css', 'strong');
      $selectedText = $tmp->getText();

      if ($selectedText === $text) {
        $this->getSession()
          ->executeScript('jQuery("' . $cssClass . '")[' . $counter . '].click()');
        $executedScript = TRUE;
      }
      $counter++;
    }

    if ($executedScript === FALSE) {
      throw new \Exception('Could not find text "' . $text . '" in tab menu.');
    }

  }

  /**
   * Choose translated from tab menu.
   *
   * @Given /^I choose "([^"]*)" via translation from tab menu$/
   */
  public function iChooseTranslatedFromTabMenu(string $text): void {
    $text = $this->translateString($text);
    $this->iChooseFromTabMenu($text);
  }

  /**
   * Click on toggle button.
   *
   * @Given /^I click on togglebutton$/
   */
  public function iClickOnTogglebutton() {
    $this->getSession()
      ->executeScript('jQuery(".dropbutton-widget ul.dropbutton li.dropbutton-toggle button").click()');
  }

  /**
   * Select from right pane.
   *
   * @Given /^I select "([^"]*)" via translation in uppercase from rightpane$/
   */
  public function iSelectFromRightpane(string $text): void {
    $divLayout = 'div.layout-region.layout-region-node-secondary div.entity-meta.js-form-wrapper.form-wrapper details';
    // Get the mink session.
    $page = $this->getSession()->getPage();
    $elements = $page->findAll("css", $divLayout);

    foreach ($elements as $element) {
      if ($element->getText() === trim(mb_strtoupper($this->translateString($text)))) {
        $pane = $element->find('css', 'summary');
        $pane->click();
        $checkbox = $element->find('css', '.details-wrapper label.option');
        $checkbox->click();
      }
    }
  }

  /**
   * Choose in select moderation box.
   *
   * @Given /^I choose "([^"]*)" via translation in selectModerationBox$/
   */
  public function iChooseInSelectModerationBox(string $text): void {
    $page = $this->getSession()->getPage();
    $optionElements = $page->findAll('css', 'div.container-inline select option');

    foreach ($optionElements as $optionElement) {
      if ($optionElement->getText() === trim($this->translateString($text))) {
        $optionElement->selectOption($optionElement);
      }
    }
  }

  /**
   * Proof content with title has moderation state.
   *
   * @Given /^I proof content with title "([^"]*)" has moderation state "([^"]*)"$/
   */
  public function iProofContentWithTitleHasModerationState(string $title, string $state): void {
    $nidArray = \Drupal::entityQuery('node')
      ->condition('title', $title)->accessCheck(FALSE)->execute();

    if (\count($nidArray) > 1) {
      throw new \Exception('Expected array with one nid, got array with multiple items.');
    }

    $nid = reset($nidArray);

    $node = Node::load($nid);

    $allRevisionIds = \Drupal::entityTypeManager()->getStorage('node')->revisionIds($node);
    $numRevisionIds = \count($allRevisionIds);
    $latestRevisionId = end($allRevisionIds);

    $nodeLastRevision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($latestRevisionId);
    $latestRevisionState = $nodeLastRevision->get('moderation_state')->getString();

    if ($state === $latestRevisionState) {
      return;
    }
    throw new \Exception("No node with title '$title' and moderation state '$state'. Instead got state '$latestRevisionState'. Got '$numRevisionIds' revision ids.");
  }

  /**
   * Click on config button.
   *
   * @Given /^I click on Configbutton "([^"]*)"$/
   */
  public function iClickOnConfigbutton($arg1) {
    $this->getSession()
      ->executeScript('jQuery("table#blocks tr td.block:contains(' . $arg1 . ')").parent().find("td").find("ul li a")[0].click()');
  }

  /**
   * Proof fields for display.
   *
   * @Given /^I proof content type "([^"]*)" has set the following fields for display:$/
   */
  public function proofFieldsForDisplay(string $contentType, TableNode $table) {
    $rowsHash = $table->getRowsHash();
    $expectedFieldNames = array_keys($rowsHash);

    $displayoptions = EntityViewDisplay::load('node.' . $contentType . '.default');

    $hiddenFields = $displayoptions->get('hidden');

    foreach ($expectedFieldNames as $fieldName) {
      if (\array_key_exists($fieldName, $hiddenFields) && ($hiddenFields[$fieldName] === 'true' || $fieldName === TRUE)) {
        throw new \Exception("Field named '$fieldName' is set to hidden, but is expected to be visible.");
      }

      if (!\array_key_exists($fieldName, $displayoptions->get('content'))) {
        throw new \Exception("Field named '$fieldName' is not configured in the content.");
      }
    }
  }

}
