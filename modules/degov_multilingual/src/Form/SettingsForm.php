<?php

declare(strict_types=1);

namespace Drupal\degov_multilingual\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\degov_multilingual
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The entity display repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'degov_multilingual.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'degov_multilingual_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get current configuration values.
    $front_pages = $this->config('degov_multilingual.settings')->get('front_pages');
    $main_menu = $this->config('degov_multilingual.settings')->get('main_menu');
    $footer_menu = $this->config('degov_multilingual.settings')->get('footer_menu');
    $footer_bottom_menu = $this->config('degov_multilingual.settings')->get('footer_bottom_menu');
    $header_top_menu = $this->config('degov_multilingual.settings')->get('header_top_menu');
    // Get active languages.
    $languages = $this->languageManager->getLanguages();
    // Get default language.
    $default_language_id = $this->languageManager->getDefaultLanguage()->getId();
    // Get node and menu storages.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $menu_storage = $this->entityTypeManager->getStorage('menu');
    // Populate the options for menu dropdown.
    $menu_options = ['' => $this->t('-- Choose menu --')];
    $menus = $menu_storage->loadMultiple();
    foreach ($menus as $machine_name => $menu) {
      $menu_options[$machine_name] = $menu->label();
    }
    // Create field wrappers.
    $form['language'] = [
      '#title' => $this->t('Front page settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    $form['main_menu'] = [
      '#title' => $this->t('Main menu settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    $form['footer_menu'] = [
      '#title' => $this->t('Footer menu settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    $form['footer_bottom_menu'] = [
      '#title' => $this->t('Footer bottom menu settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    $form['header_top_menu'] = [
      '#title' => $this->t('Header top menu settings'),
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];

    foreach ($languages as $language) {
      $language_id = $language->getId();
      // Set default values.
      $default_value = NULL;
      // Set main menu as default one, avoid to not have empty.
      $main_menu_default_value = 'main';
      $footer_menu_default_value = 'main';
      $footer_bottom_menu_default_value = 'main';
      $header_top_menu_default_value = 'main';

      if (isset($front_pages[$language_id])) {
        $node = $node_storage->load($front_pages[$language_id]);

        if ($node instanceof NodeInterface) {
          $default_value = $node;
        }
      }

      if (isset($main_menu[$language_id]) && isset($menu_options[$main_menu[$language_id]])) {
        $main_menu_default_value = $main_menu[$language_id];
      }

      if (isset($footer_menu[$language_id]) && isset($menu_options[$footer_menu[$language_id]])) {
        $footer_menu_default_value = $footer_menu[$language_id];
      }

      if (isset($footer_bottom_menu[$language_id]) && isset($menu_options[$footer_bottom_menu[$language_id]])) {
        $footer_bottom_menu_default_value = $footer_bottom_menu[$language_id];
      }

      if (isset($header_top_menu[$language_id]) && isset($menu_options[$header_top_menu[$language_id]])) {
        $header_top_menu_default_value = $header_top_menu[$language_id];
      }

      $form['language'][$language_id] = [
        '#title' => $this->t('Front page (@langcode)', ['@langcode' => $language_id]),
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#default_value' => $default_value,
        '#required' => $default_language_id === $language_id,
      ];

      $form['main_menu'][$language_id] = [
        '#title' => $this->t('Main menu (@langcode)', ['@langcode' => $language_id]),
        '#type' => 'select',
        '#options' => $menu_options,
        '#default_value' => $main_menu_default_value,
        '#required' => $default_language_id === $language_id,
      ];

      $form['footer_menu'][$language_id] = [
        '#title' => $this->t('Footer menu (@langcode)', ['@langcode' => $language_id]),
        '#type' => 'select',
        '#options' => $menu_options,
        '#default_value' => $footer_menu_default_value,
        '#required' => $default_language_id === $language_id,
      ];

      $form['footer_bottom_menu'][$language_id] = [
        '#title' => $this->t('Footer bottom menu (@langcode)', ['@langcode' => $language_id]),
        '#type' => 'select',
        '#options' => $menu_options,
        '#default_value' => $footer_bottom_menu_default_value,
        '#required' => $default_language_id === $language_id,
      ];

      $form['header_top_menu'][$language_id] = [
        '#title' => $this->t('Header top menu (@langcode)', ['@langcode' => $language_id]),
        '#type' => 'select',
        '#options' => $menu_options,
        '#default_value' => $header_top_menu_default_value,
        '#required' => $default_language_id === $language_id,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $front_pages = [];
    $main_menu = [];
    $footer_menu = [];
    $footer_bottom_menu = [];
    $header_top_menu = [];

    // Save settings.
    foreach ($form_state->getValue('language') as $language_id => $nid) {
      if (!empty($nid)) {
        $front_pages[$language_id] = $nid;
      }
    }

    foreach ($form_state->getValue('main_menu') as $language_id => $menu_id) {
      if (!empty($menu_id)) {
        $main_menu[$language_id] = $menu_id;
      }
    }

    foreach ($form_state->getValue('footer_menu') as $language_id => $menu_id) {
      if (!empty($menu_id)) {
        $footer_menu[$language_id] = $menu_id;
      }
    }

    foreach ($form_state->getValue('footer_bottom_menu') as $language_id => $menu_id) {
      if (!empty($menu_id)) {
        $footer_bottom_menu[$language_id] = $menu_id;
      }
    }

    foreach ($form_state->getValue('header_top_menu') as $language_id => $menu_id) {
      if (!empty($menu_id)) {
        $header_top_menu[$language_id] = $menu_id;
      }
    }

    $this->configFactory()->getEditable('degov_multilingual.settings')
      ->set('front_pages', $front_pages)
      ->set('main_menu', $main_menu)
      ->set('footer_menu', $footer_menu)
      ->set('footer_bottom_menu', $footer_bottom_menu)
      ->set('header_top_menu', $header_top_menu)
      ->save();

    Cache::invalidateTags(['degov_multilingual_front_page']);
    parent::submitForm($form, $form_state);
  }

}
