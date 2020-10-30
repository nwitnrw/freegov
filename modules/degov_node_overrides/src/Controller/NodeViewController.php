<?php

namespace Drupal\degov_node_overrides\Controller;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\node\Controller\NodeViewController as BaseNodeViewController;

/**
 * Class MediaViewController.
 *
 * @package Drupal\degov_node_overrides\Controller
 */
class NodeViewController extends BaseNodeViewController {

  /**
   * Pre-render callback to build the page title.
   *
   * @param array $page
   *   A page render array.
   *
   * @return array
   *   The changed page render array.
   */
  public function buildTitle(array $page) {
    $entity_type = $page['#entity_type'];
    $entity = $page['#' . $entity_type];
    // If the entity's label is rendered using a field formatter, set the
    // rendered title field formatter as the page title instead of the default
    // plain text title. This allows attributes set on the field to propagate
    // correctly (e.g. RDFa, in-place editing).
    if ($entity instanceof FieldableEntityInterface) {
      $label_field = $entity->getEntityType()->getKey('label');
      if (isset($page[$label_field])) {
        $page['#title'] = $this->renderer->render($page[$label_field]);
      }
      if ($entity->bundle() == ('normal_page' || 'press' || 'events') && $entity->hasField('field_teaser_title') && !$entity->get('field_teaser_title')->isEmpty() && isset($page['field_teaser_title'])) {
        $page['#title'] = $this->renderer->render($page['field_teaser_title']);
      }
    }
    return $page;
  }

}
