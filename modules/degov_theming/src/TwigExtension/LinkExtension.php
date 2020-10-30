<?php

namespace Drupal\degov_theming\TwigExtension;

/**
 * Class LinkExtension.
 */
class LinkExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'degov_theming_link';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new \Twig_SimpleFilter('menu_item_attribute', [$this, 'getMenuItemAttribute']),
    ];
  }

  /**
   * Returns the requested attribute for a given menu item.
   *
   * Currently supports @target and @rel.
   *
   * @param array $menu_item
   *   The menu item.
   * @param string $attribute
   *   The requested attribute.
   *
   * @return \Twig_Markup
   *   The target attribute, or an empty string if nothing set.
   */
  public function getMenuItemAttribute(array $menu_item, string $attribute): \Twig_Markup {
    $output = '';
    switch ($attribute) {
      case 'target':
        if (!empty($menu_item['external']) && (bool) $menu_item['external'] === TRUE) {
          $output = 'target="_blank"';
        }
        if (!empty($menu_item['class']['options']['attributes']['target'])) {
          $output = sprintf('target="%s"', $menu_item['class']['options']['attributes']['target']);
        }
        break;

      case 'rel':
        if (!empty($menu_item['class']['options']['attributes']['rel'])) {
          $output = sprintf(' rel="%s"', $menu_item['class']['options']['attributes']['rel']);
        }
        break;
    }
    return new \Twig_Markup($output, 'UTF-8');
  }

}
