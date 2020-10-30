<?php

declare(strict_types=1);

namespace Drupal\Tests\node_action\Unit;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\node_action\Redirector;
use Drupal\node_action\RedirectResponseFactory;
use Drupal\node_action\UrlFactory;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectorTest.
 */
class RedirectorTest extends UnitTestCase {

  /**
   * Test disallowed redirect.
   */
  public function testDisallowedRedirect(): void {
    $messenger = $this->prophesize(MessengerInterface::class);
    $messenger->deleteByType(Argument::type('string'))->shouldBeCalled();

    $redirectResponseFactory = $this->prophesize(RedirectResponseFactory::class);
    $urlFactory = $this->prophesize(UrlFactory::class);

    $redirector = new Redirector($messenger->reveal(), $redirectResponseFactory->reveal(), $urlFactory->reveal());
    self::assertNull($redirector->computeRedirectResponseByEntities([], 'some.test.route'));
  }

  /**
   * Test allowed redirect.
   */
  public function testAllowedRedirect(): void {
    $messenger = $this->prophesize(MessengerInterface::class);

    $redirectResponse = $this->prophesize(RedirectResponse::class);

    $redirectResponseReturnResult = $this->prophesize(RedirectResponse::class);
    $redirectResponse->send()->willReturn($redirectResponseReturnResult->reveal());

    $redirectResponseFactory = $this->prophesize(RedirectResponseFactory::class);
    $redirectResponseFactory->create(Argument::type('string'))->willReturn($redirectResponse->reveal());

    $urlFactory = $this->prophesize(UrlFactory::class);

    $url = $this->prophesize(Url::class);
    $url->toString()->willReturn('http://some-url.com');

    $urlFactory->create(Argument::type('string'), Argument::type('array'))->willReturn($url->reveal());

    $redirector = new Redirector($messenger->reveal(), $redirectResponseFactory->reveal(), $urlFactory->reveal());

    $entity1 = $this->prophesize(NodeInterface::class);
    $entity1->id()->willReturn(1);
    $entity1->getTitle()->willReturn('Test title');

    $entity2 = $this->prophesize(NodeInterface::class);
    $entity2->id()->willReturn(2);
    $entity2->getTitle()->willReturn('Test title');

    self::assertInstanceOf(RedirectResponse::class, $redirector->computeRedirectResponseByEntities([$entity1->reveal(), $entity2->reveal()], 'some.test.route'));
  }

}
