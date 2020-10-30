<?php

declare(strict_types = 1);

namespace Drupal\degov_password_policy\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function in_array;

/**
 * Class PasswordExpirationSubscriber.
 *
 * @package Drupal\degov_password_policy\EventSubscriber
 */
final class PasswordExpirationSubscriber implements EventSubscriberInterface {

  use RedirectDestinationTrait;
  use StringTranslationTrait;

  /**
   * The AccountProxyInterface.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * The EntityStorageInterface.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStorage;

  /**
   * The Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  private $request;

  /**
   * The MessengerInterface.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * PasswordExpirationSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The AccountProxyInterface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManagerInterface.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The RequestStack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The MessengerInterface.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    AccountProxyInterface $currentUser,
    EntityTypeManagerInterface $entityTypeManager,
    RequestStack $requestStack,
    MessengerInterface $messenger
  ) {
    $this->currentUser = $currentUser;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->request = $requestStack->getCurrentRequest();
    $this->messenger = $messenger;
  }

  /**
   * Get a list of subscribed events.
   *
   * @return array
   *   A list of subscribed events.
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => 'checkPasswordExpiration',
    ];
  }

  /**
   * Check if the user's password is expired, handle the event appropriately.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The current event.
   */
  public function checkPasswordExpiration(GetResponseEvent $event): void {
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->userStorage->load($this->currentUser->id());

      /*
       * Check should only happen if:
       * - not in an ajax callback
       * - not on the user edit form
       * - not logging out
       * - not requesting a new password
       */
      $routeName = $this->request->attributes->get(RouteObjectInterface::ROUTE_NAME);
      $isIgnoredRoute = in_array(
        $routeName,
        [
          'entity.user.edit_form',
          'system.ajax',
          'user.logout',
          'user.pass',
        ]
      );
      $isAjax = $this->request->headers->get('X_REQUESTED_WITH') === 'XMLHttpRequest';
      $expirationType = $user->get('field_password_expiration')->get(0);

      if ($expirationType && !$isIgnoredRoute && !$isAjax) {
        $redirect = $expirationType->getValue()['value'] === 'redirect';

        if ($redirect) {
          $this->messenger->addError(
            $this->t('This site requires that you change your password every 40 days. Please change your password to proceed.')
          );

          $url = Url::fromRoute(
            'entity.user.edit_form',
            ['user' => $this->currentUser->id()],
            ['query' => $this->getDestinationArray()]
          );
          $event->setResponse(new RedirectResponse($url->toString()));
        }
        else {
          $this->messenger->addWarning($this->t('Your password will expire soon, please update it!'));
        }
      }
    }
  }

}
