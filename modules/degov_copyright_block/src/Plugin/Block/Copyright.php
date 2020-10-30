<?php

namespace Drupal\degov_copyright_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a copyright block with the current year and the site name.
 *
 * @Block(
 *   id = "degov_copyright_block",
 *   admin_label = @Translation("Copyright"),
 * )
 */
final class Copyright extends BlockBase implements ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /** @var \Drupal\Core\Config\ConfigFactoryInterface*/
  protected $configFactory;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function setConfigFactory(ConfigFactoryInterface $config_factory): void {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setConfigFactory($container->get('config.factory'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $blockConfiguration = $this->getConfiguration();
    $form['copyright_text'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Copyright text'),
      '#description'   => $this->t('This text will be displayed in the footer after the copyright symbol and the current year.'),
      '#default_value' => $blockConfiguration['copyright_text'] ?? '',
      '#attributes'    => [
        'placeholder' => $this->configFactory->get('system.site')->get('name'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->setConfigurationValue('copyright_text', $form_state->getValue('copyright_text'));
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $blockConfiguration = $this->getConfiguration();

    $block = [
      '#theme'            => 'degov_copyright_block',
      '#date'             => date('Y'),
      '#copyright_holder' => !empty(trim($blockConfiguration['copyright_text'])) ? $blockConfiguration['copyright_text'] : $this->configFactory->get('system.site')->get('name'),
    ];

    return $block;
  }

}
