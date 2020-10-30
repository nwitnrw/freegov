<?php

namespace Drupal\entity_reference_timer;

use Drupal\entity_reference_timer\Plugin\Field\Exception\NoStartButEndException;
use Drupal\entity_reference_timer\Plugin\Field\Exception\StartAfterEndException;

/**
 * Class InputValidator.
 */
class InputValidator {

  /**
   * Handle date input exception.
   */
  public static function handleDateInputException(array $element): void {
    $startDate = $element['start_date']['#value']['date'];
    $startTime = $element['start_date']['#value']['time'];
    $endDate = $element['end_date']['#value']['date'];
    $endTime = $element['end_date']['#value']['time'];

    if ($element['publish_timer']['#value'] !== 1) {
      return;
    }

    if (empty($startDate) &&
      empty($startTime) &&
      empty($endDate) &&
      empty($endTime)
    ) {
      return;
    }

    if (empty($startDate) && !empty($endDate)) {
      throw new NoStartButEndException();
    }

    if (!empty($startDate) && !empty($endDate)) {
      $startDate = strtotime($startDate . ' ' . $startTime);
      $endDate = strtotime($endDate . ' ' . $endTime);

      if ($startDate >= $endDate) {
        throw new StartAfterEndException();
      }
    }

  }

}
