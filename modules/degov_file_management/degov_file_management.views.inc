<?php

/**
 * @file
 * Implementations of views-related hooks.
 */

/**
 * Implements hook_views_data_alter().
 */
function degov_file_management_views_data_alter(array &$data) {
  $data['file_managed']['file_delete_link'] = [
    'title' => t('Delete'),
    'field' => [
      'title' => t('Delete'),
      'help'  => t('Link to delete the file'),
      'id'    => 'degov_file_management_file_delete_link',
    ],
  ];
}
