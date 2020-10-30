<?php

namespace Drupal\degov_theme\Preprocess;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ViewsSearchContentAndMediaSorts.
 *
 * @package Drupal\degov_theme\Preprocess
 */
final class ViewsSearchContentAndMediaSorts implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Definition of blockManager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Definition of currentUser.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManagerInterface $blockManager, AccountProxyInterface $account) {
    $this->blockManager = $blockManager;
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('current_user')
    );
  }

  /**
   * Preprocess.
   */
  public function preprocess(array &$vars, string $hook): void {
    if (!in_array($hook, ['views_view__search_content', 'views_view__search_media'])) {
      return;
    }
    /** @var \Drupal\Core\Block\BlockManager $block_manager */
    $block_manager = $this->blockManager;
    $config = [];
    $block_id = ($hook === 'views_view__search_content')
      ? 'search_api_sorts_block:views_page:search_content__page_1'
      : 'search_api_sorts_block:views_page:search_media__page';

    $plugin_block = $block_manager->createInstance($block_id, $config);

    $access_result = $plugin_block->access($this->currentUser);
    if ((is_object($access_result) && $access_result->isForbidden())
      || (is_bool($access_result) && !$access_result)) {
      return;
    }

    if ($sorts_block = $plugin_block->build()) {
      $sorts_block['links']['#attributes']['class'][] = 'dropdown-menu';
      $sorts_block['links']['#attributes']['class'][] = 'pr-4';
      $sorts_block['links']['#attributes']['class'][] = 'pt-2';
      $sorts_block['links']['#attributes']['aria-labelledby'] = 'searchSortsButton';
      $active_sort_label = '';
      foreach ($sorts_block['links']['#items'] as &$sorts_block_link) {
        $sorts_block_link['#attribues']['class'][] = 'dropdown-item';
        $order = $sorts_block_link['#order'] === 'desc' ?
          $this->t('descending') : $this->t('ascending');
        $sorts_block_link['#label'] = $sorts_block_link['#label'] . " ($order)";
        $sorts_block_link['#order_indicator'] = [];
        if ($sorts_block_link['#active']) {
          $active_sort_label = $sorts_block_link['#label'];
        }
      }
      // Avoid side effect with $sorts_block_link, therefore we unset the variable here.
      unset($sorts_block_link);
      $vars['header']['sorts'] = $sorts_block;
      $vars['header']['active_sort_label'] = $active_sort_label;
    }

    if (($hook === 'views_view__search_media')) {
      /** @var \Drupal\views\ViewExecutable $view */
      $view = $vars['view'];
      $total = $view->total_rows;
      $vars['header']['result']['h2'] = [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Search results'),
      ];
      $vars['header']['result']['total'] = [
        '#markup' => $this->t('(about :num results)', [':num' => $total]),
      ];
    }
  }

}
