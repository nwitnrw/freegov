<?php

/**
 * @file
 * Enables modules and site configuration for the deGov profile.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_install_tasks().
 */
function degov_install_tasks() {
  // Defines additional tasks to be performed by the deGov installation profile.
  $tasks = [
    'degov_theme_setup'    => [
      'display_name' => t('Install deGov - Theme'),
      'display'      => TRUE,
    ],
    'degov_module_setup'   => [
      'display_name' => t('Install deGov - Base'),
      'type'         => 'batch',
    ],
    'degov_media_setup'    => [
      'display_name' => t('Install deGov - Media'),
      'type'         => 'batch',
    ],
    'degov_finalize_setup' => [
      'display_name' => t('Finalize installation'),
      'type'         => 'batch',
      'display'      => TRUE,
    ],
  ];

  return $tasks;
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function degov_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Prevent Drupal status messages during profile installation.
  \Drupal::messenger()->deleteByType('status');

  // Alters the profile configuration form to add an additional list of optional
  // deGov modules that can be enabled during profile installation.

  // List all optional deGov modules.
  $degov_optional_modules = [
    'degov_demo_content' => t('Demo Content'),
    'degov_devel'        => t('deGov - Devel'),
  ];
  $form['degov']['optional_modules'] = [
    '#type'          => 'checkboxes',
    '#title'         => t('ENABLE OPTIONAL FEATURES'),
    '#description'   => t('Checked features are recommended.'),
    '#options'       => $degov_optional_modules,
    '#default_value' => [],
  ];

  // Add an additional submit handler for optional modules.
  $form['#submit'][] = 'degov_optional_modules_submit';
}

/**
 * Submit handler for degov_form_install_configure_form_alter().
 */
function degov_optional_modules_submit($form_id, &$form_state) {
  $degovOptionalModules = array_filter($form_state->getValue('optional_modules'));
  \Drupal::state()->set('degov_optional_modules', $degovOptionalModules);
}

/**
 * Install deGov modules task.
 *
 * Install all required base deGov modules and features as an additional step to
 * prevent double defined configuration files.
 */
function degov_module_setup(&$install_state) {
  // Prevent Drupal status messages during profile installation.
  \Drupal::messenger()->deleteByType('status');

  // Define all required base deGov modules and features.
  $modules = [
    'degov_common'                      => 'degov_common',
    'degov_content_types_shared_fields' => 'degov_content_types_shared_fields',
    'degov_config_integrity'            => 'degov_config_integrity',
    'degov_image_and_crop_styles'       => 'degov_image_and_crop_styles',
    'degov_date_formats'                => 'degov_date_formats',
    'degov_pathauto'                    => 'degov_pathauto',
    'degov_rich_text_format_settings'   => 'degov_rich_text_format_settings',
    'degov_users_roles'                 => 'degov_users_roles',
    'degov_node_overrides'              => 'degov_node_overrides',
    'degov_taxonomies'                  => 'degov_taxonomies',
    'degov_taxonomy_term_synonyms'      => 'degov_taxonomy_term_synonyms',
    'degov_node_normal_page'            => 'degov_node_normal_page',
    'degov_password_policy'             => 'degov_password_policy',
    'degov_paragraph_text'              => 'degov_paragraph_text',
    'degov_paragraph_webform'           => 'degov_paragraph_webform',
    'degov_paragraph_slideshow'         => 'degov_paragraph_slideshow',
    'degov_paragraph_header'            => 'degov_paragraph_header',
    'degov_paragraph_block_reference'   => 'degov_paragraph_block_reference',
    'degov_simplenews'                  => 'degov_simplenews',
    'degov_simplenews_references'       => 'degov_simplenews_references',
    'degov_email_login'                 => 'degov_email_login',
    'degov_fa_icon_picker'              => 'degov_fa_icon_picker',
    'degov_copyright_block'             => 'degov_copyright_block',
    'degov_node_external_teaser'        => 'degov_node_external_teaser',
    'degov_auto_crop'                   => 'degov_auto_crop',
    'degov_file_management'             => 'degov_file_management',
    'degov_search_content'              => 'degov_search_content',
    'degov_search_synonyms'             => 'degov_search_synonyms',
    'degov_scheduled_updates'           => 'degov_scheduled_updates',
    'degov_media_usage'                 => 'degov_media_usage',
    'degov_media_usage_node'            => 'degov_media_usage_node',
    'degov_media_usage_paragraphs'      => 'degov_media_usage_paragraphs',
    'node_action'                       => 'node_action',
    'filter_disallow'                   => 'filter_disallow',
    'media_file_links'                  => 'media_file_links',
    'entity_reference_timer'            => 'entity_reference_timer',
  ];

  // See issue https://www.drupal.org/project/search_api/issues/2931562
  \Drupal::state()->set('search_api_use_tracking_batch', FALSE);

  if (in_array('degov_devel', \Drupal::state()->get('degov_optional_modules'), TRUE)) {
    array_unshift($modules, 'degov_devel');
  }

  /** @var \Drupal\degov\Installation $degov_installation */
  $degov_installation = \Drupal::service('degov.installation');
  // Batch API definition.
  $batch = [
    'operations'    => $degov_installation->getBatchOperations($modules),
    'title'         => t('Install deGov modules'),
    'error_message' => t('An error occurred during deGov module installation.'),
  ];

  return $batch;
}

/**
 * Install deGov modules task.
 *
 * Install all required base deGov modules and features as an additional step to
 * prevent double defined configuration files.
 */
function degov_media_setup(&$install_state) {
  // Prevent Drupal status messages during profile installation.
  \Drupal::messenger()->deleteByType('status');

  // Define all required base deGov modules and features.
  $modules = [
    'degov_media_video'               => 'degov_media_video',
    'degov_media_video_upload'        => 'degov_media_video_upload',
    'degov_media_address'             => 'degov_media_address',
    'degov_media_audio'               => 'degov_media_audio',
    'degov_media_caption_helper'      => 'degov_media_caption_helper',
    'degov_media_citation'            => 'degov_media_citation',
    'degov_media_contact'             => 'degov_media_contact',
    'degov_media_document'            => 'degov_media_document',
    'degov_media_gallery'             => 'degov_media_gallery',
    'degov_media_image'               => 'degov_media_image',
    'degov_media_instagram'           => 'degov_media_instagram',
    'degov_media_person'              => 'degov_media_person',
    'degov_media_tweet'               => 'degov_media_tweet',
    'degov_search_media'              => 'degov_search_media',
    'degov_media_overrides'           => 'degov_media_overrides',
    'degov_social_media_settings'     => 'degov_social_media_settings',
    'degov_media_video_mobile'        => 'degov_media_video_mobile',
    'degov_paragraph_media_reference' => 'degov_paragraph_media_reference',
    'degov_simplenews'                => 'degov_simplenews',
    'degov_simplenews_references'     => 'degov_simplenews_references',
  ];

  /** @var \Drupal\degov\Installation $degov_installation */
  $degov_installation = \Drupal::service('degov.installation');
  // Batch API definition.
  $batch = [
    'operations'    => $degov_installation->getBatchOperations($modules),
    'title'         => t('Install deGov - Media'),
    'error_message' => t('An error occurred during deGov - Media installation.'),
  ];

  return $batch;
}

/**
 * Install deGov theme task.
 *
 * Installs the deGov demo theme as an additional step.
 */
function degov_theme_setup(&$install_state) {
  // Prevent Drupal status messages during profile installation.
  \Drupal::messenger()->deleteByType('status');

  // Set the default theme to be deGov.
  $themes = ['degov_theme', 'bartik'];
  \Drupal::service('theme_handler')->install($themes);
  \Drupal::configFactory()
    ->getEditable('system.theme')
    ->set('default', 'degov_theme')
    ->set('admin', 'claro')
    ->save();

  \Drupal::service('theme.manager')->resetActiveTheme();
}

/**
 * Finalize deGov profile installation task.
 *
 * Installs additional recommended deGov modules and features that has been
 * selected during the deGov profile installation.
 */
function degov_finalize_setup() {
  if (!\Drupal::configFactory()->get('ultimate_cron.settings')->isNew()) {
    \Drupal::configFactory()->getEditable('ultimate_cron.settings')->set('launcher.max_threads', 5)->save();
  }

  // Prevent Drupal status messages during profile installation.
  \Drupal::messenger()->deleteByType('status');

  // Retrieve all checked optional modules.
  $degov_optional_modules = \Drupal::state()->get('degov_optional_modules');

  /** @var \Drupal\degov\Installation $degov_installation */
  $degov_installation = \Drupal::service('degov.installation');

  // Batch API definition.
  $batch = [
    'operations'    => $degov_installation->getBatchOperations($degov_optional_modules),
    'title'         => t('Install deGov - optional modules'),
    'error_message' => t('An error occurred during installation.'),
  ];

  return $batch;
}

/**
 * Performs batch operation to install a deGov module or feature.
 */
function _install_degov_module_batch(array $module, string $module_name, &$context):void {
  set_time_limit(0);
  /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
  $module_installer = \Drupal::service('module_installer');
  $module_installer->install($module, $dependencies = TRUE);
  $context['results'][] = $module;
  $context['message'] = t('Installed %module module.', ['%module' => $module_name]);
}
