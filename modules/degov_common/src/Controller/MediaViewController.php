<?php

namespace Drupal\degov_common\Controller;

use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Class MediaViewController.
 *
 * @package Drupal\degov_common\Controller
 */
class MediaViewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function buildTitle(array $page): array {
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
      if (isset($page['field_title']) && $entity->hasField('field_title') && !$entity->get('field_title')->isEmpty()) {
        $page['#title'] = $this->renderer->render($page['field_title']);
      }
    }
    return $page;
  }

  // @codingStandardsIgnoreStart
  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $media, $view_mode = 'full'): array {
    return parent::view($media, $view_mode);
  }
  // @codingStandardsIgnoreEnd

}
