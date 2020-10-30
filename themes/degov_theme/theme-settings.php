<?php

/**
 * @file
 * theme-settings.php
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function degov_theme_form_system_theme_settings_alter(&$form, FormStateInterface &$form_state, $form_id = NULL) {
  if (isset($form_id)) {
    return;
  }
  $form['social-icons'] = [
    '#type' => 'details',
    '#title' => t('Social icons'),
    '#open' => TRUE,
    '#tree' => TRUE,
  ];

  $settings = theme_get_setting('social-icons');

  $i = 0;
  while ($i != 10) {
    $i++;
    $form['social-icons'][$i] = [
      '#type' => 'fieldset',
      '#title' => t('Social network @counter', ['@counter' => $i]),
    ];
    $form['social-icons'][$i]['font-awesome-classes'] = [
      '#type'          => 'textfield',
      '#title'         => t('Font awesome classes'),
      '#default_value' => $settings[$i]['font-awesome-classes'],
      '#description'   => t("Enter the font awesome classes."),
    ];

    $form['social-icons'][$i]['url'] = [
      '#type'          => 'url',
      '#title'         => t('Social network url'),
      '#default_value' => $settings[$i]['url'],
      '#description'   => t("Enter the absolute path."),
    ];
  }
}
