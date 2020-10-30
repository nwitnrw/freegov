<?php

declare(strict_types=1);

namespace Drupal\Tests\node_action\Unit;

use Drupal\content_moderation\Plugin\Field\ModerationStateFieldItemList;
use Drupal\Core\Messenger\Messenger;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\node_action\AccessChecker\PublishedStateChange;
use Drupal\node_action\StringTranslationAdapter;
use Drupal\node_action\UserInteractionFacade;
use Drupal\Tests\UnitTestCase;

/**
 * Class PublishedStateChangeTest.
 */
class PublishedStateChangeTest extends UnitTestCase {

  /**
   * Test disallowed due to no moderation state.
   */
  public function testDisallowedDueNoModerationState(): void {
    $userInteractionFacade = $this->getMockBuilder(UserInteractionFacade::class)
      ->disableOriginalConstructor()
      ->getMock();

    $messenger = $this->getMockBuilder(Messenger::class)
      ->disableOriginalConstructor()
      ->setMethods(['addMessage'])
      ->getMock();

    $messenger->expects($this->exactly(1))
      ->method('addMessage')
      ->willReturn(NULL);

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stringTranslationAdapter = $this->getMockBuilder(StringTranslationAdapter::class)
      ->disableOriginalConstructor()
      ->setMethods(['t'])
      ->getMock();

    $stringTranslationAdapter->expects($this->exactly(1))
      ->method('t')
      ->willReturn('Some string');

    $userInteractionFacade->messenger = $messenger;
    $userInteractionFacade->stringTranslationAdapter = $stringTranslationAdapter;

    $publishedStateChange = new PublishedStateChange($userInteractionFacade);

    $moderationStateFieldItemList = $this->getMockBuilder(ModerationStateFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();

    $moderationStateFieldItemList->expects($this->once())
      ->method('count')
      ->willReturn(0);

    $node->expects($this->once())
      ->method('get')
      ->with('moderation_state')
      ->willReturn($moderationStateFieldItemList);

    self::assertFalse($publishedStateChange->isAllowed($node));
  }

  /**
   * Test has moderation state.
   */
  public function testHasModerationState(): void {
    [$userInteractionFacade, $messenger, $stringTranslationAdapter, $moderationStateFieldItemList, $node] = $this->mockClasses();

    $messenger->expects($this->exactly(0))
      ->method('addMessage')
      ->willReturn(NULL);

    $stringTranslationAdapter->expects($this->exactly(0))
      ->method('t')
      ->willReturn('Some string');

    $userInteractionFacade->messenger = $messenger;
    $userInteractionFacade->stringTranslationAdapter = $stringTranslationAdapter;

    $publishedStateChange = new PublishedStateChange($userInteractionFacade);

    $userInteractionFacade->messenger = $messenger;

    $moderationStateFieldItemList->expects($this->once())
      ->method('count')
      ->willReturn(1);

    $valueStub = $this->getMockBuilder(\stdClass::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMock();

    $valueStub->expects($this->once())
      ->method('getValue')
      ->willReturn([
        'value' => 'draft',
      ]);

    $moderationStateFieldItemList->expects($this->once())
      ->method('first')
      ->willReturn($valueStub);

    $node->expects($this->exactly(2))
      ->method('get')
      ->with('moderation_state')
      ->willReturn($moderationStateFieldItemList);

    self::assertTrue($publishedStateChange->isAllowed($node));
  }

  /**
   * Mock classes.
   */
  private function mockClasses(): array {
    $userInteractionFacade = $this->getMockBuilder(UserInteractionFacade::class)
      ->disableOriginalConstructor()
      ->getMock();

    $messenger = $this->getMockBuilder(Messenger::class)
      ->disableOriginalConstructor()
      ->setMethods(['addMessage'])
      ->getMock();

    $stringTranslationAdapter = $this->getMockBuilder(StringTranslationAdapter::class)
      ->disableOriginalConstructor()
      ->setMethods(['t'])
      ->getMock();

    $moderationStateFieldItemList = $this->getMockBuilder(ModerationStateFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();

    $node = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();

    return [
      $userInteractionFacade,
      $messenger,
      $stringTranslationAdapter,
      $moderationStateFieldItemList,
      $node,
    ];
  }

}
