<?php

declare(strict_types=1);

namespace Drupal\node_action;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class UserInteractionFacade.
 */
class UserInteractionFacade {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  public $currentUser;

  /**
   * Redirect response factory.
   *
   * @var \Drupal\node_action\RedirectResponseFactory
   */
  public $redirectResponseFactory;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  public $messenger;

  /**
   * String translation adapter.
   *
   * @var \Drupal\node_action\StringTranslationAdapter
   */
  public $stringTranslationAdapter;

  /**
   * Url factory.
   *
   * @var \Drupal\node_action\UrlFactory
   */
  public $urlFactory;

  /**
   * UserInteractionFacade constructor.
   */
  public function __construct(AccountProxyInterface $currentUser, RedirectResponseFactory $redirectResponseFactory, MessengerInterface $messenger, StringTranslationAdapter $stringTranslationAdapter, UrlFactory $urlFactory) {
    $this->currentUser = $currentUser;
    $this->redirectResponseFactory = $redirectResponseFactory;
    $this->messenger = $messenger;
    $this->stringTranslationAdapter = $stringTranslationAdapter;
    $this->urlFactory = $urlFactory;
  }

}
