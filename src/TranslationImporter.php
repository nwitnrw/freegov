<?php

namespace Drupal\degov;

use Drupal\locale\Gettext;

/**
 * Class TranslationImporter.
 */
class TranslationImporter {

  /**
   * Import for profile.
   */
  public static function importForProfile(string $installationProfile = 'degov', string $langcode = 'de-de'): void {
    self::import($installationProfile, $langcode);
  }

  /**
   * Import for profiles.
   */
  public static function importForProfiles(array $installationProfiles, string $langcode = 'de-de'): void {
    foreach ($installationProfiles as $installationProfile) {
      self::importForProfile($installationProfile, $langcode);
    }
  }

  /**
   * Import.
   */
  private static function import(string $installationProfile, string $langcode): void {
    $file = new \stdClass();
    $file->uri = drupal_get_path('profile', $installationProfile) . '/translations/' . $langcode . '.po';
    $file->langcode = 'de';

    Gettext::fileToDatabase($file, [
      'overwrite_options' => [
        'not_customized' => TRUE,
        'customized'     => TRUE,
      ],
      'customized'        => LOCALE_CUSTOMIZED,
    ]);
  }

}
