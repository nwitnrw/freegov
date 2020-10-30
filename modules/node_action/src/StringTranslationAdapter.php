<?php

declare(strict_types=1);

namespace Drupal\node_action;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class StringTranslationAdapter.
 */
class StringTranslationAdapter {

  use StringTranslationTrait;

  /**
   * Translates a string to the current language or to a given language.
   *
   * See \Drupal\Core\StringTranslation\TranslatableMarkup::__construct() for
   * important security information and usage guidelines.
   *
   * In order for strings to be localized, make them available in one of the
   * ways supported by the
   * @link https://www.drupal.org/node/322729 Localization API @endlink. When
   * possible, use the \Drupal\Core\StringTranslation\StringTranslationTrait
   * $this->t(). Otherwise create a new
   * \Drupal\Core\StringTranslation\TranslatableMarkup object.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   (optional) An associative array of replacements to make after
   *   translation. Based on the first character of the key, the value is
   *   escaped and/or themed. See
   *   \Drupal\Component\Render\FormattableMarkup::placeholderFormat() for
   *   details.
   * @param array $options
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'langcode' (defaults to the current language): A language code, to
   *     translate to a language other than what is used to display the page.
   *   - 'context' (defaults to the empty context): The context the source
   *     string belongs to. See the
   *     @link i18n Internationalization topic @endlink for more information
   *     about string contexts.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   *
   * @see \Drupal\Component\Render\FormattableMarkup::placeholderFormat()
   * @see \Drupal\Core\StringTranslation\TranslatableMarkup::__construct()
   *
   * @ingroup sanitization
   */
  public function t($string, array $args = [], array $options = []) {
    return new TranslatableMarkup($string, $args, $options, $this->getStringTranslation());
  }

}
