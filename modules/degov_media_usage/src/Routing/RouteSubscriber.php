<?php

declare(strict_types=1);

namespace Drupal\degov_media_usage\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\degov_media_usage\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The EntityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Add media usage routes.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $mediaType = $this->entityTypeManager->getDefinition('media');
    if (($mediaType instanceof EntityTypeInterface) === FALSE) {
      return;
    }

    $route = $this->getMediaUsageRefsRoute($mediaType);
    if ($route instanceof Route) {
      $collection->add('entity.media.degov_media_usage_refs', $route);
    }
  }

  /**
   * Get the media usage route for an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type we want a route for.
   *
   * @return null|\Symfony\Component\Routing\Route
   *   The route.
   */
  private function getMediaUsageRefsRoute(EntityTypeInterface $entityType): ?Route {
    if ($mediaUsageRefs = $entityType->getLinkTemplate('degov-media-usage-refs')) {
      $route = new Route($mediaUsageRefs);

      $options = [
        '_admin_route' => TRUE,
        'parameters' => [
          'media' => [
            'type' => 'media',
          ],
        ],
      ];

      $route
        ->addDefaults(
          [
            '_controller' => '\Drupal\degov_media_usage\Controller\MediaUsageController::referencesPage',
            '_title_callback' => '\Drupal\degov_media_usage\Controller\MediaUsageController::referencesTitle',
            'media' => '[\d\,]+',
          ]
        )
        ->addRequirements(
          [
            '_permission' => 'access media overview',
          ]
        )
        ->setOptions($options);

      return $route;
    }

    return NULL;
  }

}
