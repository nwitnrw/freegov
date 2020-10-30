<?php

declare(strict_types=1);

namespace Drupal\degov_common\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SidebarParagraphs.
 *
 * @Block(
 *   id = "sidebar_paragraphs",
 *   admin_label = @Translation("Sidebar paragraphs from Node entity"),
 *   category = @Translation("Blocks")
 * )
 */
final class SidebarParagraphs extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatcher;

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatcher
   */
  public function setRouteMatcher(RouteMatchInterface $routeMatcher): void {
    $this->routeMatcher = $routeMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setRouteMatcher($container->get('current_route_match'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['label_display' => 'hidden'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];
    // Try to get the node from the route.
    $node = $this->routeMatcher->getParameter('node');
    if ($this->routeMatcher->getRouteName() === 'entity.node.preview') {
      $node = $this->routeMatcher->getParameter('node_preview');
    }
    if ($node && $node instanceof NodeInterface) {
      $field_rendered_array = $node->get('field_sidebar_right_paragraphs')
        ->view('full');
      if (!$node->isPublished()) {
        $build['sidebar_paragraphs']['#markup'] = render($field_rendered_array);
      }
      else {
        $build['sidebar_paragraphs'] = $field_rendered_array;
      }
      $build['#cache'] = [
        'tags' => $this->getCacheTags(),
        'contexts' => $this->getCacheContexts(),
        'keys' => [
          'entity_view',
          'node',
          $node->id(),
          'default',
        ],
      ];
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    $cache_tags = parent::getCacheTags();
    $node = $this->routeMatcher->getParameter('node');
    if ($node && $node instanceof NodeInterface) {
      $cache_tags[] = 'node:' . $node->id();
      $cache_tags[] = 'node_view';
    }
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path', 'user.permissions']);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $node = $this->routeMatcher->getParameter('node');
    if ($this->routeMatcher->getRouteName() === 'entity.node.preview') {
      $node = $this->routeMatcher->getParameter('node_preview');
    }
    $condition = $node && $node instanceof NodeInterface &&
      $node->hasField('field_sidebar_right_paragraphs') &&
      !$node->get('field_sidebar_right_paragraphs')->isEmpty();
    return AccessResult::allowedIf($condition);
  }

}
