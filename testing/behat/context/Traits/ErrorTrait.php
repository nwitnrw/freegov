<?php

namespace Drupal\degov\Behat\Context\Traits;

/**
 * Trait ErrorTrait.
 *
 * @package Drupal\degov\Behat\Context\Traits
 */
trait ErrorTrait {

  /**
   * Error texts.
   *
   * @var array
   */
  private static $errorTexts = [
    'Error',
    'Warning',
    'Notice',
    'The import failed due for the following reasons:',
    'Es wurde eine nicht erlaubte Auswahl entdeckt.',
    'An AJAX HTTP error occurred.',
    'Nicht erfüllte Systemvoraussetzungen',
    'Fehlermeldung',
    ' ist ungültig.',
  ];

}
