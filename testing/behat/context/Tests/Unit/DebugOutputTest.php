<?php

namespace Drupal\degov\Behat\Context\Tests\Unit;

use Drupal\degov\Behat\Context\DebugOutput;
use Drupal\degov\Behat\Context\Exception\ErrorTextFoundException;
use Drupal\degov\Behat\Context\Traits\ErrorTrait;
use PHPUnit\Framework\TestCase;

/**
 * Testclass to prevent false positives
 * @package Drupal\degov\Behat\Context\Tests\Unit
 */
class DebugOutputTest extends TestCase {

  use ErrorTrait;

  /**
   * @var string
   */
  private $teststring = <<<TESTSTRING
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut
labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
consetetur sadipscing elitr.
TESTSTRING;

  public function testExceptionOnErrorString(): void {
    $this->expectException(ErrorTextFoundException::class);
    $debugOutput = new DebugOutput();
    $chooseErrorText = random_int(1, \count(self::$errorTexts));
    $chooseErrorText--;
    $additionalString = self::$errorTexts[$chooseErrorText];
    $innerTeststring = $this->teststring . $additionalString;
    $debugOutput->isErrorOnCurrentPage($innerTeststring);
  }

  public function testNoExceptionOnNonErrorString(): void {
    $innerTeststring = $this->teststring;
    $debugOutput = new DebugOutput();
    self::assertFalse($debugOutput->isErrorOnCurrentPage($innerTeststring));
  }

}
