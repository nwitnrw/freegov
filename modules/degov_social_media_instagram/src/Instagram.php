<?php

namespace Drupal\degov_social_media_instagram;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\degov_social_media_settings\SocialMediaAssetsTrait;
use InstagramScraper\Instagram as InstagramScraper;

/**
 * Class Instagram.
 *
 * @package Drupal\degov_social_media_instagram
 */
class Instagram extends InstagramScraper {

  use SocialMediaAssetsTrait;

  /**
   * Development mode status.
   *
   * @var bool
   */
  protected $devMode;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Definition of LoggerChannel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Instagram constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   */
  public function __construct(ConfigFactoryInterface $config, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger) {
    $this->devMode = $config->get('degov_devel.settings')->get('dev_mode');
    $this->logger = $logger->get('degov_social_media_instagram');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritDoc}
   */
  public function getMedias($username, $count = 20, $maxId = '') {
    if ($this->devMode) {
      return self::getDataFromFile('degov_social_media_instagram', 'medias.txt');
    }
    return parent::getMedias($username, $count, $maxId);
  }

  /**
   * {@inheritDoc}
   */
  public function getAccount($username) {
    if ($this->devMode) {
      return self::getDataFromFile('degov_social_media_instagram', 'account.txt');
    }
    return parent::getAccount($username);
  }

}
