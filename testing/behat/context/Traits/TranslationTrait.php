<?php

namespace Drupal\degov\Behat\Context\Traits;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Trait TranslationTrait.
 */
trait TranslationTrait {

  /**
   * Langcode.
   *
   * @var string
   */
  private $langcode = 'de';

  /**
   * Translate string.
   */
  public function translateString(string $text): string {
    $translateableMarkup = new TranslatableMarkup($text, [], []);
    $translatedString = \Drupal::translation()
      ->translateString($translateableMarkup);
    return $translatedString;
  }

}
