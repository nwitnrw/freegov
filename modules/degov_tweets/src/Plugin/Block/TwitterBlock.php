<?php

namespace Drupal\degov_tweets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\degov_tweets\TwitterAPIExchange;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a 'TwitterBlock' block.
 *
 * @Block(
 *  id = "degov_twitter_block",
 *  admin_label = @Translation("Twitter block"),
 * )
 */
final class TwitterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Definition of TwitterAPIExchange.
   *
   * @var \Drupal\degov_tweets\TwitterAPIExchange
   */
  protected $twitter;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface*/
  private $moduleHandler;

  /**
   * TwitterFeedBlock constructor.
   * phpcs:disable
   *
   * @param array $configuration
   *   Block plugin config.
   * @param string $plugin_id
   *   Block plugin plugin_id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\degov_tweets\TwitterAPIExchange $twitter
   *   The Twitter service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   *
   * phpcs:enable
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TwitterAPIExchange $twitter,
    ModuleHandlerInterface $moduleHandler
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->twitter = $twitter;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('degov_tweets.twitter'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $form['markup'] = [
      '#type' => 'markup',
      '#markup' => t('<a href="@url">Create a twitter app on the twitter developer site</a>', ['@url' => 'https://dev.twitter.com/apps/']),
    ];
    $form['tweets_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter username'),
      '#default_value' => $configuration['tweets_username'] ?? 'deGov',
      '#required' => TRUE,
    ];
    $form['tweets_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => $configuration['tweets_limit'] ?? 12,
      '#min' => 1
    ];
    $form['tweets_update_every'] = [
      '#type' => 'number',
      '#title' => $this->t('Update every'),
      '#description' => $this->t('Set the number in seconds. E.g. 3600 = 1 hour'),
      '#default_value' => $configuration['tweets_update_every'] ?? 7200,
      '#min' => 300,
      '#step' => 60
    ];
    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access token'),
      '#default_value' => $configuration['access_token'] ?? '',
      '#required' => TRUE,
    ];
    $form['token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token secret'),
      '#default_value' => $configuration['token_secret'] ?? '',
      '#required' => TRUE,
    ];
    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer key'),
      '#default_value' => $configuration['consumer_key'] ?? '',
      '#required' => TRUE,
    ];
    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer secret'),
      '#default_value' => $configuration['consumer_secret'] ?? '',
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $this->configuration[$key] = $value;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function build() {
    $build = [];
    if ($this->moduleHandler->moduleExists('degov_demo_content')) {
      $this->twitter->setDevMode(TRUE);
      $response = $this->twitter->performRequest();
    }
    else {
      // Make a request to Twitter API.
      $settings = [
        'oauth_access_token' => $this->configuration['access_token'],
        'oauth_access_token_secret' => $this->configuration['token_secret'],
        'consumer_key' => $this->configuration['consumer_key'],
        'consumer_secret' => $this->configuration['consumer_secret'],
      ];
      $this->twitter->setSettings($settings);

      $params = [
        'screen_name' => $this->configuration['tweets_username'],
        'count' => $this->configuration['tweets_limit'],
      ];

      $response = $this->twitter
        ->setGetfield($params)
        ->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json')
        ->performRequest();
    }

    if ($response) {
      $tweets = json_decode($response);
      $build = [
        '#theme' => 'degov_tweets',
        '#tweets' => $tweets,
        '#cache' => [
          'max-age' => is_numeric($this->configuration['tweets_update_every']) ? $this->configuration['tweets_update_every'] : self::getMaxAge(),
        ],
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:degov_devel.settings';
    return $cache_tags;
  }

  /**
   * Returns default max-age value for caching.
   */
  public static function getMaxAge() {
    $path = [
      drupal_get_path('module', 'degov_tweets'),
      'config',
      'block',
      'block.block.twitterblock.yml',
    ];
    $content = Yaml::parseFile(implode('/', $path));

    return $content['settings']['tweets_update_every'];
  }

}
