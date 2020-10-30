<?php

namespace Drupal\degov_theme\Preprocess;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Markup;

/**
 * Class MenuAccount.
 *
 * @package Drupal\degov_theme\Preprocess
 */
final class MenuAccount implements ContainerInjectionInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * MenuAccount constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Preprocess the menu theme.
   *
   * @param array $vars
   *   Variables.
   */
  public function preprocess(array &$vars) {
    if ($vars['menu_name'] === 'account') {
      array_walk($vars['items'], function (&$item) {
        $routes = [
          'user.page' => 'fa-user',
          'user.login' => 'fa-user',
          'user.logout' => 'fa-sign-out-alt',
        ];

        /** @var \Drupal\Core\Url $url */
        $url = $item['url'];

        if (($url->isRouted()) && array_key_exists($url->getRouteName(), $routes)) {
          $icon = [
            '#type' => 'html_tag',
            '#tag' => 'i',
            '#attributes' => [
              'class' => [
                'fas',
                $routes[$url->getRouteName()],
              ],
            ],
          ];
          $rendered_icon = ' ' . $this->renderer->render($icon);
          $item['title'] = new Twig_Markup($item['title'] . $rendered_icon, 'utf8');
        }
      });
    }
  }

}
