<?php

declare(strict_types=1);

namespace Drupal\node_action;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Redirector.
 */
class Redirector {

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * Redirect response factory.
   *
   * @var \Drupal\node_action\RedirectResponseFactory
   */
  private $redirectResponseFactory;

  /**
   * Url factory.
   *
   * @var \Drupal\node_action\UrlFactory
   */
  private $urlFactory;

  /**
   * Redirector constructor.
   */
  public function __construct(MessengerInterface $messenger, RedirectResponseFactory $redirectResponseFactory, UrlFactory $urlFactory) {
    $this->messenger = $messenger;
    $this->redirectResponseFactory = $redirectResponseFactory;
    $this->urlFactory = $urlFactory;
  }

  /**
   * Compute redirect response by entities.
   */
  public function computeRedirectResponseByEntities(array $entities, string $routeName): ?Response {
    $entityIds = [];

    foreach ($entities as $entity) {
      $entityIds[$entity->id()] = $entity->getTitle();
    }

    if (\count($entityIds) < 1) {
      $this->removeDefaultMessage();
      return NULL;
    }

    return $this->computeRedirectResponse($routeName, ['entityIds' => $entityIds]);
  }

  /**
   * Remove default message.
   */
  private function removeDefaultMessage(): void {
    $this->messenger->deleteByType('error');
  }

  /**
   * Compute redirect response.
   */
  public function computeRedirectResponse(string $routeName, array $routeParameters = []): Response {
    $redirectUrl = $this->urlFactory->create($routeName, $routeParameters);
    $response = $this->redirectResponseFactory->create($redirectUrl->toString());
    return $response->send();
  }

}
