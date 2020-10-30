<?php

declare(strict_types=1);

namespace Drupal\degov_multilingual;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class DegovMultilingualFrontPage.
 *
 * @package Drupal\degov_multilingual
 */
class DegovMultilingualFrontPage {

  /**
   * Not found.
   *
   * @var int
   */
  const NOT_FOUND = 1;

  /**
   * Access denied.
   *
   * @var int
   */
  const ACCESS_DENIED = 2;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DegovMultilingualFrontPage constructor.
   */
  public function __construct(LanguageManagerInterface $languageManager, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Get the front page build.
   */
  public function getBuild() {
    $node = $this->getObject();
    if ($node instanceof NodeInterface) {
      if ($node->access('view')) {
        $build = $this->entityTypeManager
          ->getViewBuilder('node')
          ->view($node);
        $build['#cache']['tags'][] = 'degov_multilingual_front_page';
        return $build;
      }

      return self::ACCESS_DENIED;
    }
    return self::NOT_FOUND;
  }

  /**
   * Get the object of the current frontpage.
   */
  public function getObject(): ?NodeInterface {
    // Get the settings for front pages.
    $front_pages = $this->configFactory
      ->get('degov_multilingual.settings')
      ->get('front_pages');
    // Get current language.
    $language = $this->languageManager
      ->getCurrentLanguage()
      ->getId();
    // Get the default language in case the current is undefined.
    $default_language = $this->languageManager
      ->getDefaultLanguage()
      ->getId();
    $nid = NULL;
    if (isset($front_pages[$language])) {
      $nid = $front_pages[$language];
    }
    elseif (isset($front_pages[$default_language])) {
      $nid = $front_pages[$default_language];
    }
    // Check if the node is defined.
    if (is_numeric($nid)) {
      // If yes - try to load the object.
      /** @var \Drupal\node\NodeInterface $node */
      $node = $this->entityTypeManager
        ->getStorage('node')
        ->load($nid);
      if ($node instanceof NodeInterface) {
        return $node;
      }
    }
    return NULL;
  }

}
