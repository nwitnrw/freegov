<?php

namespace Drupal\Tests\entity_reference_timer\Unit;

use Drupal\entity_reference_timer\InputValidator;
use Drupal\entity_reference_timer\Plugin\Field\Exception\NoStartButEndException;
use Drupal\entity_reference_timer\Plugin\Field\Exception\StartAfterEndException;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the text formatters functionality.
 *
 * @group text
 */
class ValidateDateTest extends UnitTestCase {

  /**
   * Test no start but exception.
   */
  public function testNoStartButEndException(): void {
    self::expectException(NoStartButEndException::class);
    $element['publish_timer']['#value'] = 1;
    $element['start_date']['#value']['date'] = NULL;
    $element['start_date']['#value']['time'] = '090909AM';
    $element['end_date']['#value']['date'] = '2018-11-12';
    $element['end_date']['#value']['time'] = '090909AM';

    InputValidator::handleDateInputException($element);
  }

  /**
   * Test start after end exception.
   */
  public function testStartAfterEndException(): void {
    self::expectException(StartAfterEndException::class);
    $element['publish_timer']['#value'] = 1;
    $element['start_date']['#value']['date'] = '2018-11-12';
    $element['start_date']['#value']['time'] = '090909AM';
    $element['end_date']['#value']['date'] = '2016-11-12';
    $element['end_date']['#value']['time'] = '090909AM';

    InputValidator::handleDateInputException($element);
  }

  /**
   * Test no input.
   * @doesNotPerformAssertions
   */
  public function testNoInput(): void {
    $element['publish_timer']['#value'] = 1;
    $element['start_date']['#value']['date'] = NULL;
    $element['start_date']['#value']['time'] = NULL;
    $element['end_date']['#value']['date'] = NULL;
    $element['end_date']['#value']['time'] = NULL;

    InputValidator::handleDateInputException($element);
  }

  /**
   * Test publish timer disabled.
   * @doesNotPerformAssertions
   */
  public function testPublishTimerDisabled(): void {
    $element['publish_timer']['#value'] = 0;
    $element['start_date']['#value']['date'] = '2018-11-12';
    $element['start_date']['#value']['time'] = '090909AM';
    $element['end_date']['#value']['date'] = '2016-11-12';
    $element['end_date']['#value']['time'] = '090909AM';

    InputValidator::handleDateInputException($element);
  }

  /**
   * Test start date only.
   * @doesNotPerformAssertions
   */
  public function testStartDateOnly(): void {
    self::doesNotPerformAssertions();
    $element['publish_timer']['#value'] = 0;
    $element['start_date']['#value']['date'] = '2018-11-12';
    $element['start_date']['#value']['time'] = '090909AM';
    $element['end_date']['#value']['date'] = NULL;
    $element['end_date']['#value']['time'] = NULL;

    InputValidator::handleDateInputException($element);
  }

}
