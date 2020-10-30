<?php

namespace Drupal\degov\Behat\Context;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ResponseTextException;
use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;
use Drupal\degov\Behat\Context\Traits\TranslationTrait;
use Drupal\Driver\DrupalDriver;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use WebDriver\Exception\StaleElementReference;

/**
 * Class DrupalContext.
 */
class DrupalContext extends RawDrupalContext {

  use TranslationTrait;

  use DebugOutputTrait;

  private const MAX_DURATION_SECONDS = 1200;
  private const MAX_SHORT_DURATION_SECONDS = 10;

  /**
   * Trash.
   *
   * @var array*/
  protected $trash = [];

  /**
   * Dummy image file entity ID.
   *
   * @var null|int
   */
  private $dummyImageFileEntityId = NULL;

  /**
   * Dummy document file entity ID.
   *
   * @var null|int
   */
  private $dummyDocumentFileEntityId = NULL;

  /**
   * DrupalContext constructor.
   */
  public function __construct() {
    $driver = new DrupalDriver(DRUPAL_ROOT, '');
    $driver->setCoreFromVersion();

    // Bootstrap Drupal.
    $driver->bootstrap();
  }

  /**
   * Create vocabulary.
   *
   * @Given /^I create vocabulary with name "([^"]*)" and vid "([^"]*)"$/
   */
  public function createVocabulary($name, $vid) {
    $vocabulary = \Drupal::entityQuery('taxonomy_vocabulary')
      ->condition('vid', $vid)
      ->execute();

    if (empty($vocabulary)) {
      $vocabulary = Vocabulary::create([
        'name' => $name,
        'vid'  => $vid,
      ]);
      $vocabulary->save();
    }
  }

  /**
   * Create nodes of type.
   *
   * @Given /^I create (\d+) nodes of type "([^"]*)"$/
   */
  public function iCreateNodesOfType($number, $type) {
    for ($i = 0; $i <= $number; $i++) {
      $node = new \stdClass();
      $node->type = $type;
      $node->title = $this->createRandomString();
      $node->body = $this->createRandomString();
      $this->nodeCreate($node);
    }
  }

  /**
   * Create random string.
   */
  private function createRandomString($length = 10) {
    return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", $length)), 0, $length);
  }

  /**
   * Node access records are rebuild.
   *
   * @Given Node access records are rebuild.
   */
  public function nodeAccessRecordsAreRebuild() {
    node_access_rebuild();
  }

  /**
   * Open node edit form by title.
   *
   * @param string $title
   *   Title.
   *
   * @Then /^I open node edit form by node title "([^"]*)"$/
   */
  public function openNodeEditFormByTitle($title) {
    $query = \Drupal::service('database')->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $title);

    $this->visitPath('/node/' . $query->execute()->fetchField() . '/edit');
  }

  /**
   * Open node edit form by title and vertical tab ID.
   *
   * @param string $title
   *   Title.
   * @param string $verticalTabId
   *   Vertical tab ID.
   *
   * @Then /^I open node edit form by node title "([^"]*)" and vertical tab id "([^"]*)"$/
   */
  public function openNodeEditFormByTitleAndVerticalTabId(string $title, string $verticalTabId) {
    $query = \Drupal::service('database')->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $title);

    $this->visitPath('/node/' . $query->execute()->fetchField() . '/edit#' . $verticalTabId);
  }

  /**
   * Open media edit form by name.
   *
   * @param string $name
   *   Name.
   *
   * @Then /^I open media edit form by media name "([^"]*)"$/
   */
  public function openMediaEditFormByName(string $name) {
    $query = \Drupal::service('database')->select('media_field_data', 'mfd')
      ->fields('mfd', ['mid'])
      ->condition('mfd.name', $name);

    $this->visitPath('/media/' . $query->execute()->fetchField() . '/edit');
  }

  /**
   * Open node view by title.
   *
   * @param string $title
   *   Title.
   *
   * @Then /^I open node view by node title "([^"]*)"$/
   */
  public function openNodeViewByTitle(string $title): void {
    $query = \Drupal::service('database')->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $title);

    $this->visitPath('/node/' . $query->execute()->fetchField());
  }

  /**
   * Open media edit form by title.
   *
   * @Then /^I open address medias edit form from latest media with title "([^"]*)"$/
   */
  public function openMediaEditFormByTitle(string $title): void {
    /**
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     */
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $mediaEntityStorage = $entityTypeManager->getStorage('media');

    $mediaEntities = $mediaEntityStorage->loadByProperties([
      'field_title' => $title,
      'bundle'      => 'address',
    ]);

    $mediaEntity = \end($mediaEntities);

    if (!$mediaEntity instanceof Media) {
      throw new \Exception('Could not retrieve media entity by provided title.');
    }

    $this->visitPath('/media/' . $mediaEntity->id() . '/edit');
  }

  /**
   * Open media delete url by title.
   *
   * @Then /^I open medias delete url by title "([^"]*)"$/
   */
  public function openMediaDeleteUrlByTitle(string $title): void {
    /**
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     */
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $mediaEntityStorage = $entityTypeManager->getStorage('media');

    $mediaEntities = $mediaEntityStorage->loadByProperties([
      'field_title' => $title,
    ]);

    $mediaEntity = \end($mediaEntities);

    if (!$mediaEntity instanceof Media) {
      throw new \Exception('Could not retrieve media entity by provided title.');
    }

    $this->visitPath('/media/' . $mediaEntity->id() . '/delete');
  }

  /**
   * Click by css class.
   *
   * @param string $class
   *   Class.
   *
   * @Then /^I click by CSS class "([^"]*)"$/
   */
  public function clickByCssClass(string $class) {
    $page   = $this->getSession()->getPage();
    $button = $page->find('xpath', '//*[contains(@class, "' . $class . '")]');
    $button->click();
  }

  /**
   * Click by css ID.
   *
   * @Then /^I click by CSS id "([^"]*)"$/
   */
  public function clickByCssId(string $id) {
    $page   = $this->getSession()->getPage();
    $button = $page->find('xpath', '//*[contains(@id, "' . $id . '")]');
    $button->click();
  }

  /**
   * Click by xpath.
   *
   * @param string $xpath
   *   Xpath.
   *
   * @Then /^I click by XPath "([^"]*)"$/
   */
  public function iClickByXpath(string $xpath) {
    // Get the mink session.
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
    // Runs the actual query and returns the element.
    );

    // Errors must not pass silently.
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
    }

    // ok, let's click on it.
    $element->click();
  }

  /**
   * Proof checkbox with ID has value.
   *
   * @Then /^I proof Checkbox with id "([^"]*)" has value "([^"]*)"$/
   */
  public function iProofCheckboxWithIdHasValue($id, $checkfor): void {
    $page = $this->getSession()->getPage();
    $isChecked = $page->find('css', 'input[type="checkbox"]:checked#' . $id);
    $status = ($isChecked) ? "checked" : "unchecked";
    if (
      ($checkfor == "checked" && $isChecked == TRUE) ||
      ($checkfor == "unchecked" && $isChecked == FALSE)
    ) {
      return;
    }
    else {
      try {
        throw new \Exception('Checkbox was ' . $status . ' when expecting ' . $checkfor);
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Should see the option In.
   *
   * @Given /^I should see the option "([^"]*)" in "([^"]*)"$/
   */
  public function iShouldSeeTheOptionIn(string $value, string $id) {
    $this->iShouldSeeTheOptionInWithStatus($value, $id);
  }

  /**
   * Should s ee the option in with status.
   *
   * @Given /^I should see the option "([^"]*)" in "([^"]*)" with status "([^"]*)"$/
   */
  public function iShouldSeeTheOptionInWithStatus(string $value, string $id, string $status = NULL) {
    $page = $this->getSession()->getPage();
    /** @var $selectElement \Behat\Mink\Element\NodeElement */
    $selectElement = $page->find('xpath', '//select[@id = "' . $id . '"]');
    switch ($status) {
      case 'enabled':
        $element = $selectElement->find('css', 'option[value=' . $value . ']:not([disabled])');
        break;

      case 'disabled':
        $element = $selectElement->find('css', 'option[value=' . $value . '][disabled]');
        break;

      default:
        $element = $selectElement->find('css', 'option[value=' . $value . ']');
    }
    if (!$element) {
      try {
        throw new \Exception("There is no option with the value '$value'" . ($status !== NULL ? " and status '" . $status . "'" : '') . " in the select '$id'");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Should see an element with the translated attribute.
   *
   * @Then I should see an :arg1 element with the translated :arg2 attribute :arg3
   */
  public function iShouldSeeAnElementWithTheTranslatedAttribute(string $selector, string $attribute_name, string $attribute_value) {
    $this->assertSession()->elementExists(
      'css',
      sprintf(
        "%s[%s=%s]",
        $selector,
        $attribute_name,
        $this->translateString($attribute_value)
      )
    );
  }

  /**
   * Create normal page with slideshow.
   *
   * @Given /^I have an normal_page with a slideshow paragraph reference$/
   */
  public function createNormalPageWithSlideshow(): void {
    $media = Media::create([
      'bundle'              => 'image',
      'field_title'         => 'Some image',
      'field_copyright'     => 'Some copyright',
      'field_image_caption' => 'Some image caption',
      'image'               => $this->createDummyImageFileEntity()->id(),
    ]);
    $media->save();

    $paragraphSlide = Paragraph::create([
      'type'              => 'slide',
      'field_slide_media' => $media,
    ]);
    $paragraphSlide->save();

    $paragraphSlideshow = Paragraph::create([
      'type'                   => 'slideshow',
      'field_slideshow_slides' => $paragraphSlide,
      'field_slideshow_type'   => 'Typ 1',
    ]);
    $paragraphSlideshow->save();

    $node = Node::create([
      'title'                   => 'An normal page with a slideshow',
      'type'                    => 'normal_page',
      'moderation_state'        => 'published',
      'field_header_paragraphs' => [$paragraphSlideshow],
    ]);
    $node->save();
  }

  /**
   * Create normal page with banner.
   *
   * @Given /^I have an normal_page with a banner paragraph$/
   */
  public function createNormalPageWithBanner(): void {
    $copyrightTerm = Term::create([
      'name' => 'Some copyright',
      'vid'  => 'copyright',
    ]);

    $media = Media::create([
      'bundle'              => 'image',
      'field_title'         => 'Some image',
      'field_copyright'     => $copyrightTerm,
      'field_image_caption' => 'Some image caption',
      'image'               => $this->createDummyImageFileEntity()->id(),
    ]);
    $media->save();

    $paragraphSlideshow = Paragraph::create([
      'type'                   => 'image_header',
      'field_override_caption' => '',
      'field_header_media'     => $media,
    ]);
    $paragraphSlideshow->save();

    $node = Node::create([
      'title'                   => 'An normal page with a banner',
      'type'                    => 'normal_page',
      'moderation_state'        => 'published',
      'field_header_paragraphs' => [$paragraphSlideshow],
    ]);
    $node->save();
  }

  /**
   * Create restricted document.
   *
   * @Given /^I have a restricted document media entity$/
   */
  public function createRestrictedDocument(): void {
    /**
     * @var \Drupal\permissions_by_term\Service\TermHandler $termHandler
     */
    $termHandler = \Drupal::service('permissions_by_term.term_handler');
    if (empty($termHandler->getTermIdByName('Admin role only - restricted media document'))) {
      $term = Term::create([
        'name' => 'Admin role only - restricted media document',
        'vid' => 'section',
      ]);
      $term->save();

      $termId = $term->id();

      /**
       * @var \Drupal\permissions_by_term\Service\AccessStorage $accessStorage
       */
      $accessStorage = \Drupal::service('permissions_by_term.access_storage');
      $accessStorage->addTermPermissionsByRoleIds(['administrator'], $termId);

      Media::create([
        'title'          => 'Restricted Word document',
        'field_title'    => 'Restricted Word document',
        'bundle'         => 'document',
        'field_section'  => [
          [
            'target_id' => $termId,
          ],
        ],
        'field_document' => [
          [
            'target_id' => $this->createPrivateDocumentFileEntity()->id(),
          ],
        ],
      ])->save();
    }

  }

  /**
   * Create a media of type.
   *
   * @Given /^I created a media of type "([^"]*)" named "([^"]*)"$/
   * @When /^I create a media of type "([^"]*)" named "([^"]*)"$/
   */
  public function iCreateMediaOfType($type, $name = NULL) {
    if (!$name) {
      $name = $this->createRandomString();
    }
    $mediaData = [
      'bundle'               => $type,
      'field_title'          => $name,
      'field_include_search' => TRUE,
    ];
    switch ($type) {
      case 'video':
        $mediaData += [
          'field_media_video_embed_field' => 'https://vimeo.com/191669818',
        ];
        break;

      case 'tweet':
        $mediaData += [
          'embed_code' => 'https://twitter.com/publicplan_GmbH/status/1024935629065469958',
        ];
        break;

      case 'instagram':
        $mediaData += [
          'embed_code' => 'https://www.instagram.com/p/JUvux9iFRY',
        ];
        break;

      case 'image':
        $mediaData += [
          'field_title'         => 'Some image',
          'name'                => 'Some image',
          'field_copyright'     => 'Some copyright',
          'field_image_caption' => 'Some image caption',
          'image'               => $this->createDummyImageFileEntity()->id(),
        ];
        break;

      // @TODO: Add all media types.
      default:
        throw new \InvalidArgumentException(sprintf('The media type "%s" does not exist.', $type));
    }
    $media = Media::create($mediaData);
    $media->save();
    $this->trash[$media->getEntityTypeId()][] = $media->id();

    return $media;
  }

  /**
   * Creates a page with a specific media.
   *
   * Example: Given I created a content page named "videoPage" with
   * a media "video".
   *
   * @Given /^(?:|I )created a content page named "([^"]*)" with a media "(address|audio|citation|contact|document|gallery|image|instagram|person|some_embed|tweet|video|video_upload)"$/
   */
  public function iCreatedPageWithMedia($pageName, $mediaType) {
    self::iCreatedTypePageWithMedia('normal_page', $pageName, $mediaType);
  }

  /**
   * Creates a page with given entity type and with a specific media.
   *
   * Example: Given I created a content page of type "event" named "videoPage"
   * with a media "video".
   *
   * @Given /^(?:|I )created a content page of type "([^"]*)" named "([^"]*)" with a media "(address|audio|citation|contact|document|gallery|image|instagram|person|some_embed|tweet|video|video_upload)"$/
   */
  public function iCreatedTypePageWithMedia($entityType, $pageName, $mediaType) {
    $media = $this->iCreateMediaOfType($mediaType);

    $mediaParagraph = Paragraph::create([
      'type'                        => 'media_reference',
      'field_media_reference_media' => $media,
    ]);
    $mediaParagraph->save();
    $this->trash[$mediaParagraph->getEntityTypeId()][] = $mediaParagraph->id();

    $node = Node::create([
      'type'                     => $entityType,
      'title'                    => $pageName,
      'moderation_state'         => 'published',
      'field_content_paragraphs' => [$mediaParagraph],
    ]);
    $node->save();
    $this->trash[$node->getEntityTypeId()][] = $node->id();
  }

  /**
   * Deletes entities created during the scenario.
   *
   * @afterScenario
   */
  public function tearDown() {
    foreach ($this->trash as $entity_type => $ids) {
      /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
      $entities = \Drupal::entityTypeManager()
        ->getStorage($entity_type)
        ->loadMultiple($ids);

      foreach ($entities as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Header has css class for fluid bootstrap layout.
   *
   * @Then header has CSS class for fluid bootstrap layout
   */
  public function headerHasCssClassForFluidBootstrapLayout() : ?bool {
    $header = $this->getSession()->getPage()->findAll('css', 'header.container-fluid');
    if (\count($header) > 0) {
      return TRUE;
    }
    else {
      throw new ResponseTextException('Header does not have CSS class for fluid bootstrap layout.', $this->getSession());
    }
  }

  /**
   * Assert local tasks tabs number.
   *
   * @Then /^I assert "(\d+)" local task tabs$/
   */
  public function assertLocalTasksTabsNumber($number) {
    if (\count($this->getSession()->getPage()->findAll('css', ".block-local-tasks-block > nav > nav > ul > li:nth-child($number)")) > 0) {
      return TRUE;
    }

    throw new ResponseTextException(
      sprintf('Could not find "%s" local task items', $number),
      $this->getSession()
    );
  }

  /**
   * Assert selector contains text.
   *
   * @Then /^I should see text matching "([^"]*)" in "([^"]*)" selector "([^"]*)"$/
   *
   * Example:
   *  I should see text matching "Startseite Node" in "css"
   *  selector "ol.breadcrumb"
   */
  public function assertSelectorContainsText($text, $selectorType, $selector) {
    $resultset = $this->getSession()->getPage()->findAll($selectorType, $selector);
    if (!empty($resultset)) {
      foreach ($resultset as $match) {
        if (is_numeric(strpos($match->getText(), $text))) {
          return TRUE;
        }
      }
    }

    try {
      throw new ResponseTextException(
        sprintf('Could not find text "%s" by selector type "%s" and selector "%s"', $text, $selectorType, $selector),
        $this->getSession()
      );
    }
    catch (ResponseTextException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Assert selector not contains translated text.
   *
   * @Then /^I should not see text matching "([^"]*)" via translated text in "([^"]*)" selector "([^"]*)"$/
   *
   * Example:
   *  I should not see text matching "Homepage node" via translated
   *  in "css" selector "ol.breadcrumb"
   */
  public function assertSelectorNotContainsTranslatedText($text, $selectorType, $selector) {
    $resultset = $this->getSession()->getPage()->findAll($selectorType, $selector);
    $translatedText = $this->translateString($text);
    $isFound = FALSE;
    if (!empty($resultset)) {
      foreach ($resultset as $resultRow) {
        if (is_numeric(stripos($resultRow->getText(), $translatedText))) {
          $isFound = TRUE;
          break;
        }
      }
    }
    if (!$isFound) {
      return TRUE;
    }

    try {
      throw new ResponseTextException(
        sprintf('Found the text "%s" by selector type "%s" and selector "%s"', $translatedText, $selectorType, $selector),
        $this->getSession()
      );
    }
    catch (ResponseTextException $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Run the cron.
   *
   * @Given /^I run the cron$/
   *
   * @throws \Exception
   */
  public function iRunTheCron() {
    if (TRUE !== \Drupal::service('cron')->run()) {
      try {
        throw new \Exception('Cron did not run successfully.');
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Assert page matches text.
   *
   * @Then /^I should see text matching "([^"]*)" via translated text$/
   */
  public function assertPageMatchesText(string $text) {
    if (ctype_upper($text)) {
      $translatedText = mb_strtoupper($this->translateString($text));
    }
    else {
      $translatedText = $this->translateString($text);
    }

    $this->assertSession()->pageTextMatches('"' . $translatedText . '"');
  }

  /**
   * Assert page not matches text.
   *
   * @Then /^I should not see text matching "([^"]*)" via translated text$/
   */
  public function assertPageNotMatchesText(string $text) {
    if (ctype_upper($text)) {
      $translatedText = mb_strtoupper($this->translateString($text));
    }
    else {
      $translatedText = $this->translateString($text);
    }

    $content = $this->getSession()->getPage()->getText();
    if (substr_count($content, $translatedText) === 0) {
      return TRUE;
    }
    else {
      try {
        throw new \Exception("Text '$translatedText' found on page.");
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
  }

  /**
   * Assert dom node is invisible.
   *
   * @Then /^I proof that DOM node with css selector "([^"]*)" is invisible$/
   */
  public function assertDomNodeIsInvisible(string $cssSelector): void {
    $element = $this->getSession()->getPage();
    $nodes = $element->findAll('css', $cssSelector);
    foreach ($nodes as $node) {
      if (!$node->isVisible()) {
        return;
      }
      else {
        throw new \Exception("DOM node with $cssSelector is not invisible.");
      }
    }

    throw new ElementNotFoundException($this->getSession(), 'DOM node', $cssSelector);
  }

  /**
   * Assert page matches text uppercase.
   *
   * @Then /^I should see text matching "([^"]*)" via translated text in uppercase$/
   */
  public function assertPageMatchesTextUppercase(string $text) {
    $this->assertSession()->pageTextMatches('"' . mb_strtoupper($this->translateString($text)) . '"');
  }

  /**
   * Assert page not maches text uppercase.
   *
   * @Then /^I should not see text matching "([^"]*)" via translated text in uppercase$/
   */
  public function assertPageNotMatchesTextUppercase(string $text) {
    $this->assertSession()->pageTextNotMatches('"' . mb_strtoupper($this->translateString($text)) . '"');
  }

  /**
   * Should see translated text after a while.
   *
   * @Then /^I should see text matching "([^"]*)" via translation after a while$/
   */
  public function iShouldSeeTranslatedTextAfterWhile(string $text): bool {
    try {
      $startTime = time();
      do {
        $content = $this->getSession()->getPage()->getText();
        $translatedText = $this->translateString($text);
        if (substr_count($content, $translatedText) > 0) {
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new ResponseTextException(
          sprintf('Could not find text %s after %s seconds', $translatedText, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (ResponseTextException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Assert click via translate.
   *
   * @When I click :link via translation
   */
  public function assertClickViaTranslate(string $link): void {
    $this->getSession()->getPage()->clickLink($this->translateString($link));
  }

  /**
   * Should see the fields list with exactly entries.
   *
   * @Then I should see the fields list with exactly :arg1 entries
   */
  public function iShouldSeeTheFieldsListWithExactlyEntries($numberOfEntries) {
    $this->iShouldSeeTheElementWithTheSelectorWithExactlyInstances(2, "table#field-overview tbody > tr");
  }

  /**
   * Should see the element with the selector X with exactly N instances.
   *
   * @Then I should see exactly :arg1 instances of the element with the selector :arg2
   */
  public function iShouldSeeTheElementWithTheSelectorWithExactlyInstances($numberOfInstances, $elementSelector) {
    $this->assertSession()->elementExists('css', $elementSelector);
    $this->assertSession()->elementsCount('css', $elementSelector, $numberOfInstances);
  }

  /**
   * Have dismissed the cookie banner if necessary.
   *
   * @Given I have dismissed the cookie banner if necessary
   */
  public function iHaveDismissedTheCookieBannerIfNecessary() {

    $this->getSession()->visit($this->locatePath('/'));
    if ($this->getSession()->getPage()->has('css', '.eu-cookie-compliance-buttons .agree-button')) {
      $this->getSession()->getPage()->find('css', '.eu-cookie-compliance-buttons .agree-button')->click();
    }
  }

  /**
   * Should see the details container titled with entries after a while.
   *
   * @Given I should see the details container titled :arg1 with entries after a while
   */
  public function iShouldSeeTheDetailsContainerTitledWithEntriesAfterWhile($title) {
    $title = mb_strtoupper($title);

    try {
      $startTime = time();
      do {
        $details_array = $this->getSession()->getPage()->findAll('css', 'details');

        foreach ($details_array as $details_element) {
          if ($details_element->find('css', 'summary')->getText() != $title) {
            continue;
          }
          if ($details_element->has('css', '.item-container')) {
            return TRUE;
          }
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new ResponseTextException(
          sprintf('Could not find element titled %s with entries within %s seconds.', $title, self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (ResponseTextException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return TRUE;
    }
  }

  /**
   * Should see a form element with the label and required input field.
   *
   * @Then I should see :number_of_elements form element with the label :label and a required input field
   */
  public function iShouldSeeFormElementWithTheLabelAndRequiredInputField(int $number_of_elements, string $label_text) {
    $matching_elements_count = $this->countFormElementsWithLabelMatchingSelector($label_text, 'css', '.required');
    if ($number_of_elements === $matching_elements_count) {
      return TRUE;
    }

    try {
      throw new \Exception(sprintf('Expected %s elements, found %s.', $number_of_elements, $matching_elements_count));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Should see a form element with the label and a field.
   *
   * @Then I should see :number_of_elements form element with the label :arg2 and a :arg3 field
   */
  public function iShouldSeeFormElementWithTheLabelAndField(int $number_of_elements, string $label_text, string $input_type) {
    $matching_elements_count = $this->countFormElementsWithLabelMatchingSelector($label_text, 'xpath', sprintf('//input[@type="%s"]', $input_type));
    if ($number_of_elements === $matching_elements_count) {
      return TRUE;
    }

    try {
      throw new \Exception(sprintf('Expected %s elements, found %s.', $number_of_elements, $matching_elements_count));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }
  }

  /**
   * Should see a form element with the label and the value.
   *
   * @Then I should see :number_of_elements form element with the label :label and the value :field_value
   */
  public function iShouldSeeFormElementWithTheLabelAndTheValue(int $number_of_elements, string $label_text, string $field_value) {
    $selector_value = '//*[@value="' . $field_value . '"]';
    if (empty($field_value)) {
      $selector_value = '//*[@value and string-length(@value) = 0]';
    }
    $matching_elements_count = $this->countFormElementsWithLabelMatchingSelector($label_text, 'xpath', $selector_value);
    if ($number_of_elements === $matching_elements_count) {
      return TRUE;
    }

    try {
      throw new \Exception(sprintf('Expected %s elements, found %s.', $number_of_elements, $matching_elements_count));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Should see elements with name matching pattern and a not empty value.
   *
   * @Then I should see :number_of_elements elements with name matching :name_pattern and a not empty value
   */
  public function iShouldSeeElementsWithNameMatchingPatternAndNotEmptyValue(int $number_of_elements, string $name_pattern) {
    $selector_value = '//*[contains(@name, "' . $name_pattern . '") and @value and string-length(@value) > 0]';
    $matches = $this->getSession()->getPage()->findAll('xpath', $selector_value);
    $matching_elements_count = count($matches);
    if ($number_of_elements === $matching_elements_count) {
      return TRUE;
    }
    throw new \Exception(sprintf('Expected %s elements, found %s.', $number_of_elements, $matching_elements_count));
  }

  /**
   * Count form elements with label matching selector.
   */
  private function countFormElementsWithLabelMatchingSelector(string $label_text, string $selector_type, string $selector_value): int {
    // Get all form items with labels matching the supplied text.
    $form_items_with_matching_labels = $this->getElementWithClassContainingLabelWithText('form-item', $label_text);

    $matching_elements_count = 0;
    foreach ($form_items_with_matching_labels as $form_item) {
      if (count($form_item->findAll($selector_type, $selector_value)) > 0) {
        $matching_elements_count++;
      }
    }

    return $matching_elements_count;
  }

  /**
   * Get element with class containing label with text.
   */
  private function getElementWithClassContainingLabelWithText($class_name, $label_text) {
    return $this->getSession()->getPage()->findAll(
      'xpath',
      sprintf('//label[contains(text(), "%s")]/ancestor::*[contains(@class, "%s")]', $label_text, $class_name)
    );
  }

  /**
   * Create private document file entity.
   */
  private function createPrivateDocumentFileEntity(): File {
    $documentFileEntity = NULL;

    if (is_numeric($this->dummyDocumentFileEntityId)) {
      /**
       * @var \Drupal\file\Entity\File $documentFileEntity
       */
      $documentFileEntity = File::load($this->dummyDocumentFileEntityId);
    }

    if (!($documentFileEntity instanceof File)) {
      $documentFileEntity = $this->createFileEntity('word-document.docx', 'private');
      $this->dummyDocumentFileEntityId = $documentFileEntity->id();
    }

    return $documentFileEntity;
  }

  /**
   * Create dummy image file entity.
   */
  private function createDummyImageFileEntity(): File {
    $imageFileEntity = NULL;

    if (is_numeric($this->dummyImageFileEntityId)) {
      /**
       * @var \Drupal\file\Entity\File $imageFileEntity
       */
      $imageFileEntity = File::load($this->dummyImageFileEntityId);
    }

    if (!($imageFileEntity instanceof File)) {
      $imageFileEntity = $this->createFileEntity('vladimir-riabinin-1058013-unsplash.jpg');
      $this->dummyImageFileEntityId = $imageFileEntity->id();
    }

    return $imageFileEntity;
  }

  /**
   * Create file entity.
   */
  private function createFileEntity(string $filename, string $fileSchemeMode = 'public'): File {
    $fileEntity = NULL;

    if (!($fileEntity instanceof File)) {
      /**
       * @var \Drupal\degov_theming\Factory\FilesystemFactory $symfonyFilesystem
       */
      $filesystemFactory = \Drupal::service('degov_theming.filesystem_factory');
      /**
       * @var \Symfony\Component\Filesystem\Filesystem $filesystem
       */
      $symfonyFilesystem = $filesystemFactory->create();

      /**
       * @var \Drupal\Core\File\FileSystem $drupalFilesystem
       */
      $drupalFilesystem = \Drupal::service('file_system');

      if ($fileSchemeMode === 'public') {
        $drupalFilePath = 'public://';

        $symfonyFilesystem->copy(
          drupal_get_path('module', 'degov_demo_content') . '/fixtures/' . $filename,
          $drupalFilesystem->realpath($drupalFilePath . '/' . $filename)
        );
      }
      else {
        $drupalFilePath = 'private://media/document/file';

        $documentFilesUri = $drupalFilesystem->realpath('private://') . '/media/document/file';

        if (!$symfonyFilesystem->exists($documentFilesUri)) {
          $symfonyFilesystem->mkdir($documentFilesUri);
        }

        $symfonyFilesystem->copy(
          drupal_get_path('module', 'degov_demo_content') . '/fixtures/' . $filename,
          $documentFilesUri . '/' . $filename
              );
      }

      $fileEntity = File::create([
        'uid'      => 1,
        'filename' => $filename,
        'uri'      => $drupalFilePath . '/' . $filename,
        'status'   => 1,
      ]);
      $fileEntity->save();

      $this->dummyImageFileEntityId = $fileEntity->id();
    }

    return $fileEntity;
  }

  /**
   * Create normal page entity with content reference in view mode.
   *
   * @Then /^I have created an node normal page entity with a content reference in "([^"]*)" view mode$/
   */
  public function createNormalPageEntityWithContentReferenceInViewMode(string $viewMode): void {
    $media = Media::create([
      'bundle'              => 'image',
      'field_title'         => 'Some image',
      'field_copyright'     => 'Some copyright',
      'field_image_caption' => 'Some image caption',
      'image'               => $this->createDummyImageFileEntity()->id(),
    ]);
    $media->save();

    $nodeForContentReference = Node::create([
      'title'                   => 'Some node for reference',
      'type'                    => 'normal_page',
      'moderation_state'        => 'published',
      'field_teaser_text'       => 'My nice teaser text.',
      'field_teaser_image'      => [
        [
          'target_id' => $media->id(),
        ],
      ],
    ]);
    $nodeForContentReference->save();

    $contentReferenceParagraph = Paragraph::create([
      'type'                          => 'node_reference',
      'field_node_reference_nodes'    => [
        [
          'target_id' => $nodeForContentReference->id(),
        ],
      ],
      'field_node_reference_viewmode' => $viewMode,
    ]);
    $contentReferenceParagraph->save();

    $node = Node::create([
      'title'                    => 'An normal page with a content reference',
      'type'                     => 'normal_page',
      'moderation_state'         => 'published',
      'field_content_paragraphs' => [
        $contentReferenceParagraph,
      ],
    ]);
    $node->save();
  }

  /**
   * Have created an unused file entity.
   *
   * @Then /^I have created an unused file entity$/
   */
  public function iHaveCreatedAnUnusedFileEntity() {
    $this->createDummyImageFileEntity();
  }

  /**
   * Visit the delete form for the unused file entity.
   *
   * @Then /^I visit the delete form for the unused file entity$/
   */
  public function iVisitTheDeleteFormForTheUnusedFileEntity() {
    if (preg_match("/^\d+$/", $this->dummyImageFileEntityId)) {
      $this->getSession()->visit($this->locatePath('/file/' . $this->dummyImageFileEntityId . '/delete'));
    }
  }

  /**
   * Visit normal page entity with content reference.
   *
   * @Then /^I visit an normal page entity with content reference$/
   */
  public function visitNormalPageEntityWithContentReference(): void {
    $this->openNodeViewByTitle('An normal page with a content reference');
  }

  /**
   * Set the privacy policy page for all languages.
   *
   * @Given I set the privacy policy page for all languages
   */
  public function setThePrivacyPolicyPageForAllLanguages() {
    $degov_simplenews_settings = \Drupal::service('config.factory')
      ->getEditable('degov_simplenews.settings');
    $all_languages = \Drupal::service('language_manager')->getLanguages();
    $page_with_all_teasers_nid = \Drupal::entityQuery('node')
      ->execute();
    if (!empty($page_with_all_teasers_nid)) {
      $page_with_all_teasers_nid = reset($page_with_all_teasers_nid);

      $privacy_policies = [];
      foreach ($all_languages as $language) {
        $privacy_policies[$language->getId()] = $page_with_all_teasers_nid;
      }
      $degov_simplenews_settings->set('privacy_policy', $privacy_policies)
        ->save();
    }
  }

  /**
   * Should see an element with the content.
   *
   * @Then I should see an :selector element with the content :content
   */
  public function iShouldSeeAnElementWithTheContent($selector, $content) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);

    if (!empty($elements)) {
      foreach ($elements as $element) {
        if ($element->getHtml() === $content) {
          return TRUE;
        }
      }

      try {
        throw new \Exception(sprintf('Could not find any elements matching "%s" with the content "%s"', $selector, $content));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }

    try {
      throw new \Exception(sprintf('Could not find any elements matching "%s"', $selector));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Should see an element with the content vial translation.
   *
   * @Then I should see an :selector element with the content :content via translation
   */
  public function iShouldSeeAnElementWithTheContentViaTranslation($selector, $content) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    $translatedContent = $this->translateString($content);

    if (!empty($elements)) {
      foreach ($elements as $element) {
        if ($element->getHtml() === $translatedContent) {
          return TRUE;
        }
      }

      try {
        throw new \Exception(sprintf('Could not find any elements matching "%s" with the content "%s"', $selector, $translatedContent));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }

    try {
      throw new \Exception(sprintf('Could not find any elements matching "%s"', $selector));
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Rebuild the index.
   *
   * @Given /^I rebuild the "([^"]*)" index$/
   */
  public function iRebuildTheIndex($indexId) {
    $index_storage = \Drupal::entityTypeManager()
      ->getStorage('search_api_index');
    /** @var \Drupal\search_api\IndexInterface $index */
    $index = $index_storage->load($indexId);
    $index->reindex();
    $index->indexItems();
  }

  /**
   * Clear the cache.
   *
   * @Given /^I clear the cache$/
   */
  public function iClearTheCache() {
    drupal_flush_all_caches();
  }

  /**
   * Delete all content.
   *
   * @Given I delete all content
   */
  public function iDeleteAllContent() {
    $mediaIds = \Drupal::entityQuery('media')->execute();
    $mediaStorageHandler = \Drupal::entityTypeManager()->getStorage('media');
    $media = $mediaStorageHandler->loadMultiple($mediaIds);
    $mediaStorageHandler->delete($media);
    $nodeIds = \Drupal::entityQuery('node')->execute();
    $nodesStorageHandler = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $nodesStorageHandler->loadMultiple($nodeIds);
    $nodesStorageHandler->delete($nodes);
  }

  /**
   * Enter the menu placeholder for a media file in specific field.
   *
   * @Given I enter the menu placeholder for a :mediaBundle media file in :fieldSelector
   */
  public function iEnterTheMenuPlaceholderForMediaFileInSpecificField(string $mediaBundle, string $fieldSelector): void {
    if (($id = $this->getMediaItemId($mediaBundle)) !== NULL) {
      $this->getSession()->getPage()->find('css', $fieldSelector)->setValue('<media/file/' . $id . '>');
    }
  }

  /**
   * Enter the placeholder for a media file.
   *
   * @Given I enter the placeholder for a :mediaBundle media file in textarea
   */
  public function iEnterThePlaceholderForMediaFile(string $mediaBundle): void {
    if (($id = $this->getMediaItemId($mediaBundle)) !== NULL) {
      $this->getSession()->executeScript('jQuery("div.form-textarea-wrapper:first iframe").contents().find("p").text("[media/file/' . $id . ']")');
    }
  }

  /**
   * Get media item id.
   */
  private function getMediaItemId($mediaBundle): ?int {
    $mediaResult = \Drupal::entityQuery('media')
      ->condition('bundle', $mediaBundle)
      ->condition('status', 1)
      ->range(0, 1)
      ->execute();
    if (\is_array($mediaResult) && \count($mediaResult) === 1) {
      return reset($mediaResult);
    }
    return NULL;
  }

  /**
   * Execute console command.
   *
   * @Given /^I execute the following console command: "([^"]*)"$/
   */
  public function executeConsoleCommand(string $consoleCommand): void {
    shell_exec($consoleCommand);
  }

  /**
   * Clear all search indexes and index all search indexes.
   *
   * @Given /^I clear all search indexes and index all search indexes$/
   */
  public function clearAllSearchIndexesAndIndexAllSearchIndexes(): void {
    $this->executeConsoleCommand(DRUPAL_ROOT . '/../bin/drush search-api:clear && ' . DRUPAL_ROOT . '/../bin/drush search-api:index');
  }

  /**
   * Reset demo content.
   *
   * @Given /^I reset the demo content$/
   */
  public function resetDemoContent(): void {
    $this->executeConsoleCommand(DRUPAL_ROOT . '/../bin/drush dcreg -y');
  }

  /**
   * Should see the image in.
   *
   * @Given /^I should see the "([^"]*)" in "([^"]*)"$/
   */
  public function iShouldSeeTheImageIn($selector1, $selector2) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector2);

    if (!empty($elements)) {
      foreach ($elements as $element) {
        if (!$element->has('css', $selector1)) {
          try {
            throw new \Exception(sprintf('Could not find "%s" element within "%s" element(s)' . "\r\n" . $element->getHtml(), $selector1, $selector2));
          }
          catch (\Exception $exception) {
            $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
            throw $exception;
          }
        }

      }
    }
    else {
      try {
        throw new \Exception(sprintf('Could not find any elements matching "%s"', $selector2));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
    return TRUE;
  }

  /**
   * Set newsletter privacy policy page.
   *
   * @Then I set newsletter privacy policy page
   */
  public function setNewsletterPrivacyPolicyPage() {
    \Drupal::configFactory()
      ->getEditable('degov_simplenews.settings')
      ->set('privacy_policy', ['de' => '1'])
      ->save();
  }

  /**
   * Should see visible on the page.
   *
   * @Given /^I should see "([^"]*)" element visible on the page$/
   */
  public function iShouldSeeVisibleOnThePage($selector) {
    $startTime = time();
    $wait = self::MAX_SHORT_DURATION_SECONDS * 2;
    do {
      $element = $this->getSession()->getPage();
      $nodes = $element->findAll('css', $selector);
      foreach ($nodes as $node) {
        if ($node->isVisible()) {
          return;
        }
      }
    } while (time() - $startTime < $wait);

    $nodes = $element->findAll('css', $selector);
    if (count($nodes)) {
      try {
        throw new \Exception("Element: \"$selector\" is not visible." . "\r\n" . $element->getHtml());
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
    else {
      try {
        throw new ElementNotFoundException($this->getSession(), 'css selector', $selector, "\r\n" . $element->getHtml());
      }
      catch (ElementNotFoundException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

  /**
   * Should not see the element with css selector.
   *
   * @Given I should not see the element with css selector :selector
   */
  public function iShouldNotSeeTheElementWithCssSelector($selector) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    foreach ($elements as $element) {
      if ($element->isVisible()) {

        try {
          throw new \Exception("The element with selector \"$selector\" is visible.");
        }
        catch (\Exception $exception) {
          $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
          throw $exception;
        }

      }
    }
  }

  /**
   * Should see the element with css selector.
   *
   * @Given I should see the element with css selector :selector
   */
  public function iShouldSeeTheElementWithCssSelector($selector) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    foreach ($elements as $element) {
      if (!$element->isVisible()) {
        try {
          throw new \Exception("The element with selector \"$selector\" is not visible.");
        }
        catch (\Exception $exception) {
          $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
          throw $exception;
        }
      }
    }
  }

  /**
   * Fill in the drupal autocomplete.
   *
   * @Then I fill in the autocomplete :autocomplete with :label via javascript
   */
  public function fillInDrupalAutocomplete($autocomplete, string $text) {
    try {
      $this->getSession()->evaluateScript(sprintf("jQuery('%s').val('%s').trigger('keydown');", $autocomplete, $text));
      $startTime = time();
      do {
        $page = $this->getSession()->getPage();
        $node = $page->find('css', '.ui-menu li a');
        if ($node) {
          // Fixed selector usage, can be swapped by a selector in case
          // necessary later on.
          $this->getSession()->evaluateScript("jQuery('.ui-menu li a').click();");
          return TRUE;
        }
      } while (time() - $startTime < self::MAX_DURATION_SECONDS);

      try {
        throw new ResponseTextException(
          sprintf('Could not find autocomplete after %s seconds', self::MAX_DURATION_SECONDS),
          $this->getSession()
        );
      }
      catch (ResponseTextException $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
    catch (StaleElementReference $e) {
      return FALSE;
    }
  }

  /**
   * Each html content element with css selector is unique.
   *
   * @Then each HTML content element with css selector :selector is unique
   */
  public function eachHtmlContentElementWithCssSelectorIsUnique($selector) {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    $elementText = '';
    $duplicate = FALSE;
    $values = [];
    foreach ($elements as $element) {
      $elementText = $element->getText();
      if (isset($values[$elementText])) {
        $duplicate = TRUE;
        break;
      }
      $values[$elementText] = $elementText;
    }
    if ($duplicate) {
      try {
        throw new \Exception(sprintf('Found duplicate HTML content "%s" elements with CSS selector "%s"', $elementText, $selector));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }

    }
  }

  /**
   * Counts the amount of open windows.
   *
   * @Then there should be a total of :number window(s)
   */
  public function thereShouldBeTotalOfWindow($total) {
    $totalWindows = count($this->getSession()->getWindowNames());
    if ($totalWindows == $total) {
      return TRUE;
    }

    try {
      throw new \Exception($totalWindows . ' windows are found on the page, but should be ' . $total);
    }
    catch (\Exception $exception) {
      $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
      throw $exception;
    }

  }

  /**
   * Enable dev mode.
   *
   * @Then /^I turn on development mode$/
   */
  public function enableDevMode() {
    \Drupal::configFactory()
      ->getEditable('degov_devel.settings')
      ->set('dev_mode', TRUE)
      ->save();
  }

}
