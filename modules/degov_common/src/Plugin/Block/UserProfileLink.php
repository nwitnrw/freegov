<?php

declare(strict_types=1);

namespace Drupal\degov_common\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserProfileLink.
 *
 * @Block(
 *   id = "degov_user_profile_link",
 *   admin_label = @Translation("Login / Profile link"),
 *   category = @Translation("Blocks")
 * )
 */
final class UserProfileLink extends BlockBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Session\AccountProxyInterface*/
  protected $currentUser;

  public function setCurrentUser(AccountProxyInterface $current_user): void {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->setCurrentUser($container->get('current_user'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $block = [
      '#theme'     => 'degov_user_profile_link',
      '#logged_in' => $this->currentUser->isAuthenticated(),
      '#cache'     => [
        'user',
      ],
    ];

    return $block;
  }

}
