<?php

declare(strict_types=1);

namespace Drupal\degov\Behat\Context;

use Behat\Mink\Exception\ResponseTextException;
use Drupal\block\Entity\Block;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class BlockContext.
 */
class BlockContext extends RawDrupalContext {

  /**
   * Configure and place simplenews signup.
   *
   * @Then /^I configure and place the Simplenews signup block$/
   */
  public function configureAndPlaceSimplenewsSignup() {
    /** @var \Drupal\Core\Config\ConfigFactory $configFactory $configFactory */
    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('degov_social_media_instagram.settings');
    $config->set('user', 'ig_bundestag')->save();

    $block = Block::create([
      'id' => 'simplenewsabonnement',
      'uniqueId' => '1234',
      'theme' => 'degov_theme',
      'weight' => 0,
      'status' => TRUE,
      'region' => 'content',
      'plugin' => 'simplenews_subscription_block',
      'settings' => [
        'id'            => 'simplenews_subscription_block',
        'label'         => 'Simplenews Abonnement',
        'provider'      => 'simplenews',
        'label_display' => 'visible',
        'newsletters'   => ['default' => 'default'],
        'unique_id' => 'test1234',
      ],
      'visibility' => [],
    ]);
    $block->save();
  }

  /**
   * Configure and place main menu.
   *
   * @Then /^I configure and place the main menu block$/
   */
  public function configureAndPlaceMainMenu() {
    /** @var \Drupal\Core\Config\ConfigFactory $configFactory $configFactory */
    $block = Block::load('main_menu');

    if ($block !== NULL && $block instanceof Block) {
      $block->delete();
    }

    $block = Block::create([
      'id' => 'main_menu',
      'uniqueId' => '2345',
      'theme' => 'degov_theme',
      'weight' => 0,
      'status' => TRUE,
      'region' => 'navigation',
      'plugin' => 'system_menu_block:main',
      'settings' => [
        'id'            => 'system_menu_block:main',
        'label'         => 'Main menu',
        'provider'      => 'system',
        'label_display' => FALSE,
        'level'         => 1,
        'depth'         => 0,
      ],
      'visibility' => [],
    ]);
    $block->save();
  }

  /**
   * Delete blocks.
   *
   * @Then /^I delete any existing blocks with comma separated ids "([^"]*)"$/
   */
  public function deleteBlocks(string $blockIds) {
    $blockIdsArray = explode(',', $blockIds);

    if (!is_array($blockIdsArray) || count($blockIdsArray) < 1) {

      throw new ResponseTextException(
        'Could not determine any block ids. You must pass a comma separated list with machine names.',
        $this->getSession()
      );

    }

    foreach ($blockIdsArray as $blockId) {
      $block = Block::load(trim($blockId));
      if ($block instanceof Block) {
        $block->delete();
      }
    }

  }

}
