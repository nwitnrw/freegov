<?php

namespace Drupal\degov_search_media_solr\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to filter the media search.
 *
 * @Block(
 *   id = "degov_search_media_solr_filter",
 *   admin_label = @Translation("deGov search media Solr filters")
 * )
 */
final class DegovSearchMediaFilter extends BlockBase implements ContainerFactoryPluginInterface {

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
      '#title' => t('Filter media search results'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['block-degov-search-media-filter']],
    ];

    $ids = [
      'degov_theme_search_media_bundle',
      'degov_theme_search_media_tags',
      'search_content_topic',
      'degov_theme_media_publish_date',
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
