<?php

namespace Drupal\Tests\filter_disallow\Kernel;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\filter_disallow\Plugin\Filter\FilterHtmlDisallow;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Class FilterTest.
 */
class FilterTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'filter_disallow',
    'user',
    'system',
  ];

  /**
   * Filter.
   *
   * @var \Drupal\filter_disallow\Plugin\Filter\FilterHtmlDisallow
   */
  private $filter;

  /**
   * Text with unwanted html.
   *
   * @var string
   */
  private $textWithUnwantedHtml;

  /**
   * Text with unwanted html stripped text.
   *
   * @var string
   */
  private $textWithUnwantedHtmlStripped;

  /**
   * Clean text.
   *
   * @var string
   */
  private $cleanText;

  /**
   * Special characters.
   *
   * @var string
   */
  private $specialChars;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', ['sequences']);

    $this->filter = FilterHtmlDisallow::create($this->container, [], '', ['provider' => '']);

    $user = $this->createUser(['view filter_disallow messages']);
    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $this->container->get('current_user');
    $current_user->setAccount($user);

    $this->textWithUnwantedHtml = '<p>In this text there is <script>a script tag</script> that should be removed äöüÄÖÜßßß</p>';
    $this->textWithUnwantedHtmlStripped = '<p>In this text there is a script tag that should be removed äöüÄÖÜßßß</p>';
    $this->cleanText = '<p>In this text there is nothing that should be removed äöüÄÖÜßßß</p>';
    $this->specialChars = '<p> ^ ° ¬ ! ¹ ¡ " ² ⅛ § ³ £ $ ¼ ¤ % ½ ⅜ & ¬ ⅝ / { ⅞ ( [ ™ ) ] = } ° ? \ ¿ ` ¸ ¸ @ Ω ł Ł € ţ Ţ ← ¥ ↓ ↑ î Î ø Ø þ Þ ¨ + * ~ â Â ş Ş ð Ð đ ª ŋ Ŋ ħ Ħ ̣ ˙ ĸ & ˝ ˝ ă Ă # \' ` < > | » › « ‹ ¢ © „ ‚ “ ‘ ” ’ µ º , ; · × … ÷ - _ – — </p>';
  }

  /**
   * Test filter tag is removed.
   */
  public function testFilterTagIsRemoved(): void {
    $filteredText = $this->filter->stripHtmlTag($this->textWithUnwantedHtml, 'script');
    self::assertSame($this->textWithUnwantedHtmlStripped, $filteredText);
  }

  /**
   * Test filter tag is not removed.
   */
  public function testFilterTagIsNotRemoved(): void {
    $filteredText = $this->filter->stripHtmlTag($this->cleanText, 'script');
    self::assertSame($this->cleanText, $filteredText);
  }

  /**
   * Test messages are posted on removal.
   */
  public function testMessagesArePostedOnRemoval(): void {
    $this->filter->stripHtmlTag($this->textWithUnwantedHtml, 'script');
    $messages = $this->container->get('messenger')
      ->messagesByType(MessengerInterface::TYPE_WARNING);
    self::assertCount(1, $messages);
  }

  /**
   * Test messages are not posted if nothing was filtered.
   */
  public function testMessagesAreNotPostedIfNothingWasFiltered(): void {
    $this->filter->stripHtmlTag($this->cleanText, 'script');
    $messages = $this->container->get('messenger')
      ->messagesByType(MessengerInterface::TYPE_WARNING);
    self::assertCount(0, $messages);
  }

  /**
   * Test special characters.
   */
  public function testSpecialChars(): void {
    $filteredText = $this->filter->stripHtmlTag($this->specialChars, 'script');
    self::assertSame($this->specialChars, $filteredText);
  }

}
