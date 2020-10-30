<?php

namespace Drupal\degov_theme\Preprocess;

/**
 * Class FooterBlock.
 *
 * @package Drupal\degov_theme\Preprocess
 */
class FooterBlock {

  /**
   * Preprocess FooterBlock theme.
   *
   * @param array $variables
   *   Variables.
   */
  public static function preprocess(array &$variables): void {
    if ($variables['content']['#theme'] == 'menu__main') {
      $variables['content']['#theme'] = 'menu__footer';
      $variables['content']['#items'] = array_slice($variables['content']['#items'], 0, 6);
    }
  }

}
