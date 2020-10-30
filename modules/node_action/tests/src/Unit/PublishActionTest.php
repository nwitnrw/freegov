<?php

declare(strict_types=1);

namespace Drupal\Tests\node_action\Unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node_action\AccessChecker\PublishAction;
use Drupal\node_action\StringTranslationAdapter;
use Drupal\permissions_by_term\Service\AccessCheck;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Class PublishActionTest.
 */
class PublishActionTest extends UnitTestCase {

  /**
   * Test allowed access.
   */
  public function testAllowedAccess(): void {
    $currentUser = $this->prophesize(AccountProxyInterface::class);
    $currentUser->hasPermission(Argument::type('string'))->willReturn(TRUE);

    $messenger = $this->prophesize(MessengerInterface::class);

    $stringTranslationAdapter = $this->prophesize(StringTranslationAdapter::class);
    $stringTranslationAdapter->t(Argument::type('string'));

    $publishAction = new PublishAction($currentUser->reveal(), $messenger->reveal(), $stringTranslationAdapter->reveal());

    $entity = $this->prophesize(Node::class);
    $entity->id()->willReturn(1);

    self::assertEquals(AccessResult::allowed(), $publishAction->canAccess($entity->reveal()));
  }

  /**
   * Test disallowed access by no permission.
   */
  public function testDisallowedAccessByNoPermission(): void {
    $currentUser = $this->prophesize(AccountProxyInterface::class);
    $currentUser->hasPermission(Argument::type('string'))->willReturn(FALSE);

    $messenger = $this->prophesize(MessengerInterface::class);
    $messenger->addMessage(Argument::any(), Argument::type('string'), Argument::any())->shouldBeCalled();

    $stringTranslationAdapter = $this->prophesize(StringTranslationAdapter::class);
    $stringTranslationAdapter->t(Argument::type('string'));

    $publishAction = new PublishAction($currentUser->reveal(), $messenger->reveal(), $stringTranslationAdapter->reveal());

    $entity = $this->prophesize(Node::class);
    $entity->id()->willReturn(1);
    $entity->getTitle()->willReturn('Some title');

    self::assertEquals(AccessResult::forbidden(), $publishAction->canAccess($entity->reveal()));
  }

  /**
   * Test disallowed access by no permissions by term permission.
   */
  public function testDisallowedAccessByNoPermissionsByTermPermission(): void {
    $accessCheck = $this->prophesize(AccessCheck::class);
    $accessCheck->canUserAccessByNodeId(Argument::type('int'))->willReturn(FALSE);

    $currentUser = $this->prophesize(AccountProxyInterface::class);
    $currentUser->hasPermission(Argument::type('string'))->willReturn(TRUE);

    $messenger = $this->prophesize(MessengerInterface::class);
    $messenger->addMessage(Argument::any(), Argument::type('string'), Argument::any())->shouldBeCalled();

    $stringTranslationAdapter = $this->prophesize(StringTranslationAdapter::class);
    $stringTranslationAdapter->t(Argument::type('string'));

    $publishAction = new PublishAction($currentUser->reveal(), $messenger->reveal(), $stringTranslationAdapter->reveal());
    $publishAction->setAccessCheck($accessCheck->reveal());

    $entity = $this->prophesize(Node::class);
    $entity->id()->willReturn(1);
    $entity->getTitle()->willReturn('Some title');

    self::assertEquals(AccessResult::forbidden(), $publishAction->canAccess($entity->reveal()));
  }

  /**
   * Test handling if entity no node.
   */
  public function testHandlingIfEntityNoNode(): void {
    $currentUser = $this->prophesize(AccountProxyInterface::class);

    $messenger = $this->prophesize(MessengerInterface::class);

    $stringTranslationAdapter = $this->prophesize(StringTranslationAdapter::class);

    $publishAction = new PublishAction($currentUser->reveal(), $messenger->reveal(), $stringTranslationAdapter->reveal());

    $entity = $this->prophesize(Media::class);

    self::assertEquals(AccessResult::neutral(), $publishAction->canAccess($entity->reveal()));
  }

}
