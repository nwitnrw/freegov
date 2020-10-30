<?php

namespace Drupal\degov_node_external_teaser\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class RedirectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => 'redirectExternalTeaser',
    ];
  }

  /**
   * Redirect external teaser to url in view_mode full.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Kernel events REQUEST.
   */
  public function redirectExternalTeaser(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }
    if ($request->attributes->get('node')->getType() !== 'external_teaser') {
      return;
    }
    $node = $request->attributes->get('node');
    $uri = $node->get('field_link')->first()->get('uri')->getString();
    $event->setResponse(new TrustedRedirectResponse($uri));
  }

}
