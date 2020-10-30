<?php

declare(strict_types=1);

namespace Drupal\node_action\AccessChecker;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\node_action\StringTranslationAdapter;
use Drupal\permissions_by_term\Service\AccessCheck;

/**
 * Class ActionAbstract.
 */
class ActionAbstract {

  use MessagesTrait;

  /**
   * Access check.
   *
   * @var \Drupal\permissions_by_term\Service\AccessCheck
   */
  private $accessCheck;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * Permission.
   *
   * @var string
   */
  private $permission;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * Action name.
   *
   * @var string
   */
  private static $actionName;

  /**
   * String translation adapter.
   *
   * @var \Drupal\node_action\StringTranslationAdapter
   */
  private $stringTranslationAdapter;

  /**
   * ActionAbstract constructor.
   */
  public function __construct(
    AccountProxyInterface $currentUser,
    string $permission,
    MessengerInterface $messenger,
    string $actionName,
    StringTranslationAdapter $stringTranslationAdapter
  ) {
    $this->currentUser = $currentUser;
    $this->permission = $permission;
    $this->messenger = $messenger;
    self::$actionName = $actionName;
    $this->stringTranslationAdapter = $stringTranslationAdapter;
  }

  /**
   * Has permission.
   */
  public function hasPermission(): bool {
    if (!$this->currentUser->hasPermission($this->permission)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Has permissions by term permission.
   */
  public function hasPermissionsByTermPermission(NodeInterface $node): bool {
    if ($this->accessCheck instanceof AccessCheck) {
      return $this->accessCheck->canUserAccessByNodeId($node->id());
    }
    return TRUE;
  }

  /**
   * Can access.
   */
  public function canAccess(EntityInterface $entity) {
    if (!$entity instanceof NodeInterface) {
      return AccessResult::neutral();
    }

    if (!$this->hasPermission()) {
      $this->messenger->addMessage($this->stringTranslationAdapter->t(self::$messageNoRolePermission, ['@actionName' => self::$actionName, '@nodeTitle' => $entity->getTitle()]), 'warning', FALSE);
      return AccessResult::forbidden();
    }

    if (!$this->hasPermissionsByTermPermission($entity)) {
      $this->messenger->addMessage($this->stringTranslationAdapter->t(self::$messageNoPermissionByTermPermission, ['@actionName' => self::$actionName, '@nodeTitle' => $entity->getTitle()]), 'warning', FALSE);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * @param \Drupal\permissions_by_term\Service\AccessCheck $accessCheck
   */
  public function setAccessCheck(AccessCheck $accessCheck): void {
    $this->accessCheck = $accessCheck;
  }

}
