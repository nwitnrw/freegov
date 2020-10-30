<?php

namespace Drupal\degov_search_content\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DegovSearchContentFilter.
 *
 * Combines facets by blocks into a single block for filtering
 * the search results.
 *
 * @Block(
 *   id = "degov_search_content_filter",
 *   admin_label = @Translation("DeGov search content filters")
 * )
 */
final class DegovSearchContentFilter extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
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
  public function build() {
    $form['filter'] = [
      '#type' => 'details',
      '#title' => t('Filter search results'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['block-degov-search-content-filter']],
    ];

    $ids = [
      'search_content_node_bundle',
      'search_content_tags',
      'search_content_topic',
      'search_content_node_changed',
    ];
    foreach ($ids as $id) {
      $block = Block::load($id);
      if ($block) {
        $block->disable();
        $form['filter'][$id] = $this->entityTypeManager
          ->getViewBuilder('block')
          ->view($block);
      }
    }

    return $form;
  }

}
