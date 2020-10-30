<?php

declare(strict_types=1);

namespace Drupal\node_action\Plugin\Action;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node_action\AccessChecker\ChangeModerationStateAction as AccessChecker;
use Drupal\node_action\Redirector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChangeAuthorAction.
 *
 * @Action(
 *   id = "node_action:entity:author:node",
 *   action_label = @Translation("Change the author"),
 *   type = "node"
 * )
 */
final class ChangeAuthorAction extends EntityActionBase {

  /**
   * Access checker.
   *
   * @var \Drupal\node_action\AccessChecker\ChangeModerationStateAction
   */
  private $accessChecker;

  /**
   * Redirector.
   *
   * @var \Drupal\node_action\Redirector
   */
  private $redirector;

  /**
   * ChangeAuthorAction constructor.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, AccessChecker $accessChecker, Redirector $redirector, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager);
    $this->accessChecker = $accessChecker;
    $this->redirector = $redirector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('node_action.access_checker.change_moderation_state_action'),
      $container->get('node_action.redirector'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    return $this->redirector->computeRedirectResponseByEntities($entities, 'node_action.author_form');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if (!$this->accessChecker->canAccess($object) instanceof AccessResultForbidden) {
      return TRUE;
    }

    return FALSE;
  }

}
