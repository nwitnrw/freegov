<?php

namespace Drupal\degov_common;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\media\MediaInterface;
use function GuzzleHttp\Psr7\str;
use DateInterval;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\video_embed_field\ProviderManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use JamesHeinrich\GetID3\GetID3;
use Symfony\Component\Serializer\Encoder\JsonDecode;

/**
 * Class VideoUtils.
 *
 * @package Drupal\degov_video
 */
class VideoUtils {

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Video provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManager
   */
  protected $videoProviderManager;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ClientInterface $http_client, ProviderManager $video_provider_manager, FileSystem $file_system, LoggerChannelFactoryInterface $logger, MessengerInterface $messenger, ConfigFactoryInterface $configFactory) {
    $this->httpClient = $http_client;
    $this->videoProviderManager = $video_provider_manager;
    $this->fileSystem = $file_system;
    $this->logger = $logger->get('degov_media_manager');
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
  }

  /**
   * Return the duration of Youtube video in seconds.
   *
   * @param \Drupal\media\MediaInterface|mixed $media
   *   Media.
   */
  public function getVideoDuration($media): int {
    $duration = 0;
    if ($media instanceof MediaInterface) {
      if ($media->bundle() === 'video') {
        $embed_field = $media->get('field_media_video_embed_field')->getValue();
        $url = $embed_field[0]['value'];
        /** @var \Drupal\video_embed_field\ProviderPluginBase $videoProvider */
        $videoProvider = $this->videoProviderManager->loadProviderFromInput($url);
        $provider = $videoProvider->getPluginId();
        $videoId = $videoProvider::getIdFromInput($url);
        $method = 'get' . ucfirst($provider) . 'Duration';
        if (method_exists($this, $method)) {
          $duration = $this->$method($videoId, $url);
        }
      }
      if ($media->bundle() === 'video_upload') {
        $file_uri = '';
        if (!$media->get('field_video_upload_mp4')->isEmpty()) {
          $file_uri = $media->get('field_video_upload_mp4')->entity->getFileUri();
        }
        elseif (!$media->get('field_video_upload_webm')->isEmpty()) {
          $file_uri = $media->get('field_video_upload_webm')->entity->getFileUri();
        }
        elseif (!$media->get('field_video_upload_ogg')->isEmpty()) {
          $file_uri = $media->get('field_video_upload_ogg')->entity->getFileUri();
        }
        if ($file_uri != '') {
          $file_uri = $this->fileSystem->realpath($file_uri);
        }
        $getId3 = new GetID3();
        $getId3->option_md5_data = TRUE;
        $getId3->option_md5_data_source = TRUE;
        $getId3->encoding = 'UTF-8';
        $id3Info = $getId3
          ->analyze($file_uri);
        if (isset($id3Info['error'])) {
          $this->messenger->addStatus(t('There was a problem getting the video duration. Please check site logs.'));
          $this->logger->error('Error at reading audio properties from @uri with GetId3: @error.', ['uri' => $file_uri, 'error' => $id3Info['error']]);
        }
        if (!empty($id3Info['playtime_seconds'])) {
          $duration = (int) ceil($id3Info['playtime_seconds']);
        }
      }
      if ($media->bundle() === 'audio') {
        $file_uri = '';
        if (!$media->get('field_audio_mp3')->isEmpty()) {
          $file_uri = $media->get('field_audio_mp3')->entity->getFileUri();
        }
        elseif (!$media->get('field_audio_ogg')->isEmpty()) {
          $file_uri = $media->get('field_audio_ogg')->entity->getFileUri();
        }
        if ($file_uri != '') {
          $file_uri = $this->fileSystem->realpath($file_uri);
        }
        $getId3 = new GetID3();
        $getId3->option_md5_data = TRUE;
        $getId3->option_md5_data_source = TRUE;
        $getId3->encoding = 'UTF-8';
        $id3Info = $getId3
          ->analyze($file_uri);
        if (isset($id3Info['error'])) {
          $this->messenger->addStatus(t('There was a problem getting the audio duration. Please check site logs.'));
          $this->logger->error('Error at reading audio properties from @uri with GetId3: @error.', ['uri' => $file_uri, 'error' => $id3Info['error']]);
        }
        if (!empty($id3Info['playtime_seconds'])) {
          $duration = (int) ceil($id3Info['playtime_seconds']);
        }
      }
    }
    return $duration;
  }

  /**
   * Return the duration of Youtube video in seconds.
   *
   * @return int
   *   Video duration.
   */
  private function getYoutubeDuration(string $videoId, string $url = ''): int {
    $config = $this->configFactory->get('degov_common.default_settings');
    $key = $config->get('youtube_apikey');
    if ($key === '') {
      return 0;
    }
    $params = [
      'part' => 'contentDetails',
      'id' => $videoId,
      'key' => $key,
      'time' => time(),
    ];
    $query_url = 'https://www.googleapis.com/youtube/v3/videos?' . http_build_query($params);
    $response = NULL;
    try {
      $response = $this->httpClient->request('GET', $query_url);
    }
    catch (ClientException $e) {
      $this->messenger->addStatus(t('There was a problem getting the video duration. Please check site logs.'));
      $this->logger->error("Youtube access failure with status: @trace", ['@trace' => str($e->getResponse())]);
      return 0;
    }

    if ($response->getStatusCode() == 200) {
      $result = new JsonDecode(TRUE);
      $details = $result->decode($response->getBody(), 'json');
      if (!empty($details['items'][0]['contentDetails'])) {
        $vinfo = $details['items'][0]['contentDetails'];
        $interval = new DateInterval($vinfo['duration']);
        return $interval->h * 3600 + $interval->i * 60 + $interval->s;
      }
    }
    return 0;
  }

  /**
   * Get duration of Vimeo video.
   *
   * @return int
   *   Video duration.
   */
  private function getVimeoDuration(string $videoId, string $url): int {
    $query_url = 'https://vimeo.com/api/oembed.json?url=' . $url;

    $response = NULL;
    try {
      $response = $this->httpClient->request('GET', $query_url);
    }
    catch (ClientException $e) {
      $this->messenger->addStatus(t('There was a problem getting the video duration. Please check site logs.'));
      $this->logger->error("Vimeo access failure with status: @trace", ['@trace' => str($e->getResponse())]);
      return 0;
    }

    if ($response) {
      $result = new JsonDecode(TRUE);
      $details = $result->decode($response->getBody(), 'json');
      if (!empty($details['duration'])) {
        return $details['duration'];
      }
    }
    return 0;
  }

  /**
   * Get file info.
   */
  public function getFileInfo(string $file_path): array {
    $getId3 = new GetID3();
    $getId3->option_md5_data = TRUE;
    $getId3->option_md5_data_source = TRUE;
    $getId3->encoding = 'UTF-8';
    return $getId3
      ->analyze($file_path);
  }

}
