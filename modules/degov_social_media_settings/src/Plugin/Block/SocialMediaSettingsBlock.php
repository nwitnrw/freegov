<?php

namespace Drupal\degov_social_media_settings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialMediaSettingsBlock' block.
 *
 * @Block(
 *  id = "social_media_settings_block",
 *  admin_label = @Translation("Social media settings block"),
 * )
 */
final class SocialMediaSettingsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Config\ConfigFactoryInterface*/
  private $configFactory;

  /**
   * SocialMediaSettingsBlock constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('degov_common.default_settings');
    $build = [];
    $build['#theme'] = 'degov_social_media_settings_block';
    $build['#social_media_netiquette_url'] = $config->get('netiquette_url');
    $build['#social_media_privacy_url'] = $config->get('privacy_url');
    $build['#social_media_sources'] = $this->configFactory->get('degov_social_media_settings.default')->get('sources');
    $build['#attached']['library'] = ['degov_social_media_settings/process'];
    $degov_social_media_settings = [
      'link' => $this->t('This social media source is disabled. You can enable it in the <a role="button" href="#" data-toggle="modal" data-target="#social-media-settings" class="js-social-media-settings-open social-media-settings--menu-item">social media settings</a>.'),
      'cookie' => $this->t('This social media source is disabled. After accepting our cookie policy, you can enable it.')
    ];
    $sources = [];
    foreach ($build['#social_media_sources'] as $key => $value) {
      $sources[$key] = FALSE;
    }
    $degov_social_media_settings += ['sources' => $sources];
    $build['#attached']['drupalSettings'] = ['degov_social_media_settings' => $degov_social_media_settings];
    return $build;
  }

}
