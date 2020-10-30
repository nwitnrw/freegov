<?php

namespace Drupal\degov_search_content_solr\Plugin\facets\widget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\DropdownWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The dropdown widget.
 *
 * @FacetsWidget(
 *   id = "tags",
 *   label = @Translation("Tags"),
 *   description = @Translation("A configurable widget that shows a dropdown for tags."),
 * )
 */
final class TagsWidget extends DropdownWidget implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager): void {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setEntityTypeManager($container->get('entity_type.manager'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);

    foreach ($build['#items'] as &$item) {
      if ($transformedItem = $this->transformTidToName($item)) {
        $item = $transformedItem;
      }
    }

    return $build;
  }

  /**
   * Transform term id to name.
   */
  private function transformTidToName(array $item): ?array {
    $resultSet = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => 'tags',
        'tid' => $item['#title']['#value'],
      ]);

    if (!empty($resultSet)) {
      $result = array_shift($resultSet);

      $item['#title']['#value'] = $result->getName();

      return $item;
    }

    return NULL;
  }

}
