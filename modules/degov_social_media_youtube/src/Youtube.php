<?php

namespace Drupal\degov_social_media_youtube;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\degov_social_media_settings\SocialMediaAssetsTrait;
use Madcoda\Youtube\Youtube as MadcodaYoutube;

/**
 * Class Youtube.
 *
 * @package Drupal\degov_social_media_youtube
 */
class Youtube extends MadcodaYoutube {

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
   * Youtube constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   *
   * @throws \Exception
   */
  public function __construct(ConfigFactoryInterface $config, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger) {
    $this->devMode = $config->get('degov_devel.settings')->get('dev_mode');

    if ($this->devMode) {
      return;
    }

    $this->logger = $logger->get('degov_social_media_youtube');
    $this->messenger = $messenger;
    $params = [
      'key' => $config->get('degov_social_media_youtube.settings')->get('api_key')
    ];
    try {
      parent::__construct($params);
    }
    catch (\Exception $e) {
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getChannelByName($username, $optionalParams = FALSE) {
    if ($this->devMode) {
      return FALSE;
    }
    return parent::getChannelByName($username, $optionalParams);
  }

  /**
   * {@inheritDoc}
   */
  public function searchChannelVideos($q, $channelId, $maxResults = 10, $order = NULL) {
    if ($this->devMode) {
      return self::getDataFromFile('degov_social_media_youtube', 'videos.txt');
    }
    return parent::searchChannelVideos($q, $channelId, $maxResults, $order);
  }

  /**
   * {@inheritDoc}
   */
  public function getVideoInfo($vId) {
    if ($this->devMode) {
      return self::getDataFromFile('degov_social_media_youtube', "video_info_{$vId}.txt");
    }
    return parent::getVideoInfo($vId);
  }

}
