<?php

namespace Drupal\degov_media\EventSubscriber;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\media\MediaInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class MediaAccessSubscriber.
 *
 * Redirect to front page if user should not be allowed to access
 * canonical route of media entity.
 */
class MediaAccessSubscriber implements EventSubscriberInterface {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * MediaManagerSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Request handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Get response event.
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');
    $media = $request->attributes->get('media');

    // Check if the user tries to access the media canonical route.
    if ($route === 'entity.media.canonical' && $media instanceof MediaInterface && $media->hasField('field_include_search') && $media->field_include_search->value === '0' && !$this->currentUser->hasPermission('access media overview')) {
      // Redirect the user to the front page with status 403 if the media is not
      // for search and user has no permissions to access.
      $url = Url::fromRoute('<front>');
      $response = new CacheableRedirectResponse($url->toString(), 301);
      $response->addCacheableDependency($media);
      $response->addCacheableDependency($this->currentUser);
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    return $events;
  }

}
