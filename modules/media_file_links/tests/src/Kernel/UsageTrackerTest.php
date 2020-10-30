<?php

namespace Drupal\Tests\media_file_links\Kernel;

use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Class UsageTrackerTest.
 *
 * @package Drupal\Tests\media_file_links\Kernel
 */
class UsageTrackerTest extends MediaFileLinksTestBase {

  /**
   * Usage tracker.
   *
   * @var \Drupal\media_file_links\Service\MediaFileLinkUsageTracker
   */
  private $usageTracker;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->usageTracker = $this->container->get('media_file_links.usage_tracker');
  }

  /**
   * Test usage record is created from menu link.
   */
  public function testUsageRecordIsCreatedFromMenuLink() {
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertSame([], $usages);

    $this->createMenuItem();

    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId], FALSE);
    self::assertSame([
      0 => [
        'referencing_entity_id'       => '1',
        'referencing_entity_type'     => 'menu_link_content',
        'referencing_entity_field'    => 'link',
        'referencing_entity_langcode' => 'en',
        'media_entity_id'             => $this->supportedMediaId,
      ],
    ], $usages);
  }

  /**
   * Test usage records are updated on content change.
   */
  public function testUsageRecordsAreUpdatedOnContentChange() {
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertSame([], $usages);

    $menuItemId = $this->createMenuItem();
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertCount(1, $usages);

    $menuItem = MenuLinkContent::load($menuItemId);
    $menuItem->set('link', ['uri' => 'internal:/']);
    $menuItem->save();

    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertSame([], $usages);
  }

  /**
   * Test usage records are deleted on menu content delete.
   */
  public function testUsageRecordsAreDeletedOnMenuContentDelete() {
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertSame([], $usages);

    $menuItemId = $this->createMenuItem();
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertCount(1, $usages);

    $this->deleteMenuItem($menuItemId);
    $usages = $this->usageTracker->getUsagesByMediaIds([$this->supportedMediaId]);
    self::assertSame([], $usages);
  }

  /**
   * Create menu item.
   */
  private function createMenuItem() {
    $menuItem = MenuLinkContent::create([
      'title'     => 'Fna',
      'link'      => [
        'uri' => 'internal:<media/file/' . $this->supportedMediaId . '>',
      ],
      'menu_name' => 'main',
      'expanded'  => TRUE,
    ]);
    $menuItem->save();
    return $menuItem->id();
  }

  /**
   * Delete menu item.
   */
  private function deleteMenuItem(int $itemId) {
    $menuItem = MenuLinkContent::load($itemId);
    $menuItem->delete();
  }

}
