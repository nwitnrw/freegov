<?php

declare(strict_types=1);

namespace Drupal\degov_demo_content\Generator;

use Drupal\Core\Database\Connection;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * Class MenuItemGenerator.
 *
 * @package Drupal\degov_demo_content\Generator
 */
final class MenuItemGenerator extends ContentGenerator implements GeneratorInterface {

  /**
   * Database.
   *
   * @var \Drupal\Core\Database\Connection
   *   The database connection.
   */
  private $database;

  /**
   * The entity type we are working with.
   * @var string
   */
  protected $entityType = 'menu_link_content';

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public function setDatabase(Connection $database): void {
    $this->database = $database;
  }

  /**
   * Generates menu items from a definitions file.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateContent(): void {
    $definitions = $this->loadDefinitions('menu_item.yml');

    $this->generateMenuItems($definitions);
  }

  /**
   * Generate menu items.
   *
   * Generates menu items from YAML definitions recursively for menus as
   * deep as we want.
   *
   * @param array $menuItemDefinitions
   *   Menu item definitions.
   * @param \Drupal\menu_link_content\MenuLinkContentInterface|null $parentMenuLink
   *   Parent menu link.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function generateMenuItems(array $menuItemDefinitions, MenuLinkContentInterface $parentMenuLink = NULL): void {
    foreach ($menuItemDefinitions as $key => $menuItemDefinition) {

      if (isset($menuItemDefinition['node_title'])) {
        $title = $menuItemDefinition['menu_title'] ?? $menuItemDefinition['node_title'];
        $uri = 'internal:/node/' . $this->getNidByNodeTitle($menuItemDefinition['node_title']);
      }
      elseif (isset($menuItemDefinition['media_title'])) {
        $title = $menuItemDefinition['menu_title'] ?? $menuItemDefinition['media_title'];
        $uri = 'internal:/media/' . $this->getMidByMediaTitle($menuItemDefinition['media_title']);
      }
      else {
        throw new \Exception(sprintf('Menu item definition must contain node_title or media_title to create a a link. Not found in: "%s"', $key));
      }

      $menuLinkParameters = [
        'title'     => $title,
        'link'      => [
          'uri' => $uri,
        ],
        'menu_name' => $menuItemDefinition['menu_name'],
        'expanded'  => TRUE,
      ];

      if (isset($menuItemDefinition['menu_weight'])) {
        $menuLinkParameters['weight'] = $menuItemDefinition['menu_weight'];
      }

      if (!empty($parentMenuLink)) {
        $menuLinkParameters['parent'] = $parentMenuLink->getPluginId();
      }

      if (empty($parentMenuLink) && !empty($menuItemDefinition['fontawesome_css_class'])) {
        $menuLinkParameters['link']['options'] = [
          'attributes' => [
            'class' => [
              $menuItemDefinition['fontawesome_css_class'],
            ],
          ],
        ];
      }

      $menuItem = MenuLinkContent::create($menuLinkParameters);
      $menuItem->save();

      if (!empty($menuItemDefinition['children'])) {
        $this->generateMenuItems($menuItemDefinition['children'], $menuItem);
      }
    }
  }

  /**
   * Gets the ID of a node with the given title.
   *
   * @param string $nodeTitle
   *   Node title.
   *
   * @return string
   *   Node ID.
   *
   * @throws \Exception
   */
  private function getNidByNodeTitle(string $nodeTitle): string {
    $query = $this->database->select('node_field_data', 'nfd')
      ->fields('nfd', ['nid'])
      ->condition('nfd.title', $nodeTitle);

    $nid = $query->execute()->fetchField();

    if (empty($nid) || !is_numeric($nid)) {
      throw new \Exception(sprintf('No node has been found by node title "%s"', $nodeTitle));
    }

    return $nid;
  }

  /**
   * Gets the ID of a Media with the given title.
   *
   * @param string $mediaTitle
   *   Node title.
   *
   * @return string
   *   Media ID.
   *
   * @throws \Exception
   */
  private function getMidByMediaTitle(string $mediaTitle): string {
    $query = $this->database->select('media_field_data', 'mfd')
      ->fields('mfd', ['mid'])
      ->condition('mfd.name', $mediaTitle);
    $mid = $query->execute()->fetchField();
    if (empty($mid) || !is_numeric($mid)) {
      throw new \Exception(sprintf('No media has been found by title "%s"', $mediaTitle));
    }
    return $mid;
  }

  /**
   * Deletes the generated content.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteContent(): void {
    $entities = $this->entityTypeManager
      ->getStorage($this->entityType)
      ->loadMultiple();

    foreach ($entities as $entity) {
      if ($this->isDemoMenuItem($entity)) {
        $entity->delete();
      }
    }
  }

  /**
   * Deletes, then regenerates the demo content.
   */
  public function resetContent(): void {
    $this->deleteContent();
    $this->generateContent();
  }

  /**
   * Checks if the given menu item was generated by this module.
   *
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $menuLinkItem
   *   The menu item to check.
   *
   * @return bool
   *   Demo menu item.
   *
   * @throws \Exception
   */
  private function isDemoMenuItem(MenuLinkContent $menuLinkItem): bool {
    $definitions = $this->loadDefinitions('menu_item.yml');
    return \in_array($menuLinkItem->getTitle(), $this->getMenuTitlesFromDefinition($definitions), TRUE);
  }

  /**
   * Get menu titles from definition.
   *
   * Recursively checks if a menu item title is contained in the
   * definitions array.
   *
   * @param array $definitions
   *   Definitions.
   *
   * @return array
   *   Menu titles.
   */
  private function getMenuTitlesFromDefinition(array $definitions): array {
    $titlesArray = [];

    foreach ($definitions as $definition) {
      $titlesArray[] = $definition['menu_title'] ?? $definition['node_title'];

      if (!empty($definition['children'])) {
        $titlesArray = array_merge($titlesArray, $this->getMenuTitlesFromDefinition($definition['children']));
      }
    }

    return $titlesArray;
  }

}
