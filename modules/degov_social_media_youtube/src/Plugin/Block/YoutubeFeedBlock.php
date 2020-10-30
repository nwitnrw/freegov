<?php

namespace Drupal\degov_social_media_youtube\Plugin\Block;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\degov_social_media_youtube\Youtube;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'YoutubeFeedBlock' block.
 *
 * @Block(
 *  id = "degov_social_media_youtube",
 *  admin_label = @Translation("Youtube Feed Block"),
 * )
 */
final class YoutubeFeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Definition of LoggerChannel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Definition of ImmutableConfig.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $degovSocialMediaYoutubeConfig;

  /**
   * Definition of Youtube.
   *
   * @var \Drupal\degov_social_media_youtube\Youtube
   */
  protected $youTube;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface*/
  private $moduleHandler;

  /**
   * YoutubeFeedBlock constructor.
   * phpcs:disable
   *
   * @param array $configuration
   *   Block plugin config.
   * @param string $plugin_id
   *   Block plugin plugin_id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The config service.
   * @param \Drupal\degov_social_media_youtube\Youtube $youtube
   *   The Youtube service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * phpcs:enable
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerChannelInterface $logger,
    ImmutableConfig $config,
    Youtube $youtube,
    ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->degovSocialMediaYoutubeConfig = $config;
    $this->youTube = $youtube;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('degov_social_media_youtube'),
      $container->get('config.factory')->get('degov_social_media_youtube.settings'),
      $container->get('degov_social_media_youtube.youtube'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function build() {
    $build = [];

    $channelName = $this->degovSocialMediaYoutubeConfig->get('channel');
    $numberOfVideos = $this->degovSocialMediaYoutubeConfig->get('number_of_videos');

    $channelID = NULL;

    try {
      if (!empty($this->youTube->getChannelByName($channelName))) {
        $channelID = $this->youTube->getChannelByName($channelName)->id;
      }
      else {
        // If no channel was found by that name then take
        // the channel name as ID.
        $channelID = $channelName;
      }
      $videos = $this->youTube->searchChannelVideos('', $channelID, $numberOfVideos, 'date');
      foreach ($videos as $video) {
        $info = $this->youTube->getVideoInfo($video->id->videoId);
        $build[] = [
          '#theme' => 'degov_social_media_youtube',
          '#title' => $video->snippet->title,
          '#description' => Unicode::truncate($video->snippet->description, 123, FALSE, TRUE),
          '#likes' => $info->statistics->likeCount,
          '#dislikes' => $info->statistics->dislikeCount,
          '#views' => $info->statistics->viewCount,
          '#comments' => (property_exists($info->statistics, 'commentCount')) ? $info->statistics->commentCount : NULL,
          '#videoID' => $video->id->videoId,
          '#thumbnail' => $video->snippet->thumbnails->high->url,
          '#link_display' => Unicode::truncate("https://youtube.com/watch?q=" . $video->id->videoId, 32, FALSE, TRUE),
          '#cache' => [
            'max-age' => (60 * 60),
          ],
        ];
      }
    }
    catch (\Exception $exception) {
      $this->logger->warning($exception->getMessage());
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:degov_devel.settings';
    $cache_tags[] = 'config:degov_social_media_youtube.settings';
    return $cache_tags;
  }

  /**
   * @{inheritDoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $this->moduleHandler->loadInclude('degov_social_media_youtube', 'install');
    $result = $this->moduleHandler->invoke('degov_social_media_youtube', 'requirements', ['runtime']);
    return AccessResult::allowedIf(is_array($result) && empty($result));
  }

}
