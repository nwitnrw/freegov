<?php

namespace Drupal\degov_theme\Preprocess;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Node.
 *
 * @package Drupal\degov_theme\Preprocess
 */
final class Node implements ContainerInjectionInterface {

  /**
   * Definition of DateFormatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * Definition of PathMatcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  private $pathMatcher;

  /**
   * Node constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Date formatter object.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   Path Matcher object.
   */
  public function __construct(DateFormatterInterface $dateFormatter, PathMatcherInterface $pathMatcher) {
    $this->dateFormatter = $dateFormatter;
    $this->pathMatcher = $pathMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('date.formatter'),
      $container->get('path.matcher')
    );
  }

  /**
   * Preprocess node theme.
   *
   * @param array $variables
   *   Variables.
   */
  public function preprocess(array &$variables): void {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $variables['node'];
    // Add created time to the search teaser template.
    if ($variables['view_mode'] === 'teaser') {
      $variables['bundle'] = $variables['node']->type->entity->label();
      $variables['date'] = $this->dateFormatter
        ->format($node->getCreatedTime(), 'custom', 'd.m.Y');
    }

    $variables['is_front'] = $this->pathMatcher->isFrontPage();
  }

  /**
   * Helper function to determine which image style to use.
   *
   * Determine responsive image style for particular view mode.
   *
   * @param string $view_mode_name
   *   The view mode to test.
   *
   * @return string
   *   Responsive image style.
   */
  protected function determineResponsiveImageStyle(string $view_mode_name): string {
    $responsive_image_style_id = '';
    if (in_array($view_mode_name, [
      'long_text',
      'preview',
      'small_image',
    ])) {
      $responsive_image_style_id = 'teaser_squared';
    }
    elseif ($view_mode_name === 'slim') {
      $responsive_image_style_id = 'teaser_landscape';
    }
    return $responsive_image_style_id;
  }

}
