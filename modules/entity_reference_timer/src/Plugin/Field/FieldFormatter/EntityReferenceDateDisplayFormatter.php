<?php

namespace Drupal\entity_reference_timer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\entity_reference_display\Plugin\Field\FieldFormatter\EntityReferenceDisplayFormatter;
use Drupal\entity_reference_timer\Service\EntityReferenceTimerVisibilityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity_reference_display_default' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_date_display_default",
 *   label = @Translation("Selected display mode for entity reference date"),
 *   description = @Translation("This formatter allows you to render referenced entities by selected display mode."),
 *   field_types = {
 *     "entity_reference_date",
 *   }
 * )
 */
final class EntityReferenceDateDisplayFormatter extends EntityReferenceDisplayFormatter {

  /**
   * The entity reference timer visibility service.
   *
   * @var \Drupal\entity_reference_timer\Service\EntityReferenceTimerVisibilityService
   */
  protected $visibilityService;

  /**
   * @param \Drupal\entity_reference_timer\Service\EntityReferenceTimerVisibilityService $visibility_service
   */
  public function setVisibilityService(EntityReferenceTimerVisibilityService $visibility_service): void {
    $this->visibilityService = $visibility_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setVisibilityService($container->get('entity_reference_timer.visibility_service'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    foreach ($items as $item) {
      $this->visibilityService->addExpirationDateToParentNode($item);
    }
    $items->filter([$this->visibilityService, 'isVisible']);
    return parent::viewElements($items, $langcode);
  }

}
