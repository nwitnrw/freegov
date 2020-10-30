<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node_action\StringTranslationAdapter;

/**
 * Class UnpublishAction.
 */
class UnpublishAction extends ActionAbstract implements AccessCheckInterface {

  use MessagesTrait;

  /**
   * Permission.
   *
   * @var string
   */
  private static $permission = 'use node unpublish action';

  /**
   * Action name.
   *
   * @var string
   */
  private static $actionName = 'Unpublish';

  /**
   * UnpublishAction constructor.
   */
  public function __construct(AccountProxyInterface $currentUser, MessengerInterface $messenger, StringTranslationAdapter $stringTranslationAdapter) {
    parent::__construct($currentUser, self::$permission, $messenger, self::$actionName, $stringTranslationAdapter);
  }

}
