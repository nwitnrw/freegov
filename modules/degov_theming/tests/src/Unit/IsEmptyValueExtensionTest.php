<?php

namespace Drupal\Tests\degov_theming\Unit;

use Drupal\degov_theming\TwigExtension\IsEmptyValueExtension;
use Drupal\Tests\UnitTestCase;

/**
 * Class IsEmptyValueExtensionTest.
 */
class IsEmptyValueExtensionTest extends UnitTestCase {

  /**
   * Extension.
   *
   * @var \Drupal\degov_theming\TwigExtension\IsEmptyValueExtension
   */
  protected $extension;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->getMockBuilder('Drupal\Core\Render\RendererInterface')
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \Drupal\Core\Routing\UrlGeneratorInterface $url_generator */
    $url_generator = $this->getMockBuilder('Drupal\Core\Routing\UrlGeneratorInterface')
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \Drupal\Core\Theme\ThemeManagerInterface $theme_manager */
    $theme_manager = $this->getMockBuilder('Drupal\Core\Theme\ThemeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $this->getMockBuilder('Drupal\Core\Datetime\DateFormatterInterface')
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory */
    $loggerFactory = $this->getMockBuilder('Drupal\Core\Logger\LoggerChannelFactory')
      ->disableOriginalConstructor()
      ->getMock();
    $this->extension = new IsEmptyValueExtension($renderer, $url_generator, $theme_manager, $date_formatter, $loggerFactory);
  }

  /**
   * Is empty.
   *
   * @dataProvider providerTestIsEmpty
   */
  public function testIsEmpty($expected_result, $render_array, $stripTags = ''): void {
    $result = $this->extension->isEmpty($render_array, $stripTags);
    $this->assertSame($expected_result, $result);
  }

  /**
   * Provider test is empty.
   */
  public function providerTestIsEmpty(): array {
    return [
      [FALSE, 'This is a test a simple test'],
      [FALSE, '   this is a test with text, padding

         and returns   ',
      ],
      [FALSE, '   this is a test with text, left padding

         and returns',
      ],
      [FALSE, "   this is a test with text, left padding

         and returns", "",
      ],
      [FALSE, '<p><h1>This is a test with tags</h1>this is a test</p>'],
      [FALSE, '<p>
                <h1>This is a test with tags</h1>
                this is a test
              </p>',
      ],
      [FALSE, '    <p><h1>This is a test with tags and padding</h1>this is a test</p>     '],
      [FALSE, '    <p><h1>This is a test with tags and left padding</h1>this is a test</p>'],
      [FALSE, '<img src="http://www.example.com/image.png" alt="This test only contains tags" />'],
      [
        TRUE,
        '<img src="http://www.example.com/image.png" alt="This test only contains tags">',
        '<blockquote>',
      ],
      [TRUE, ''],
      [TRUE, '\n\r       '],
      [TRUE, '     \n\r       ', ''],
      [TRUE, "\n\r       "],
      [TRUE, "     \n\r       ", ""],
      [FALSE, '\n\rthis is a test with characterMask and right padding       '],
      [FALSE, [
        '#prefix' => '<p class="wrapper">',
        '#suffix' => '</p>',
        '#markup' => 'This is a markup renderable array',
        '#printed' => TRUE,
      ],
      ],
      [FALSE, [
        '0' => [
          'target_id' => '54',
        ],
      ],
      ],
    ];
  }

}
