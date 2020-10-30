<?php

namespace Drupal\degov\Behat\Context\Exception;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;

/**
 * Class TextNotFoundException.
 */
class TextNotFoundException extends \Exception {

  /**
   * Session.
   *
   * @var \Behat\Mink\Driver\DriverInterface|Session
   */
  private $session;

  /**
   * Driver.
   *
   * @var \Behat\Mink\Driver\DriverInterface
   */
  private $driver;

  /**
   * Initializes exception.
   *
   * @param string $message
   *   Optional message.
   * @param \Behat\Mink\Driver\DriverInterface|Session $driver
   *   Driver instance (or session for BC)
   * @param \Exception|null $exception
   *   Expectation exception.
   */
  public function __construct($message, $driver, \Exception $exception = NULL) {
    if ($driver instanceof Session) {
      @trigger_error('Passing a Session object to the ExpectationException constructor is deprecated as of Mink 1.7. Pass the driver instead.', E_USER_DEPRECATED);

      $this->session = $driver;
      $this->driver = $driver->getDriver();
    }
    elseif (!$driver instanceof DriverInterface) {
      // Trigger an exception as we cannot typehint a disjunction.
      throw new \InvalidArgumentException('The ExpectationException constructor expects a DriverInterface or a Session.');
    }
    else {
      $this->driver = $driver;
    }

    if (!$message && NULL !== $exception) {
      $message = $exception->getMessage();
    }

    parent::__construct($this->getMessageWithAllHtml($message), 0, $exception);
  }

  /**
   * Get message with all html.
   */
  private function getMessageWithAllHtml(string $message) {
    return $message;
  }

}
