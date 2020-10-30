<?php

namespace Drupal\degov_theming\TwigExtension;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Template\TwigExtension;
use Drupal\Core\Theme\ThemeManagerInterface;
use Twig\TwigFunction;

/**
 * Class IsEmptyValueExtension.
 */
class IsEmptyValueExtension extends TwigExtension {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * IsEmptyValueExtension constructor.
   */
  public function __construct(RendererInterface $renderer, UrlGeneratorInterface $url_generator, ThemeManagerInterface $theme_manager, DateFormatterInterface $date_formatter, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($renderer, $url_generator, $theme_manager, $date_formatter, $loggerFactory);
    $this->logger = $loggerFactory->get('degov_theme');
  }

  /**
   * Get functions.
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('is_empty', [$this, 'isEmpty']),
    ];
  }

  /**
   * Get name.
   */
  public function getName(): string {
    return 'is_empty';
  }

  /**
   * Check if empty.
   *
   * @param array|\Countable|null $build
   *   Build.
   * @param string $stripTags
   *   Strip tags.
   *
   * @return bool
   *   True if empty.
   */
  public function isEmpty($build, string $stripTags = ''): bool {
    try {
      if ($this->isEntityReference($build)) {
        return FALSE;
      }

      $build = $this->renderVar($build);
      $build = ($stripTags === '') ? strip_tags($build, '<img>,<picture>,<color>') : strip_tags($build, $stripTags);
      $build = \trim($build, " \t\n\r\0\x0B");
      $build = \trim($build, ' \t\n\r\0\x0B');
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }

    if (empty($build)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Is entity reference.
   *
   * @param array|\Countable|null $build
   *   Build.
   *
   * @return bool
   *   True if entity reference.
   */
  private function isEntityReference($build): bool {
    if (!\is_array($build) || \count($build) <= 0) {
      return FALSE;
    }

    if (($element = array_shift($build)) && isset($element['target_id']) && is_numeric($element['target_id'])) {
      return TRUE;
    }

    return FALSE;
  }

}
