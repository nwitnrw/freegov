<?php

declare(strict_types=1);

namespace Drupal\node_action\Plugin\Action;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node_action\AccessChecker\UnpublishAction as UnpublishActionChecker;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unpublishes an node.
 *
 * @Action(
 *   id = "node_action:entity:unpublish_action:node",
 *   action_label = @Translation("Publish"),
 * )
 */
final class UnpublishAction extends EntityActionBase {

  /**
   * Unpublish action checker.
   *
   * @var \Drupal\node_action\AccessChecker\UnpublishAction
   */
  private $unpublishActionChecker;

  /**
   * UnpublishAction constructor.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, UnpublishActionChecker $unpublishActionChecker) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->unpublishActionChecker = $unpublishActionChecker;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('node_action.access_checker.unpublish_action')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
    /**
     * @var \Drupal\node\Entity\Node $entity
     */
    $entity->set('moderation_state', 'archived')->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if (!$this->unpublishActionChecker->canAccess($object) instanceof AccessResultForbidden) {
      return TRUE;
    }

    return FALSE;
  }

}
