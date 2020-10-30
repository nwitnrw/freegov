<?php

namespace Drupal\degov\Behat\Context;

use Drupal\degov\Behat\Context\Traits\DebugOutputTrait;
use Drupal\degov\Behat\Context\Traits\ErrorTrait;

/**
 * Class SmokeTestContext.
 */
class SmokeTestContext extends DrupalContext {

  use ErrorTrait;

  use DebugOutputTrait;

  /**
   * Username.
   *
   * @var string
   */
  private $username;

  /**
   * Password.
   *
   * @var string
   */
  private $password;

  /**
   * SmokeTestContext constructor.
   */
  public function __construct(array $parameters) {
    $this->username = $parameters['admin_account_credentials']['0'];
    $this->password = $parameters['admin_account_credentials']['1'];

    parent::__construct();
  }

  /**
   * Login by custom parameters.
   *
   * @Given /^I am logged in as user with the account details from Behat config file$/
   */
  public function loginByCustomParameters(): void {
    $this->getSession()->visit($this->locatePath('/user'));
    $element = $this->getSession()->getPage();
    $element->fillField('edit-name', $this->username);
    $element->fillField('edit-pass', $this->password);

    $submit = $element->findButton('edit-submit');
    if (empty($submit)) {
      throw new \Exception(sprintf("No submit button at %s", $this->getSession()->getCurrentUrl()));
    }
    $submit->click();

    if (!$this->loggedIn()) {
      try {
        throw new \Exception(sprintf("Unable to determine if logged in because 'log_out' link cannot be found for user '%s'", $this->username));
      }
      catch (\Exception $exception) {
        $this->generateCurrentBrowserViewDebuggingOutput(__METHOD__);
        throw $exception;
      }
    }
  }

}
