<?php

namespace Drupal\degov_social_media_instagram\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\degov_social_media_instagram\Instagram;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'InstagramFeedBlock' block.
 *
 * @Block(
 *  id = "degov_social_media_instagram",
 *  admin_label = @Translation("Instagram Feed Block"),
 * )
 */
class InstagramFeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Number of characters for tweets caption.
   */
  const NUMBER_OF_CHARACTERS = 100;

  /**
   * Definition of ImmutableConfig.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $degovSocialMediaInstagramConfig;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Definition of Instagram.
   *
   * @var \Drupal\degov_social_media_instagram\Instagram
   */
  protected $instagram;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $modulehandler;

  /**
   * InstagramFeedBlock constructor.
   * phpcs:disable
   *
   * @param array $configuration
   *   Block plugin config.
   * @param string $plugin_id
   *   Block plugin plugin_id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The config service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\degov_social_media_instagram\Instagram $instagram
   *   The Instagram service.
   * phpcs:enable
   */
  final public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ImmutableConfig $config,
    DateFormatterInterface $date_formatter,
    Instagram $instagram,
  ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->degovSocialMediaInstagramConfig = $config;
    $this->dateFormatter = $date_formatter;
    $this->instagram = $instagram;
    $this->modulehandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')->get('degov_social_media_instagram.settings'),
      $container->get('date.formatter'),
      $container->get('degov_social_media_instagram.instagram'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InstagramScraper\Exception\InstagramException
   * @throws \InstagramScraper\Exception\InstagramNotFoundException
   */
  public function build() {
    $build = [];

    $user = $this->degovSocialMediaInstagramConfig->get('user');
    $max = $this->degovSocialMediaInstagramConfig->get('number_of_posts');
    $maxLength = self::NUMBER_OF_CHARACTERS;
    if (is_numeric($this->degovSocialMediaInstagramConfig->get('number_of_characters'))) {
      $maxLength = $this->degovSocialMediaInstagramConfig->get('number_of_characters');
    }

    if (is_numeric($max)) {
      $max = intval($max);
    }
    if ($medias = $this->instagram->getMedias($user, $max)) {
      /** @var \InstagramScraper\Model\Media $media */
      foreach ($medias as $media) {
        $build[] = [
          '#theme' => 'degov_social_media_instagram',
          '#imageUrl' => $media->getImageThumbnailUrl(),
          '#instagramUser' => $this->instagram->getAccount($user)
            ->getFullName(),
          '#link' => $media->getLink(),
          '#link_display' => $this->shortDescription($media->getLink(), 32, '...'),
          '#type' => $media->getType(),
          '#caption' => $this->shortDescription($media->getCaption(), $maxLength, "..."),
          '#views' => $media->getVideoViews(),
          '#likes' => $media->getLikesCount(),
          '#comments' => $media->getCommentsCount(),
          '#date' => $this->dateFormatter
            ->formatTimeDiffSince($media->getCreatedTime()),
          '#cache' => [
            'max-age' => (60 * 5),
          ],
        ];
      }
    }

    return $build;
  }

  /**
   * Returns the short description.
   *
   * @param string $string
   *   Description text.
   * @param int $maxLength
   *   Maximum length of the description.
   * @param string $replacement
   *   Dots.
   *
   * @return string
   *   Short description.
   */
  public function shortDescription(string $string, int $maxLength, string $replacement) {
    if (mb_strlen($string) > $maxLength) {
      return mb_substr($string, 0, $maxLength) . $replacement;
    }

    return $string;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:degov_devel.settings';
    $cache_tags[] = 'config:degov_social_media_instagram.settings';
    return $cache_tags;
  }

  /**
   * @{inheritDoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $this->modulehandler->loadInclude('degov_social_media_instagram', 'install');
    $result = $this->modulehandler->invoke('degov_social_media_instagram', 'requirements', ['runtime']);
    return AccessResult::allowedIf(is_array($result) && empty($result));
  }

}
