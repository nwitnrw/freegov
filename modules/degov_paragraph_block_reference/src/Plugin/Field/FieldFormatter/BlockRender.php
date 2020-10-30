<?php

namespace Drupal\degov_paragraph_block_reference\Plugin\Field\FieldFormatter;

use Drupal\block_field\BlockFieldManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field label formatter for Block Field.
 *
 * @FieldFormatter(
 *   id = "degov_block_render",
 *   label = @Translation("deGov Block label Display"),
 *   field_types = {"block_field"}
 * )
 */
class BlockRender extends FormatterBase {

  /**
   * @var \Drupal\block_field\BlockFieldManagerInterface
   */
  private $blockFieldManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   */
  public function setCurrentUser(AccountProxyInterface $current_user): void {
    $this->currentUser = $current_user;
  }

  /**
   * @param \Drupal\block_field\BlockFieldManagerInterface $block_field_manager
   */
  public function setBlockFieldManager(BlockFieldManagerInterface $block_field_manager): void {
    $this->blockFieldManager = $block_field_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setBlockFieldManager($container->get('block_field.manager'));
    $instance->setCurrentUser($container->get('current_user'));
    return $instance;
  }

  /**
   * Builds a renderable array for a field value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to be rendered.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $definitions = $this->blockFieldManager->getBlockDefinitions();
    foreach ($items as $delta => $item) {
      /** @var \Drupal\block_field\BlockFieldItemInterface $item */
      $block_instance = $item->getBlock();
      // Make sure the block exists and is accessible.
      if (!$block_instance || !$block_instance->access($this->currentUser)) {
        continue;
      }
      $title = $block_instance->getPluginId();
      if (!empty($definitions[$title])) {
        $category = (string) $definitions[$title]['category'];
        $label = $definitions[$title]['admin_label'];
        $title = $category . ': ' . $label;
      }
      $elements[$delta] = [
        '#markup' => $title,
      ];
    }
    return $elements;
  }

}
