<?php

namespace Drupal\degov_theme;

/**
 * Class NumberFormatShort.
 *
 * @package Drupal\degov_theme
 */
class NumberFormatShort {

  /**
   * Number Shortener.
   */
  public static function format($n, int $precision = 1): string {
    if ($n < 900) {
      // 0 - 900
      $n_format = number_format($n, $precision);
      $suffix = '';
    }
    else {
      if ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = t('K');
      }
      else {
        if ($n < 900000000) {
          // 0.9m-850m
          $n_format = number_format($n / 1000000, $precision);
          $suffix = t('M');
        }
      }
    }
    // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50".
    if ($precision > 0) {
      $dotzero = '.' . str_repeat('0', $precision);
      $n_format = str_replace($dotzero, '', $n_format);
    }
    return $n_format . $suffix;
  }

}
